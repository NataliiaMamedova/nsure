<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Request\ValidationInterface;
use App\VO\ValidationError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractRequestArgumentResolver implements ArgumentValueResolverInterface
{
    protected ValidatorInterface $validator;

    protected DenormalizerInterface $denormalizer;

    public function __construct(ValidatorInterface $validator, DenormalizerInterface $denormalizer)
    {
        $this->validator = $validator;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @throws ValidationException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $bodyParams = $this->decodeRequestBodyParams($request, $argument);
        $denormalizedRequest = $this->denormalizeRequestParams($this->mergeAllRequestParams($request, $bodyParams), (string) $argument->getType());
        $this->validateDenormalizedRequest($denormalizedRequest);

        yield $denormalizedRequest;
    }

    abstract protected function decodeRequestBodyParams(Request $request, ArgumentMetadata $argument): array;

    /**
     * @throws ValidationException
     */
    protected function validateDenormalizedRequest(object $denormalizedRequest): void
    {
        $errors = $this->validator->validate($denormalizedRequest);
        if (0 !== count($errors)) {
            $ex = new ValidationException('Request validation failed');
            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $ex->addError(new ValidationError(
                    (string) $error->getMessage(),
                    $error->getCode(),
                    $error->getPropertyPath(),
                    $error->getInvalidValue()
                ));
            }
            throw $ex;
        }
        if ($denormalizedRequest instanceof ValidationInterface) {
            $denormalizedRequest->validate();
        }
    }

    /**
     * @template T of object
     *
     * @throws ValidationException
     *
     * @return object class-string<T> $requestFqn
     */
    protected function denormalizeRequestParams(array $requestParams, string $requestFqn): object
    {
        try {
            return $this->denormalizer->denormalize($requestParams, $requestFqn, null, [
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ]);
        } catch (\Throwable $ex) {
            throw new ValidationException('Can not denormalize request: ' . $ex->getMessage(), ValidationException::REQUEST_PARSING_ERROR_CODE);
        }
    }

    protected function mergeAllRequestParams(Request $request, array $bodyParams): array
    {
        return array_merge(
            iterator_to_array($request->attributes->getIterator())['_route_params'] ?? [],
            iterator_to_array($request->query->getIterator()),
            iterator_to_array($request->request->getIterator()),
            $bodyParams
        );
    }
}

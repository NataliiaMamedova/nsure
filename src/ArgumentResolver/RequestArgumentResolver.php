<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\NSure\Request\NSureRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestArgumentResolver extends AbstractRequestArgumentResolver
{
    /**
     * @codeCoverageIgnore
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = (string) $argument->getType();

        return class_exists($type) && isset(class_implements($type)[NSureRequestInterface::class]); /** @phpstan-ignore-line */
    }

    /**
     * @throws ValidationException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $allParams = $this->mergeAllRequestParams($request, $this->decodeRequestBodyParams($request, $argument));
        $denormalizedRequest = $this->denormalizeRequestParams($allParams, (string) $argument->getType());
        $this->validateDenormalizedRequest($denormalizedRequest);

        yield $denormalizedRequest;
    }

    protected function decodeRequestBodyParams(Request $request, ArgumentMetadata $argument): array
    {
        $bodyParams = [];
        $contentType = explode(';', $request->headers->get('Content-Type', ''));
        if (in_array('application/json', $contentType, true) && '' !== $request->getContent()) {
            try {
                $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
                if (is_array($body)) {
                    $bodyParams = $body;
                }
            } catch (\Throwable $ex) {
                throw new ValidationException('Body is not valid JSON: ' . $ex->getMessage(), ValidationException::INVALID_JSON_CODE);
            }
        }

        return $bodyParams;
    }
}

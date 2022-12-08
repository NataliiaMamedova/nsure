<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Exception\ValidationException;
use App\Message\AbstractMessage;
use App\VO\ValidationError;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait MessageValidatorTrait
{
    protected ValidatorInterface $validator;

    /**
     * @throws ValidationException
     */
    protected function validate(AbstractMessage $message): void
    {
        $errors = $this->validator->validate($message);

        if (0 !== count($errors)) {
            $ex = new ValidationException('Message validation failed');
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
    }
}

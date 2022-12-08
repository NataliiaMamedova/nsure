<?php

declare(strict_types=1);

namespace App\VO;

use Paybis\Common\ValueObject\VO\AbstractString;

class EventId extends AbstractString
{
    private const EVENT_PHONE_VERIFICATION = 'phoneVerification';

    private const EVENT_EMAIL_VERIFICATION = 'emailVerification';

    private const EVENT_SIGN_IN = 'signIn';

    private const EVENT_SIGN_OUT = 'signOut';

    private const EVENT_SIGN_UP = 'signUp';

    private const EVENT_TX_CANCEL = 'txCancel';

    private const EVENT_RECIPIENT_UPDATE = 'recipientUpdate';

    private const EVENT_PAYMENT_METHOD = 'paymentMethod';

    private const EVENT_TX_FAILURE = 'txFailure';

    public static function phoneVerification(): self
    {
        return new static(self::EVENT_PHONE_VERIFICATION); /** @phpstan-ignore-line */
    }

    public static function emailVerification(): self
    {
        return new static(self::EVENT_EMAIL_VERIFICATION); /** @phpstan-ignore-line */
    }

    public static function signIn(): self
    {
        return new static(self::EVENT_SIGN_IN); /** @phpstan-ignore-line */
    }

    public static function signOut(): self
    {
        return new static(self::EVENT_SIGN_OUT); /** @phpstan-ignore-line */
    }

    public static function signUp(): self
    {
        return new static(self::EVENT_SIGN_UP); /** @phpstan-ignore-line */
    }

    public static function txCancel(): self
    {
        return new static(self::EVENT_TX_CANCEL); /** @phpstan-ignore-line */
    }

    public static function recipientUpdate(): self
    {
        return new static(self::EVENT_RECIPIENT_UPDATE); /** @phpstan-ignore-line */
    }

    public static function paymentMethod(): self
    {
        return new static(self::EVENT_PAYMENT_METHOD); /** @phpstan-ignore-line */
    }

    public static function txFailure(): self
    {
        return new static(self::EVENT_TX_FAILURE); /** @phpstan-ignore-line */
    }
}

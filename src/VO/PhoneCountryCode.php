<?php

declare(strict_types=1);

namespace App\VO;

use Paybis\Common\ValueObject\VO\AbstractString;
use Symfony\Component\Validator\Constraints as Assert;

class PhoneCountryCode extends AbstractString
{
    /**
     * @Assert\Regex("/^[0-9]+$/")
     */
    protected string $value;
}

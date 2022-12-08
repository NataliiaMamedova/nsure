<?php

declare(strict_types=1);

namespace App\VO;

use Paybis\Common\ValueObject\VO\AbstractString;
use Symfony\Component\Validator\Constraints as Assert;

class IP extends AbstractString
{
    /**
     * @Assert\Ip
     */
    protected string $value;
}

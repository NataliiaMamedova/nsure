<?php

declare(strict_types=1);

namespace App\VO;

use Paybis\Common\ValueObject\VO\AbstractString;
use Symfony\Component\Validator\Constraints as Assert;

class Bin extends AbstractString
{
    /**
     * @Assert\Length(min=6, max=6)
     */
    protected string $value;
}

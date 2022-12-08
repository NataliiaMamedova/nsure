<?php

declare(strict_types=1);

namespace App\VO;

use Paybis\Common\ValueObject\VO\AbstractString;
use Symfony\Component\Validator\Constraints as Assert;

class Last4 extends AbstractString
{
    /**
     * @Assert\Length(min=4, max=4)
     */
    protected string $value;
}

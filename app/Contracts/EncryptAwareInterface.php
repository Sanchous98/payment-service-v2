<?php

declare(strict_types=1);

namespace App\Contracts;

use PaymentSystem\Contracts\EncryptInterface;

interface EncryptAwareInterface
{
    public function setEncrypt(EncryptInterface $encrypt): void;
}

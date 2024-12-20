<?php

namespace App\Contracts;

use PaymentSystem\Contracts\EncryptInterface;

interface EncryptAwareInterface
{
    public function setEncrypt(EncryptInterface $encrypt): void;
}

<?php

namespace App\Enums;

enum AccountType: string
{
    case Seeker = 'seeker';
    case Company = 'company';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}

<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Verifikator = 'verifikator';
    case Admin = 'admin';
}

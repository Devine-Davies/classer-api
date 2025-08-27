<?php

namespace App\Enums;

enum RegistrationType: string
{
    case STANDARD = 'standard';
    case SOCIAL = 'social';
    case INVITE = 'invite';
    case ADMIN = 'admin';
}
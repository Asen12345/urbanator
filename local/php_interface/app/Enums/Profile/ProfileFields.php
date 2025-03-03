<?php

namespace App\Enums\Profile;

enum ProfileFields: string
{
    case NAME = 'NAME';
    case LAST_NAME = 'LAST_NAME';
    case SECOND_NAME = 'SECOND_NAME';
    case EMAIL = 'EMAIL';
    case PHONE = 'PERSONAL_PHONE';
}
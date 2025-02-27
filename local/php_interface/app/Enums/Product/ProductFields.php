<?php

namespace App\Enums\Product;

enum ProductFields: string {
    case NAME = 'NAME';
    case DETAIL_PICTURE = 'DETAIL_PICTURE';
    case PREVIEW_PICTURE = 'PREVIEW_PICTURE';
    case DETAIL_DESCRIPTION = 'DETAIL_TEXT';
    case MORE_PHOTO = 'MORE_PHOTO';
}
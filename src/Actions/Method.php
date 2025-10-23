<?php

declare(strict_types=1);

namespace MakeCommerce\Actions;

enum Method
{
    case GET;
    case POST;
    case PUT;
    case DELETE;
    case PATCH;
    case OPTIONS;
    case HEAD;
}

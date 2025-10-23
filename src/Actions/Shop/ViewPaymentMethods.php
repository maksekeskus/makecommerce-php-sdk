<?php

declare(strict_types=1);

namespace MakeCommerce\Actions\Shop;

use MakeCommerce\Actions\Action;
use MakeCommerce\Actions\Method;

class ViewPaymentMethods extends Action
{
    public Method $method = Method::GET;

    public const ENDPOINT = 'methods';

    public const SCHEMA_NAME = 'ViewPaymentMethodsSchema.json';

    protected function getMethod(): Method
    {
        return $this->method;
    }

    protected function getSchemaName(): string
    {
        return self::SCHEMA_NAME;
    }

    protected function getEndpoint(): string
    {
        return self::ENDPOINT;
    }
}

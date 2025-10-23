<?php

declare(strict_types=1);

namespace MakeCommerce\Actions\Refunds;

use MakeCommerce\Actions\Action;
use MakeCommerce\Actions\Method;

class ListRefundsAction extends Action
{
    public Method $method = Method::GET;

    public const ENDPOINT = 'refunds';

    public const SCHEMA_NAME = 'ListRefundsSchema.json';

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

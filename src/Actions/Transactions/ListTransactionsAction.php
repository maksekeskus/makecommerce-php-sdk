<?php

declare(strict_types=1);

namespace MakeCommerce\Actions\Transactions;

use MakeCommerce\Actions\Action;
use MakeCommerce\Actions\Method;

class ListTransactionsAction extends Action
{
    public Method $method = Method::GET;
    public const ENDPOINT = 'transactions';
    public const SCHEMA_NAME = 'ListTransactionsSchema.json';

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

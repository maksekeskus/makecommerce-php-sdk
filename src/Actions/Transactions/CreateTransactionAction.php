<?php

declare(strict_types=1);

namespace MakeCommerce\Actions\Transactions;

use MakeCommerce\Actions\Action;
use MakeCommerce\Actions\Method;

class CreateTransactionAction extends Action
{
    public Method $method = Method::POST;

    public const ENDPOINT = 'transactions';

    public const SCHEMA_NAME = 'CreateTransactionsSchema.json';

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

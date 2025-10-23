<?php

declare(strict_types=1);

namespace MakeCommerce\Actions\Transactions;

use MakeCommerce\Actions\Action;
use MakeCommerce\Actions\Method;

class ViewTransactionAction extends Action
{
    public Method $method = Method::GET;

    public string $endpoint = 'transactions/{id}';

    public const SCHEMA_NAME = '';

    public function setTransaction(string $transactionId): void
    {
        $this->endpoint = str_replace('{id}', $transactionId, $this->endpoint);
    }

    public function setTransactionStatement(string $transactionId): void
    {
        $this->endpoint = str_replace('{id}', sprintf('%s/statement', $transactionId), $this->endpoint);
    }

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
        return $this->endpoint;
    }
}

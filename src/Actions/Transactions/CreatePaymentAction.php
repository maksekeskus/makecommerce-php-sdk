<?php

declare(strict_types=1);

namespace MakeCommerce\Actions\Transactions;

use MakeCommerce\Actions\Action;
use MakeCommerce\Actions\Method;

class CreatePaymentAction extends Action
{
    public Method $method = Method::POST;

    public string $endpoint = 'transactions/{id}/payments';

    public const SCHEMA_NAME = 'CreatePaymentSchema.json';

    public function setTransaction(string $transactionId): void
    {
        $this->endpoint = str_replace('{id}', $transactionId, $this->endpoint);
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

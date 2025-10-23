<?php

declare(strict_types=1);

namespace MakeCommerce\Actions\Shop;

use MakeCommerce\Actions\Action;
use MakeCommerce\Actions\Method;

class ViewAccountstatementAction extends Action
{
    public Method $method = Method::GET;

    public string $endpoint = 'shop/accountstatements';

    public string $schemaName = 'AccountstatementSchema.json';

    public function setXML(): void
    {
        $this->endpoint = 'shop/accountstatements/xml';
        $this->schemaName = 'AccountstatementXMLSchema.json';
    }

    public function setCAMT053(): void
    {
        $this->endpoint = 'shop/accountstatements/camt053';
        $this->schemaName = 'AccountstatementXMLSchema.json';
    }

    protected function getMethod(): Method
    {
        return $this->method;
    }

    protected function getSchemaName(): string
    {
        return $this->schemaName;
    }

    protected function getEndpoint(): string
    {
        return $this->endpoint;
    }
}

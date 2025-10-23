<?php

declare(strict_types=1);

namespace MakeCommerce;

use Psr\Http\Message\ResponseInterface;

class MCResponse
{
    /**
     * @var int
     */
    public int $code;

    /**
     * @var mixed
     */
    public $rawBody;

    /**
     * @var array|object
     */
    public $body;

    /**
     * @var array
     */
    public array $headers;

    /**
     *
     * @param ResponseInterface $response
     * @throws MCException
     */
    public function __construct(ResponseInterface $response)
    {
        if (!in_array($response->getStatusCode(), [200, 201])) {
            throw new MCException($response->getReasonPhrase(), $response->getStatusCode());
        }

        $this->headers = $response->getHeaders();
        $this->code = $response->getStatusCode();
        $this->rawBody = $response->getBody()->getContents();
        $this->body = json_decode($this->rawBody, true);
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getBody(): array
    {
        return $this->body;
    }
}

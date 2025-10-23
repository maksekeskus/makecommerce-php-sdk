<?php

declare(strict_types=1);

namespace MakeCommerce\Actions;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use JsonSchema\Validator;
use MakeCommerce\MCException;
use MakeCommerce\MCResponse;

abstract class Action
{
    public const DIR_SCHEMAS = __DIR__ . '/../Schemas/';
    protected GuzzleClient $client;

    protected array $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    public function __construct(GuzzleClient $httpClient)
    {
        $this->client = $httpClient;
    }

    /**
     * @param array $data
     * @return MCResponse
     * @throws MCException
     */
    public function action(array $data = []): MCResponse
    {
        try {
            $this->validate($this->getSchemaName(), $data);
            if ($this->getMethod() === Method::POST) {
                return $this->makeApiRequest($this->getMethod(), $this->getEndpoint(), [], $data);
            }
            return $this->makeApiRequest($this->getMethod(), $this->getEndpoint(), $data);
        } catch (Exception $e) {
            throw new MCException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return Method
     */
    abstract protected function getMethod(): Method;

    /**
     * @return string
     */
    abstract protected function getSchemaName(): string;

    /**
     * @return string
     */
    abstract protected function getEndpoint(): string;

    /**
     * @param string $schemaName
     * @param array $data
     * @throws MCException
     */
    protected function validate(string $schemaName, array $data): void
    {
        $schemaPath = self::DIR_SCHEMAS . $schemaName;

        if (empty($data)) {
            return;
        }

        if (!file_exists($schemaPath)) {
            throw new MCException("Schema not found: $schemaName", 500);
        }

        if (!is_dir($schemaPath)) {
            $jsonSchema = json_decode(file_get_contents($schemaPath));
            $jsonData = json_decode(json_encode($data));

            if (is_object($jsonData)) {
                $validator = new Validator();
                $validator->validate($jsonData, $jsonSchema);

                if (!$validator->isValid()) {
                    $errors = [];
                    foreach ($validator->getErrors() as $error) {
                        if (!empty($error['property'])) {
                            $errors[] = $error['property'] . ": " . $error['message'];
                        } else {
                            $errors[] = $error['message'];
                        }
                    }
                    throw new MCException("Validation Errors: " . json_encode($errors, JSON_PRETTY_PRINT), 400);
                }
            } else {
                throw new MCException("Invalid json data format", 400);
            }
        }
    }

    /**
     * @throws MCException
     */
    protected function makeApiRequest(
        Method $method,
        string $endpoint,
        array $params = [],
        array $body = [],
        array $additionalHeaders = []
    ): MCResponse {

        $requestContent = ['headers' => $this->headers];

        if (!empty($additionalHeaders)) {
            $this->headers = array_merge($this->headers, $additionalHeaders);
        }

        if (!empty($params)) {
            $requestContent['query'] = $params;
        }

        try {
            switch ($method) {
                case Method::GET:
                    return new MCResponse($this->client->get($endpoint, $requestContent));
                case Method::POST:
                    $requestContent['body'] = json_encode($body);
                    return new MCResponse($this->client->post($endpoint, $requestContent));
                default:
                    throw new MCException('Incorrect HTTP method!', 400);
            }
        } catch (GuzzleException $e) {
            throw new MCException($e->getMessage(), $e->getCode());
        }
    }
}

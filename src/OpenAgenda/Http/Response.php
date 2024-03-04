<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http;

class Response
{
    protected array $headers;
    protected string $requestedURL;
    protected array $response;
    protected string $body;
    protected array $json;

    public function __construct(array $headers, string $requestedURL, array $response, string $body)
    {
        $this->headers = $headers;
        $this->requestedURL = $requestedURL;
        $this->response = $response;
        $this->body = $body;
        $this->json = $this->parseAsJson($this->body);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getRequestedURL(): string
    {
        return $this->requestedURL;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function getResponseCode(): ?int
    {
        return $this->response['code'] ?? null;
    }

    public function getResponseMessage(): ?string
    {
        return $this->response['message'] ?? null;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getParsedJson(): array
    {
        return $this->json;
    }

    protected function parseAsJson(string $body): array
    {
        $decoded = json_decode($body, true, 512);

        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }
}

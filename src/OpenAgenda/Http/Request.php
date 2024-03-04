<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http;

use DateTime;
use DateTimeZone;
use Exception;
use OWC\OpenAgenda\Http\Handlers\Stack;

class Request
{
    public const ENDPOINT = '';

    protected string $baseURL;
    protected string $username;
    protected string $password;
    protected Stack $responseHandlers;

    public function __construct(array $settings = [])
    {
        $this->baseURL = $settings['rest_api_base_url'];
        $this->username = $settings['rest_api_username'];
        $this->password = $settings['rest_api_password'];
        $this->responseHandlers = Stack::create();
    }

    public function request(string $method = 'GET', array $args = []): array
    {
        try {
            $response = $this->doRequest($method, $args);
        } catch (Exception $exception) {
            $this->logExceptionToTeams($exception);

            throw $exception;
        }

        return $response->getParsedJson();
    }

    protected function doRequest(string $method = 'GET', array $args = []): Response
    {
        $requestArgs = [
            'timeout' => 10,
            'method' => $method,
            'headers' => $this->getHeaders(),
        ];

        if (! empty($args)) {
            $requestArgs['body'] = wp_json_encode($args);
        }

        $response = \wp_safe_remote_request($this->makeURL(), $requestArgs);

        if (\is_wp_error($response)) {
            throw new Exception($response->get_error_message(), 400);
        }

        $response = new Response(
            isset($response['headers']) ? $response['headers']->getAll() : [],
            $this->makeURL(),
            $response['response'],
            $response['body']
        );

        return $this->handleResponse($response);
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => sprintf('Basic %s', base64_encode($this->username . ':' . $this->password)),
            'Content-Type' => 'application/json',
        ];
    }

    protected function makeURL(): string
    {
        return sprintf('%s/%s', untrailingslashit($this->baseURL), untrailingslashit(static::ENDPOINT));
    }

    protected function handleResponse(Response $response): Response
    {
        foreach ($this->responseHandlers->get() as $handler) {
            $response = $handler->handle($response);
        }

        return $response;
    }

    protected function logExceptionToTeams(Exception $exception): void
    {
        $logMethodsByExceptionCode = [
            100 => 'debug', // Is not used since we're only logging from 200 and above.
            200 => 'info',
            250 => 'notice',
            300 => 'warning',
            400 => 'error',
            500 => 'critical',
            550 => 'alert',
            600 => 'emergency',
        ];

        $method = $logMethodsByExceptionCode[$exception->getCode()] ?? 'error';

        resolve('teams')->$method('OpenAgenda', [
            'Domain' => get_site_url(),
            'URI' => $_SERVER['REQUEST_URI'] ?? '',
            'File' => $exception->getFile(),
            'Line' => $exception->getLine(),
            'DateTime' => (new DateTime('now', new DateTimeZone(\wp_timezone_string())))->format('Y-m-d H:i:s'),
            'Message' => $exception->getMessage(),
            'Code' => $exception->getCode(),
            'Requested URL' => $this->makeURL(),
        ]);
    }
}

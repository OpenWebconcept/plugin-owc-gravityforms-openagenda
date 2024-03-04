<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http\Handlers;

use OWC\OpenAgenda\Http\Errors\ServerError;
use OWC\OpenAgenda\Http\Response;

class InternalErrorHandler implements HandlerInterface
{
    public function handle(Response $response): Response
    {
        if ($response->getResponseCode() !== 500) {
            return $response;
        }

        throw ServerError::fromResponse($response);
    }
}

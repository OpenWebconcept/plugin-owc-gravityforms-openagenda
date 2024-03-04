<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http\Handlers;

use OWC\OpenAgenda\Http\Errors\UnauthenticatedError;
use OWC\OpenAgenda\Http\Response;

class UnauthenticatedHandler implements HandlerInterface
{
    public function handle(Response $response): Response
    {
        if (! in_array($response->getResponseCode(), [401, 403])) {
            return $response;
        }

        throw UnauthenticatedError::fromResponse($response);
    }
}

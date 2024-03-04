<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http\Handlers;

use OWC\OpenAgenda\Http\Errors\ResourceNotFoundError;
use OWC\OpenAgenda\Http\Response;

class ResourceNotFoundHandler implements HandlerInterface
{
    public function handle(Response $response): Response
    {
        if ($response->getResponseCode() !== 404) {
            return $response;
        }

        throw ResourceNotFoundError::fromResponse($response);
    }
}

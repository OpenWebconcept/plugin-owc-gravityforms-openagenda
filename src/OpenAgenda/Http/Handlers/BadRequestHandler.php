<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http\Handlers;

use OWC\OpenAgenda\Http\Errors\BadRequestError;
use OWC\OpenAgenda\Http\Response;

class BadRequestHandler implements HandlerInterface
{
    public function handle(Response $response): Response
    {
        if ($response->getResponseCode() !== 400) {
            return $response;
        }

        throw BadRequestError::fromResponse($response);
    }
}

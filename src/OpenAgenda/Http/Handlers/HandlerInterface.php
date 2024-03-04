<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http\Handlers;

use OWC\OpenAgenda\Http\Response;

interface HandlerInterface
{
    public function handle(Response $response): Response;
}

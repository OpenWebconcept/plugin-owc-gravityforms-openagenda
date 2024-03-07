<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http\Endpoints;

use OWC\OpenAgenda\Http\Request;

class GetEventTaxonomies extends Request
{
    public const ENDPOINT = 'wp/v2/taxonomies?type=event';
}

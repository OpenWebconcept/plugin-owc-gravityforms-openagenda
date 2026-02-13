<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http\Endpoints;

use OWC\OpenAgenda\Http\Request;

class GetLocations extends Request
{
    public const ENDPOINT = 'owc/openagenda/v1/locations';

    /**
     * Fetch all locations, handling pagination automatically.
     */
    public function list(): array
    {
        $allResults = [];
        $currentPage = 1;

        do {
            $response = (new self())->appendParametersToURL(['page' => $currentPage])->request('GET');

            $results = $response['results'] ?? [];
            $allResults = array_merge($allResults, $results);

            $totalPages = $response['pagination']['pages']['total'] ?? 1;
            $currentPage++;
        } while ($currentPage <= $totalPages);

        return $allResults;
    }
}

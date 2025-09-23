<?php

namespace OWC\OpenAgenda\Traits;

trait ImageProcessingTrait
{
    protected function urlToBase64(string $url = ''): string
    {
        if ('' === $url) {
            return '';
        }

        $contents = @file_get_contents($url, false, $this->streamContext());

        return $contents ? base64_encode($contents) : '';
    }

    /**
     * SSL is usually not valid in local environments.
     * Disable verifications.
     */
    public function streamContext()
    {
        if ('development' !== ($_ENV['APP_ENV'] ?? '')) {
            return null;
        }

        return stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
    }
}

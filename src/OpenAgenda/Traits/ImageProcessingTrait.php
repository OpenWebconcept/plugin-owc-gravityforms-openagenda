<?php

namespace OWC\OpenAgenda\Traits;

trait ImageProcessingTrait
{
    protected function urlToBase64($url): string
    {
        $contents = file_get_contents($url, false, $this->streamContext());

        return $contents ? base64_encode($contents) : '';
    }

    /**
     * SSL is usually not valid in local environments.
     * Disable verifications.
     */
    public function streamContext()
    {
        if (($_ENV['APP_ENV'] ?? '') !== 'development') {
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

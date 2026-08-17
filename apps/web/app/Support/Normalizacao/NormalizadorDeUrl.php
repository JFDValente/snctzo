<?php

namespace App\Support\Normalizacao;

use Illuminate\Support\Str;

class NormalizadorDeUrl
{
    public function normalizar(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (! Str::startsWith(Str::lower($url), ['http://', 'https://'])) {
            $url = "https://{$url}";
        }

        return $url;
    }
}

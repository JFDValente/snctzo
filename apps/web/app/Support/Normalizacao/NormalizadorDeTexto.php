<?php

namespace App\Support\Normalizacao;

use Illuminate\Support\Str;

class NormalizadorDeTexto
{
    public function normalizar(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = preg_replace('/\s+/u', ' ', trim($valor));

        return $valor === '' ? null : $valor;
    }

    public function normalizarEmail(?string $email): ?string
    {
        $email = $this->normalizar($email);

        return $email === null ? null : Str::lower($email);
    }

    public function chaveDeComparacao(?string $valor): ?string
    {
        $valor = $this->normalizar($valor);

        return $valor === null ? null : Str::lower(Str::ascii($valor));
    }
}

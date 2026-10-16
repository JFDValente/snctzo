<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdicionarParticipanteTardioRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['aluno', 'professor'])],
            'nome' => ['required', 'string', 'max:150'],
            'data' => ['required', 'date_format:Y-m-d'],
            'turno' => ['required', Rule::in(['manha', 'tarde', 'noite'])],
        ];
    }
}

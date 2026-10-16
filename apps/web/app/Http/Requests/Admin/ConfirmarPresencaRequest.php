<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmarPresencaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'data' => ['required', 'date_format:Y-m-d'],
            'turno' => ['required', 'string', Rule::in(['manha', 'tarde', 'noite'])],
            'confirmar' => ['accepted'],
            'alunos' => ['present', 'array'],
            'alunos.*' => ['required', Rule::in(['presente', 'ausente'])],
            'professores' => ['present', 'array'],
            'professores.*' => ['required', Rule::in(['presente', 'ausente'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmar.accepted' => 'Confirme o registro de presença antes de salvar.',
            'required' => 'Informe a presença de todos os participantes.',
            'in' => 'Informe uma situação de presença válida.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Aluno;
use App\Models\Curso;
use App\Models\Professor;
use App\Support\Normalizacao\NormalizadorDeTexto;
use App\Support\Normalizacao\NormalizadorDeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAtividadeRequest extends FormRequest
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
            'token_submissao' => ['required', 'uuid'],
            'instituicao.id' => ['required', 'integer', Rule::exists('instituicoes', 'id')],
            'curso_principal.id' => ['required', 'integer', Rule::exists('cursos', 'id')],
            'professor_responsavel.email' => ['required', 'email:rfc', 'max:254'],
            'professor_responsavel.nome' => ['required', 'string', 'max:150'],
            'atividade.nome' => ['required', 'string', 'max:255'],
            'atividade.participa_dia_20' => ['nullable', 'boolean'],
            'atividade.participa_dia_21' => ['nullable', 'boolean'],
            'atividade.resumo' => ['required', 'string', 'max:3000'],
            'atividade.observacoes' => ['nullable', 'string', 'max:5000'],
            'atividade.instagram' => ['nullable', 'string', 'max:255'],
            'atividade.facebook' => ['nullable', 'string', 'max:255'],
            'atividade.site' => ['nullable', 'url', 'max:2048'],
            'atividade.outros_links' => ['nullable', 'string'],
            'participantes' => ['required', 'array', 'min:1'],
            'participantes.*.tipo' => ['required', Rule::in(['aluno', 'professor'])],
            'participantes.*.id' => ['nullable', 'integer'],
            'participantes.*.nome' => ['nullable', 'string', 'max:150'],
            'ciente_responsavel' => ['accepted'],
            'ciente_banner' => ['accepted'],
            'ciente_espaco_professor' => ['accepted'],
            'ciente_montagem' => ['accepted'],
            'ciente_atividades_interativas' => ['accepted'],
            'ciente_sem_comercio' => ['accepted'],
            'ciente_antecedencia' => ['accepted'],
            'ciente_sem_alcool_objetos' => ['accepted'],
            'ciente_voluntariado' => ['accepted'],
            'conformidade_politica' => ['accepted'],
            'conformidade_material_perigoso' => ['accepted'],
            'conformidade_alcool' => ['accepted'],
            'conformidade_drogas_armas' => ['accepted'],
            'conformidade_pudor' => ['accepted'],
            'conformidade_violencia_discriminacao' => ['accepted'],
            'cessao_imagem' => ['accepted'],
            'confirmacao_presenca' => ['accepted'],
            'ciencia_geral' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Este campo é obrigatório.',
            'accepted' => 'Este aceite é obrigatório.',
            'array' => 'Informe uma lista válida.',
            'boolean' => 'Informe uma opção válida.',
            'email' => 'Informe um e-mail válido.',
            'exists' => 'O cadastro informado não existe.',
            'in' => 'Informe uma opção válida.',
            'integer' => 'Informe um número válido.',
            'min' => 'Informe a quantidade mínima exigida.',
            'url' => 'Informe uma URL válida.',
            'max' => 'O campo ultrapassa o limite permitido.',
            'uuid' => 'O token de submissão é inválido.',
            'website.max' => 'Não foi possível enviar a inscrição.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('atividade.participa_dia_20') || $this->boolean('atividade.participa_dia_21')) {
                $this->validarRelacionamentos($validator);
                $this->validarParticipantes($validator);

                return;
            }

            $validator->errors()->add('atividade.participa_dia_20', 'Selecione pelo menos um dia de participação.');
        });
    }

    protected function prepareForValidation(): void
    {
        $texto = new NormalizadorDeTexto;
        $url = new NormalizadorDeUrl;
        $dados = $this->all();

        foreach ([
            'professor_responsavel.nome', 'atividade.nome', 'atividade.resumo',
            'atividade.observacoes', 'atividade.instagram', 'atividade.facebook',
            'atividade.outros_links',
        ] as $caminho) {
            data_set($dados, $caminho, $texto->normalizar(data_get($dados, $caminho)));
        }

        data_set($dados, 'atividade.site', $url->normalizar(data_get($dados, 'atividade.site')));
        data_set($dados, 'professor_responsavel.email', $texto->normalizarEmail(data_get($dados, 'professor_responsavel.email')));

        foreach (data_get($dados, 'participantes', []) as $indice => $participante) {
            data_set($dados, "participantes.{$indice}.nome", $texto->normalizar($participante['nome'] ?? null));
        }

        $this->replace($dados);
    }

    private function validarRelacionamentos(Validator $validator): void
    {
        $texto = new NormalizadorDeTexto;
        $instituicaoId = $this->integer('instituicao.id') ?: null;
        $cursoId = $this->integer('curso_principal.id') ?: null;
        $professor = Professor::query()
            ->with('instituicao:id,nome')
            ->where('email', $this->input('professor_responsavel.email'))
            ->first();

        if ($cursoId !== null && ! $validator->errors()->has('curso_principal.id')) {
            $curso = Curso::query()->find($cursoId);

            if ($curso !== null && (int) $curso->instituicao_id !== $instituicaoId) {
                $validator->errors()->add('curso_principal.id', 'O curso principal não pertence à instituição selecionada.');
            }
        }

        if ($professor !== null && (int) $professor->instituicao_id !== $instituicaoId) {
            $validator->errors()->add(
                'professor_responsavel.email',
                "E-mail já cadastrado para um professor da instituição {$professor->instituicao->nome}.",
            );

            return;
        }

        if ($professor !== null && $texto->chaveDeComparacao($professor->nome) !== $texto->chaveDeComparacao($this->input('professor_responsavel.nome'))) {
            $validator->errors()->add('professor_responsavel.email', 'E-mail já utilizado por outro professor.');
        }
    }

    private function validarParticipantes(Validator $validator): void
    {
        $texto = new NormalizadorDeTexto;
        $vistos = [];

        foreach ($this->input('participantes', []) as $indice => $participante) {
            $tipo = $participante['tipo'] ?? null;
            $id = isset($participante['id']) ? (int) $participante['id'] : null;

            if ($tipo === 'aluno') {
                $this->validarAlunoParticipante($validator, $indice, $participante, $id, $texto, $vistos);
            }

            if ($tipo === 'professor') {
                $this->validarProfessorParticipante($validator, $indice, $participante, $id, $texto, $vistos);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $participante
     * @param  array<string, bool>  $vistos
     */
    private function validarAlunoParticipante(Validator $validator, int $indice, array $participante, ?int $id, NormalizadorDeTexto $texto, array &$vistos): void
    {
        if ($id !== null) {
            $aluno = Aluno::query()->find($id);

            if ($aluno === null) {
                $validator->errors()->add("participantes.{$indice}.id", 'O aluno informado não existe.');
            }

            $this->registrarDuplicidade($validator, $vistos, "aluno:{$id}", $indice);

            return;
        }

        $chaveNome = $texto->chaveDeComparacao($participante['nome'] ?? null);

        if ($chaveNome === null) {
            $validator->errors()->add("participantes.{$indice}.nome", 'Informe o nome completo do aluno.');

            return;
        }

        $this->registrarDuplicidade($validator, $vistos, "aluno-novo:{$chaveNome}", $indice);
    }

    /**
     * @param  array<string, mixed>  $participante
     * @param  array<string, bool>  $vistos
     */
    private function validarProfessorParticipante(Validator $validator, int $indice, array $participante, ?int $id, NormalizadorDeTexto $texto, array &$vistos): void
    {
        if ($id !== null) {
            if (! Professor::query()->whereKey($id)->exists()) {
                $validator->errors()->add("participantes.{$indice}.id", 'O professor informado não existe.');
            }

            $this->registrarDuplicidade($validator, $vistos, "professor:{$id}", $indice);

            return;
        }

        $nome = $texto->chaveDeComparacao($participante['nome'] ?? null);
        if ($nome === null) {
            $validator->errors()->add("participantes.{$indice}.nome", 'Informe o nome completo do professor.');

            return;
        }

        $this->registrarDuplicidade($validator, $vistos, "professor-novo:{$nome}", $indice);
    }

    /**
     * @param  array<string, bool>  $vistos
     */
    private function registrarDuplicidade(Validator $validator, array &$vistos, string $chave, int $indice): void
    {
        if (isset($vistos[$chave])) {
            $validator->errors()->add("participantes.{$indice}", 'Um participante não pode ser incluído duas vezes.');
        }

        $vistos[$chave] = true;
    }
}

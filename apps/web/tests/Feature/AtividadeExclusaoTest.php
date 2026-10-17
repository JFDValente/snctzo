<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Atividade;
use App\Models\Curso;
use App\Models\Instituicao;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AtividadeExclusaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrador_exclui_atividade_sem_remover_participantes(): void
    {
        [$atividade, $aluno] = $this->criarAtividadeComParticipante('Atividade removida');
        $administrador = User::factory()->create();

        $this->actingAs($administrador)
            ->delete(route('admin.atividades.destroy', $atividade))
            ->assertRedirect(route('admin.atividades.index'))
            ->assertSessionHas('sucesso', 'Atividade excluída com sucesso.');

        $this->assertSoftDeleted('atividades', ['id' => $atividade->id]);
        $this->assertNull(Atividade::find($atividade->id));
        $this->assertNotNull(Atividade::withTrashed()->find($atividade->id));
        $this->assertDatabaseHas('atividade_aluno', [
            'atividade_id' => $atividade->id,
            'aluno_id' => $aluno->id,
        ]);
        $this->assertDatabaseHas('alunos', ['id' => $aluno->id]);

        $this->actingAs($administrador)
            ->get(route('admin.atividades.show', $atividade->id))
            ->assertNotFound();
    }

    public function test_atividade_excluida_nao_aparece_nos_modulos_nem_nos_relatorios(): void
    {
        [$atividade] = $this->criarAtividadeComParticipante('Atividade invisível');
        $administrador = User::factory()->create();
        $atividade->delete();

        $this->actingAs($administrador)
            ->get(route('admin.atividades.index'))
            ->assertOk()
            ->assertDontSee($atividade->nome);

        $this->actingAs($administrador)
            ->get(route('admin.presencas.index', ['data' => '2026-10-20', 'turno' => 'manha']))
            ->assertOk()
            ->assertDontSee($atividade->nome);

        $atividadesCsv = $this->actingAs($administrador)
            ->get(route('admin.atividades.exportar'));
        $atividadesCsv->assertOk();
        $this->assertStringNotContainsString($atividade->nome, $atividadesCsv->streamedContent());

        $presencasCsv = $this->actingAs($administrador)
            ->get(route('admin.atividades.presencas.exportar'));
        $presencasCsv->assertOk();
        $this->assertStringNotContainsString($atividade->nome, $presencasCsv->streamedContent());
    }

    public function test_operador_de_presenca_nao_pode_excluir_atividade(): void
    {
        [$atividade] = $this->criarAtividadeComParticipante('Atividade protegida');
        $operador = User::factory()->create([
            'perfil' => User::PERFIL_OPERADOR_PRESENCA,
        ]);

        $this->actingAs($operador)
            ->delete(route('admin.atividades.destroy', $atividade))
            ->assertForbidden();

        $this->assertNotNull(Atividade::find($atividade->id));
    }

    /**
     * @return array{Atividade, Aluno}
     */
    private function criarAtividadeComParticipante(string $nome): array
    {
        $instituicao = Instituicao::create(['nome' => 'Unidade de teste']);
        $curso = Curso::create([
            'instituicao_id' => $instituicao->id,
            'nome' => 'Curso de teste',
        ]);
        $professor = Professor::create([
            'instituicao_id' => $instituicao->id,
            'nome' => 'Responsável de teste',
            'email' => 'responsavel@example.test',
        ]);
        $atividade = Atividade::create([
            'token_submissao' => (string) Str::uuid(),
            'curso_id' => $curso->id,
            'professor_responsavel_id' => $professor->id,
            'nome' => $nome,
            'participa_dia_20' => true,
            'participa_dia_21' => false,
            'resumo' => 'Resumo de teste.',
            'termos_aceitos_em' => now(),
            'versao_termos' => '2026.1',
        ]);
        $aluno = Aluno::create([
            'curso_id' => null,
            'nome' => 'Participante de teste',
        ]);
        $atividade->alunos()->attach($aluno->id);

        return [$atividade, $aluno];
    }
}

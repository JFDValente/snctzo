<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_a_raiz_redireciona_para_as_inscricoes(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/inscricoes');
    }

    public function test_a_rota_publica_antiga_informa_o_encerramento_das_inscricoes(): void
    {
        $this->get('/inscricoes')
            ->assertOk()
            ->assertSee('O prazo de inscrições já foi encerrado');

        $this->post('/inscricoes')->assertStatus(405);
    }
}

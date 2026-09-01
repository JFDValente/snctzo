<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atividade;
use Illuminate\Contracts\View\View;

class AtividadeController extends Controller
{
    public function index(): View
    {
        $atividades = Atividade::query()
            ->with(['curso.instituicao'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.atividades.index', compact('atividades'));
    }

    public function show(Atividade $atividade): View
    {
        $atividade->load([
            'curso.instituicao',
            'professorResponsavel.instituicao',
            'alunos.curso',
            'professores.instituicao',
        ]);

        return view('admin.atividades.show', compact('atividade'));
    }
}

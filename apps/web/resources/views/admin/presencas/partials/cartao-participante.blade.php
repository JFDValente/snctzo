<article class="admin-presenca-item">
    <div>
        <strong>{{ $participante->nome }}</strong>
        <span>{{ ucfirst($tipo) }}{{ $participante->pivot?->cadastrado_tardiamente ? ' (cadastro tardio)' : '' }}</span>
    </div>
    <fieldset class="admin-situacao" data-situacao-presenca>
        <legend>Situação</legend>
        <label><input name="{{ $tipo === 'aluno' ? 'alunos' : 'professores' }}[{{ $participante->id }}]" type="radio" value="presente" required @checked(old(($tipo === 'aluno' ? 'alunos' : 'professores').".{$participante->id}", $status) === 'presente')> Presente</label>
        <label><input name="{{ $tipo === 'aluno' ? 'alunos' : 'professores' }}[{{ $participante->id }}]" type="radio" value="ausente" @checked(old(($tipo === 'aluno' ? 'alunos' : 'professores').".{$participante->id}", $status) === 'ausente')> Ausente</label>
    </fieldset>
</article>

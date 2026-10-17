<article class="admin-presenca-item" data-nome-participante="{{ $participante->nome }}">
    <div>
        <strong>{{ $participante->nome }}</strong>
        <span>{{ ucfirst($tipo) }}{{ $participante->pivot?->cadastrado_tardiamente ? ' (cadastro tardio)' : '' }}</span>
    </div>
    <fieldset class="admin-situacao {{ ($temErroDeParticipantes ?? false) ? 'admin-situacao--erro' : '' }}" data-situacao-presenca>
        <legend>Situação</legend>
        <label><input name="{{ $tipo === 'aluno' ? 'alunos' : 'professores' }}[{{ $participante->id }}]" type="radio" value="presente" required aria-invalid="{{ ($temErroDeParticipantes ?? false) ? 'true' : 'false' }}" @checked(old(($tipo === 'aluno' ? 'alunos' : 'professores').".{$participante->id}", $status) === 'presente')> Presente</label>
        <label><input name="{{ $tipo === 'aluno' ? 'alunos' : 'professores' }}[{{ $participante->id }}]" type="radio" value="ausente" aria-invalid="{{ ($temErroDeParticipantes ?? false) ? 'true' : 'false' }}" @checked(old(($tipo === 'aluno' ? 'alunos' : 'professores').".{$participante->id}", $status) === 'ausente')> Ausente</label>
    </fieldset>
</article>

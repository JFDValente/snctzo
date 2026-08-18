<fieldset class="etapa" data-etapa="2">
    <legend>2. Instituição, curso e responsável</legend>

    <div class="grupo-campos">
        <div class="campo campo--largo">
            <label for="instituicao">Instituição <span aria-hidden="true">*</span></label>
            <select id="instituicao" data-instituicao required>
                <option value="">Carregando instituições…</option>
            </select>
            <input type="hidden" name="instituicao[id]" data-instituicao-id>
            <p class="campo__ajuda">Selecione uma instituição cadastrada ou escolha “Outra instituição”.</p>
        </div>
        <div class="campo campo--largo" data-instituicao-nova>
            <label for="instituicao-nome">Nome da instituição <span aria-hidden="true">*</span></label>
            <input id="instituicao-nome" name="instituicao[nome]" type="text" maxlength="150" autocomplete="organization">
        </div>
        <div class="campo">
            <label for="instituicao-instagram">Instagram</label>
            <input id="instituicao-instagram" name="instituicao[instagram]" type="text" maxlength="255">
        </div>
        <div class="campo">
            <label for="instituicao-facebook">Facebook</label>
            <input id="instituicao-facebook" name="instituicao[facebook]" type="text" maxlength="255">
        </div>
        <div class="campo campo--largo">
            <label for="instituicao-site">Site</label>
            <input id="instituicao-site" name="instituicao[site]" type="text" inputmode="url" maxlength="2048">
        </div>
        <div class="campo campo--largo">
            <label for="instituicao-outros-links">Outros links</label>
            <textarea id="instituicao-outros-links" name="instituicao[outros_links]" rows="3"></textarea>
        </div>
        <div class="campo campo--largo">
            <label for="curso-principal">Curso principal <span aria-hidden="true">*</span></label>
            <select id="curso-principal" data-curso-principal required disabled>
                <option value="">Selecione primeiro a instituição</option>
            </select>
            <input type="hidden" name="curso_principal[id]" data-curso-principal-id>
        </div>
        <div class="campo campo--largo" data-curso-principal-novo>
            <label for="curso-principal-nome">Novo curso <span aria-hidden="true">*</span></label>
            <input id="curso-principal-nome" name="curso_principal[nome]" type="text" maxlength="150">
        </div>
    </div>

    <section class="secao-interna" aria-labelledby="titulo-responsavel">
        <h2 id="titulo-responsavel">Professor responsável</h2>
        <p>O responsável é o contato oficial da inscrição e deve pertencer à instituição do curso principal.</p>
        <div class="grupo-campos">
            <div class="campo">
                <label for="responsavel-email">E-mail <span aria-hidden="true">*</span></label>
                <input id="responsavel-email" name="professor_responsavel[email]" type="email" maxlength="254" autocomplete="email" required data-professor-responsavel-email>
            </div>
            <div class="campo">
                <label for="responsavel-nome">Nome <span aria-hidden="true">*</span></label>
                <input id="responsavel-nome" name="professor_responsavel[nome]" type="text" maxlength="150" autocomplete="name" required data-professor-responsavel-nome>
            </div>
        </div>
        <p class="mensagem-campo" data-professor-responsavel-mensagem aria-live="polite"></p>
    </section>
</fieldset>

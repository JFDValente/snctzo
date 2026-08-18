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
        <div class="campo campo--link-instituicao">
            <label for="instituicao-instagram">
                <svg class="icone-rede" aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M7.5 2A5.5 5.5 0 0 0 2 7.5v9A5.5 5.5 0 0 0 7.5 22h9a5.5 5.5 0 0 0 5.5-5.5v-9A5.5 5.5 0 0 0 16.5 2h-9Zm0 2h9A3.5 3.5 0 0 1 20 7.5v9a3.5 3.5 0 0 1-3.5 3.5h-9A3.5 3.5 0 0 1 4 16.5v-9A3.5 3.5 0 0 1 7.5 4ZM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm0 2a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm5.25-2.75a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Z"/></svg>
                <span>Instagram</span>
            </label>
            <input id="instituicao-instagram" name="instituicao[instagram]" type="text" maxlength="255">
        </div>
        <div class="campo campo--link-instituicao">
            <label for="instituicao-facebook">
                <svg class="icone-rede" aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-8h2.75l.41-3h-3.16V8.08c0-.87.24-1.46 1.49-1.46h1.6V3.94c-.28-.04-1.23-.12-2.34-.12-2.31 0-3.89 1.41-3.89 4v2.18H7.75v3h2.61v8h3.14Z"/></svg>
                <span>Facebook</span>
            </label>
            <input id="instituicao-facebook" name="instituicao[facebook]" type="text" maxlength="255">
        </div>
        <div class="campo campo--link-instituicao">
            <label for="instituicao-site">Site</label>
            <input id="instituicao-site" name="instituicao[site]" type="text" inputmode="url" maxlength="2048">
        </div>
        <div class="campo campo--link-instituicao">
            <label for="instituicao-outros-links">Outros links</label>
            <textarea id="instituicao-outros-links" name="instituicao[outros_links]" rows="2"></textarea>
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

<fieldset class="etapa" data-etapa="2">
    <legend>2. Unidade acadêmica, curso e responsável</legend>

    <div class="grupo-campos">
        <div class="campo">
            <label for="instituicao">Unidade acadêmica <span aria-hidden="true">*</span></label>
            <select id="instituicao" data-instituicao required><option value="">Carregando unidades acadêmicas…</option></select>
            <input type="hidden" name="instituicao[id]" data-instituicao-id>
            <p class="campo__ajuda">Selecione uma unidade acadêmica cadastrada.</p>
        </div>
        <div class="campo">
            <label for="curso-principal">Curso principal <span aria-hidden="true">*</span></label>
            <select id="curso-principal" data-curso-principal required disabled><option value="">Selecione primeiro a unidade acadêmica</option></select>
            <input type="hidden" name="curso_principal[id]" data-curso-principal-id>
        </div>
    </div>

    <section class="secao-interna" aria-labelledby="titulo-responsavel">
        <h2 id="titulo-responsavel">Professor responsável</h2>
        <p>O responsável é o contato oficial da inscrição e deve pertencer à unidade acadêmica do curso principal.</p>
        <div class="grupo-campos">
            <div class="campo">
                <label for="responsavel-email">E-mail <span aria-hidden="true">*</span></label>
                <input id="responsavel-email" name="professor_responsavel[email]" type="email" maxlength="254" autocomplete="email" required data-professor-responsavel-email>
            </div>
            <div class="campo">
                <label for="responsavel-nome">Nome completo <span aria-hidden="true">*</span></label>
                <input id="responsavel-nome" name="professor_responsavel[nome]" type="text" maxlength="150" autocomplete="name" required data-professor-responsavel-nome>
            </div>
        </div>
        <p id="professor-responsavel-mensagem" class="mensagem-campo" data-professor-responsavel-mensagem aria-live="polite"></p>
    </section>

    <section class="secao-interna" aria-labelledby="titulo-links-atividade">
        <h2 id="titulo-links-atividade">Redes sociais e links da atividade</h2>
        <div class="grupo-campos">
            <div class="campo campo--link-instituicao"><label for="atividade-instagram">Instagram</label><input id="atividade-instagram" name="atividade[instagram]" type="text" maxlength="255"><p class="campo__ajuda">se for colocar mais de um instagram, separe-os por vírgula. Ex: @snctzo, @uerjzonaoeste</p></div>
            <div class="campo campo--link-instituicao"><label for="atividade-facebook">Facebook</label><input id="atividade-facebook" name="atividade[facebook]" type="text" maxlength="255"></div>
            <div class="campo campo--link-instituicao"><label for="atividade-site">Site</label><input id="atividade-site" name="atividade[site]" type="text" inputmode="url" maxlength="2048"></div>
            <div class="campo campo--link-instituicao"><label for="atividade-outros-links">Outros links</label><textarea id="atividade-outros-links" name="atividade[outros_links]" rows="1"></textarea></div>
        </div>
    </section>
</fieldset>

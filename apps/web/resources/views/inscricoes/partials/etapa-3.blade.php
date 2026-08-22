<fieldset class="etapa" data-etapa="3">
    <legend>3. Dados da atividade e participantes</legend>

    <div class="grupo-campos">
        <div class="campo campo--largo">
            <label for="atividade-nome">Nome da atividade <span aria-hidden="true">*</span></label>
            <input id="atividade-nome" name="atividade[nome]" type="text" maxlength="255" required>
            <p class="campo__ajuda">Exemplos: Oficina — Desvendando os números da vida; Experiência — Brincando com formas e volumes.</p>
        </div>
        <fieldset class="campo campo--opcoes">
            <legend>Forma de apresentação <span aria-hidden="true">*</span></legend>
            <label><input name="atividade[forma_apresentacao]" type="radio" value="presencial" required> Presencial</label>
            <label><input name="atividade[forma_apresentacao]" type="radio" value="remota"> Remota</label>
        </fieldset>
        <fieldset class="campo campo--largo campo--opcoes">
            <legend>Dias de participação <span aria-hidden="true">*</span></legend>
            <label><input name="atividade[participa_dia_20]" type="checkbox" value="1"> 20/10/2026 — das 9h às 21h</label>
            <label><input name="atividade[participa_dia_21]" type="checkbox" value="1"> 21/10/2026 — das 9h às 17h</label>
        </fieldset>
    </div>

    <label class="aceite">
        <input name="ciente_responsavel" type="checkbox" value="1" required>
        <span><strong>Importante:</strong> o nome do professor responsável deve constar na lista de participantes caso ele deva ser incluído no resumo ou e-book. <strong>Ciente.</strong></span>
    </label>

    <section class="secao-interna" aria-labelledby="titulo-participantes">
        <div class="secao-interna__cabecalho">
            <div>
                <h2 id="titulo-participantes">Participantes</h2>
                <p>Inclua pelo menos uma pessoa. O responsável não é incluído automaticamente.</p>
            </div>
        </div>
        <div class="participante participante-ficha" data-participante-ficha></div>
        <div class="participantes-tabela" data-participantes-tabela hidden>
            <table>
                <thead>
                    <tr>
                        <th scope="col">Tipo</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Curso ou unidade acadêmica</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody data-participantes></tbody>
            </table>
        </div>
        <div data-participantes-enviados></div>
        <p class="mensagem-campo" data-participantes-mensagem aria-live="polite"></p>
    </section>

    <div class="grupo-campos">
        <div class="campo campo--largo">
            <label for="atividade-resumo">Resumo da atividade <span aria-hidden="true">*</span></label>
            <textarea id="atividade-resumo" name="atividade[resumo]" rows="6" maxlength="3000" required></textarea>
            <p class="campo__ajuda">Espaço destinado à descrição da atividade. Informe o tipo de abordagem e o resultado esperado para o público. Esta informação constará no resumo ou e-book. Limite de 3.000 caracteres.</p>
        </div>
        <div class="campo campo--largo">
            <label for="atividade-observacoes">Observações</label>
            <textarea id="atividade-observacoes" name="atividade[observacoes]" rows="6" maxlength="5000"></textarea>
            <p class="campo__ajuda">Informe preferências de localização, necessidade de tomada elétrica ou outras informações. Cada estande contará com sete mesas e cinco cadeiras.</p>
        </div>
    </div>
</fieldset>

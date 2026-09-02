const normalizarChave = (valor) => valor
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .trim()
    .replace(/\s+/g, ' ')
    .toLocaleLowerCase('pt-BR');

const iniciarParticipantes = async () => {
    const formulario = document.querySelector('#formulario-inscricao');
    const ficha = formulario?.querySelector('[data-participante-ficha]');
    const lista = formulario?.querySelector('[data-participantes]');
    const enviados = formulario?.querySelector('[data-participantes-enviados]');
    const tabela = formulario?.querySelector('[data-participantes-tabela]');
    const mensagemGeral = formulario?.querySelector('[data-participantes-mensagem]');

    if (!formulario || !ficha || !lista || !enviados || !tabela || !mensagemGeral) {
        return;
    }

    let alunos = [];
    let participantes = [];

    const chaveDoParticipante = (participante) => {
        if (participante.tipo === 'aluno' && participante.id) {
            return `aluno:${participante.id}`;
        }

        if (participante.tipo === 'aluno') {
            const nome = normalizarChave(participante.nome);

            return nome ? `aluno-novo:${nome}` : null;
        }

        if (participante.id) {
            return `professor:${participante.id}`;
        }

        const nome = normalizarChave(participante.nome);
        return nome ? `professor-novo:${nome}` : null;
    };

    const adicionarCampoOculto = (nome, valor) => {
        if (valor === null || valor === undefined || valor === '') {
            return;
        }

        const campo = document.createElement('input');
        campo.type = 'hidden';
        campo.name = nome;
        campo.value = valor;
        enviados.appendChild(campo);
    };

    const atualizarCamposEnviados = () => {
        enviados.replaceChildren();

        participantes.forEach((participante, indice) => {
            const raiz = `participantes[${indice}]`;
            adicionarCampoOculto(`${raiz}[tipo]`, participante.tipo);
            adicionarCampoOculto(`${raiz}[id]`, participante.id);
            adicionarCampoOculto(`${raiz}[nome]`, participante.nome);

        });
    };

    const renderizarTabela = () => {
        lista.replaceChildren();
        tabela.hidden = participantes.length === 0;

        participantes.forEach((participante, indice) => {
            const linha = document.createElement('tr');
            linha.dataset.participanteLinha = indice;
            const tipo = participante.tipo === 'aluno' ? 'Aluno' : 'Professor';
            [
                tipo,
                participante.nome,
            ].forEach((valor) => {
                const celula = document.createElement('td');
                celula.textContent = valor;
                linha.appendChild(celula);
            });

            const acoes = document.createElement('td');
            const remover = document.createElement('button');
            remover.className = 'botao botao--secundario participante__remover';
            remover.type = 'button';
            remover.dataset.removerParticipante = indice;
            remover.setAttribute('aria-label', `Remover ${tipo.toLocaleLowerCase('pt-BR')} ${participante.nome}`);
            remover.textContent = 'X';
            acoes.appendChild(remover);
            linha.appendChild(acoes);
            lista.appendChild(linha);
        });

        atualizarCamposEnviados();
    };

    const limparMensagem = () => {
        mensagemGeral.textContent = '';
        mensagemGeral.classList.remove('mensagem-campo--erro');
    };

    const criarParticipante = (dados) => {
        const chave = chaveDoParticipante(dados);

        if (chave !== null && participantes.some((participante) => chaveDoParticipante(participante) === chave)) {
            mensagemGeral.textContent = 'Um participante não pode ser incluído duas vezes.';
            mensagemGeral.classList.add('mensagem-campo--erro');

            return false;
        }

        participantes.push(dados);
        limparMensagem();
        renderizarTabela();

        return true;
    };

    const camposDaFichaSaoValidos = () => {
        const campos = [...ficha.querySelectorAll('input, select, textarea')]
            .filter((campo) => !campo.disabled && campo.type !== 'hidden');
        const campoInvalido = campos.find((campo) => !campo.checkValidity());

        if (!campoInvalido) {
            return true;
        }

        campoInvalido.reportValidity();

        return false;
    };

    const renderizarFicha = (tipo = 'aluno') => {
        ficha.innerHTML = `
            <div class="participante-ficha__tipo" role="group" aria-labelledby="tipo-participante">
                <span id="tipo-participante">Tipo de participante <span aria-hidden="true">*</span></span>
                <label><input data-tipo name="tipo-participante" type="radio" value="aluno" ${tipo === 'aluno' ? 'checked' : ''} required> Aluno</label>
                <label><input data-tipo name="tipo-participante" type="radio" value="professor" ${tipo === 'professor' ? 'checked' : ''}> Professor</label>
            </div>
            <div data-corpo-participante></div>
            <div class="participante-ficha__acoes">
                <button class="botao" type="button" data-salvar-participante aria-label="Adicionar participante">+</button>
            </div>
            <p class="mensagem-campo" data-participante-mensagem aria-live="polite"></p>
        `;

        const corpo = ficha.querySelector('[data-corpo-participante]');
        const mensagem = ficha.querySelector('[data-participante-mensagem]');

        const configurarSalvar = (obterDados) => {
            const botaoAnterior = ficha.querySelector('[data-salvar-participante]');
            const botao = botaoAnterior.cloneNode(true);
            botaoAnterior.replaceWith(botao);

            botao.addEventListener('click', () => {
                if (!camposDaFichaSaoValidos()) {
                    return;
                }

                if (criarParticipante(obterDados())) {
                    renderizarFicha();
                }
            });
        };

        const renderizarAluno = () => {
            corpo.innerHTML = `
                <div class="grupo-campos">
                    <div class="campo campo--largo">
                        <label for="participante-aluno">Nome (Completo) <span aria-hidden="true">*</span></label>
                        <input id="participante-aluno" data-aluno-autocomplete data-participante-nome type="text" list="participante-alunos" maxlength="150" autocomplete="off" placeholder="Digite ou escolha um aluno cadastrado" required>
                        <datalist id="participante-alunos"></datalist>
                    </div>
                </div>
            `;

            const autocomplete = corpo.querySelector('[data-aluno-autocomplete]');
            const listaAlunos = corpo.querySelector('datalist');
            const alunosPorNome = new Map();
            let alunoId = '';

            alunos.forEach((aluno) => {
                alunosPorNome.set(aluno.nome, aluno);
                const opcao = document.createElement('option');
                opcao.value = aluno.nome;
                listaAlunos.appendChild(opcao);
            });

            const liberarAlunoNovo = () => {
                alunoId = '';
                autocomplete.readOnly = false;
            };

            autocomplete.addEventListener('input', () => {
                const aluno = alunosPorNome.get(autocomplete.value);

                if (!aluno) {
                    liberarAlunoNovo();

                    return;
                }

                alunoId = String(aluno.id);
                autocomplete.value = aluno.nome;
                autocomplete.readOnly = true;
            });

            configurarSalvar(() => ({
                tipo: 'aluno',
                id: alunoId,
                nome: autocomplete.value.trim(),
            }));
        };

        const renderizarProfessor = () => {
            corpo.innerHTML = `
                <div class="grupo-campos">
                    <div class="campo campo--largo">
                        <label for="participante-nome">Nome (Completo) <span aria-hidden="true">*</span></label>
                        <input id="participante-nome" data-participante-nome type="text" maxlength="150" required>
                    </div>
                </div>
            `;

            const nome = corpo.querySelector('[data-participante-nome]');

            configurarSalvar(() => ({
                tipo: 'professor',
                id: '',
                nome: nome.value.trim(),
            }));
        };

        ficha.querySelectorAll('[data-tipo]').forEach((radio) => radio.addEventListener('change', () => {
            limparMensagem();

            if (radio.value === 'aluno' && radio.checked) {
                renderizarAluno();
            }

            if (radio.value === 'professor' && radio.checked) {
                renderizarProfessor();
            }
        }));

        if (tipo === 'aluno') {
            renderizarAluno();
        } else {
            renderizarProfessor();
        }
    };

    lista.addEventListener('click', (evento) => {
        const botao = evento.target.closest('[data-remover-participante]');

        if (!botao) {
            return;
        }

        participantes.splice(Number(botao.dataset.removerParticipante), 1);
        limparMensagem();
        renderizarTabela();
    });

    formulario.addEventListener('inscricao:instituicao-alterada', ({ detail }) => {
        alunos = detail.alunos;
        participantes = [];
        limparMensagem();
        renderizarTabela();
        renderizarFicha();
    });

    renderizarTabela();
    renderizarFicha();
};

document.addEventListener('DOMContentLoaded', iniciarParticipantes);

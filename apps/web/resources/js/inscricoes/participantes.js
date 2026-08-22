const OUTRA_INSTITUICAO = '__nova__';
const NOVO_CURSO = '__novo__';

const normalizarChave = (valor) => valor
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .trim()
    .replace(/\s+/g, ' ')
    .toLocaleLowerCase('pt-BR');

const criarOpcao = (valor, texto) => {
    const opcao = document.createElement('option');
    opcao.value = valor;
    opcao.textContent = texto;

    return opcao;
};

const buscarProfessor = async (email) => {
    const resposta = await fetch(`/inscricoes/professores/busca?email=${encodeURIComponent(email)}`, {
        headers: { Accept: 'application/json' },
    });

    if (!resposta.ok) {
        throw new Error('Não foi possível consultar o professor.');
    }

    return resposta.json();
};

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

    let cursos = [];
    let alunos = [];
    let instituicoes = [];
    let participantes = [];

    try {
        const resposta = await fetch('/inscricoes/catalogo/instituicoes', {
            headers: { Accept: 'application/json' },
        });

        if (!resposta.ok) {
            throw new Error();
        }

        instituicoes = (await resposta.json()).instituicoes;
    } catch {
        mensagemGeral.textContent = 'Não foi possível carregar as unidades acadêmicas para professores participantes.';
    }

    const preencherCursos = (seletor) => {
        seletor.replaceChildren(
            criarOpcao('', 'Selecione o curso'),
            ...cursos.map((curso) => criarOpcao(curso.id, curso.nome)),
            criarOpcao(NOVO_CURSO, 'Outro curso'),
        );
    };

    const preencherInstituicoes = (seletor) => {
        seletor.replaceChildren(
            criarOpcao('', 'Selecione a unidade acadêmica'),
            ...instituicoes.map((instituicao) => criarOpcao(instituicao.id, instituicao.nome)),
            criarOpcao(OUTRA_INSTITUICAO, 'Outra unidade acadêmica'),
        );
    };

    const chaveDoParticipante = (participante) => {
        if (participante.tipo === 'aluno' && participante.id) {
            return `aluno:${participante.id}`;
        }

        if (participante.tipo === 'aluno') {
            const curso = participante.curso.id || normalizarChave(participante.curso.nome ?? '');
            const nome = normalizarChave(participante.nome);

            return nome && curso ? `aluno-novo:${nome}:${curso}` : null;
        }

        if (participante.id) {
            return `professor:${participante.id}`;
        }

        return participante.email ? `professor-novo:${normalizarChave(participante.email)}` : null;
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

            if (participante.tipo === 'aluno') {
                adicionarCampoOculto(`${raiz}[curso][id]`, participante.curso.id);
                adicionarCampoOculto(`${raiz}[curso][nome]`, participante.curso.nome);

                return;
            }

            adicionarCampoOculto(`${raiz}[email]`, participante.email);
            adicionarCampoOculto(`${raiz}[instituicao][id]`, participante.instituicao.id);
            adicionarCampoOculto(`${raiz}[instituicao][nome]`, participante.instituicao.nome);
        });
    };

    const renderizarTabela = () => {
        lista.replaceChildren();
        tabela.hidden = participantes.length === 0;

        participantes.forEach((participante, indice) => {
            const linha = document.createElement('tr');
            linha.dataset.participanteLinha = indice;
            const tipo = participante.tipo === 'aluno' ? 'Aluno' : 'Professor';
            const cursoOuInstituicao = participante.tipo === 'aluno'
                ? participante.curso.nome
                : participante.instituicao.nome;

            [
                tipo,
                participante.nome,
                cursoOuInstituicao,
                participante.email ?? '—',
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
    };

    const criarParticipante = (dados) => {
        const chave = chaveDoParticipante(dados);

        if (chave !== null && participantes.some((participante) => chaveDoParticipante(participante) === chave)) {
            mensagemGeral.textContent = 'Um participante não pode ser incluído duas vezes.';

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
                        <label for="participante-aluno">Aluno <span aria-hidden="true">*</span></label>
                        <input id="participante-aluno" data-aluno-autocomplete data-participante-nome type="text" list="participante-alunos" maxlength="150" autocomplete="off" placeholder="Digite ou escolha um aluno cadastrado" required>
                        <datalist id="participante-alunos"></datalist>
                    </div>
                    <div class="campo campo--largo">
                        <label for="participante-curso">Curso <span aria-hidden="true">*</span></label>
                        <select id="participante-curso" data-aluno-curso required></select>
                        <input data-aluno-curso-id type="hidden">
                    </div>
                    <div class="campo campo--largo" data-aluno-curso-novo hidden>
                        <label for="participante-curso-novo">Informe o novo curso <span aria-hidden="true">*</span></label>
                        <input id="participante-curso-novo" data-aluno-curso-nome type="text" maxlength="150" disabled>
                    </div>
                </div>
            `;

            const autocomplete = corpo.querySelector('[data-aluno-autocomplete]');
            const listaAlunos = corpo.querySelector('datalist');
            const seletorCurso = corpo.querySelector('[data-aluno-curso]');
            const campoCursoId = corpo.querySelector('[data-aluno-curso-id]');
            const campoCursoNome = corpo.querySelector('[data-aluno-curso-nome]');
            const campoNovoCurso = corpo.querySelector('[data-aluno-curso-novo]');
            const cursosPorId = new Map(cursos.map((curso) => [String(curso.id), curso]));
            const alunosPorRotulo = new Map();
            let alunoId = '';

            alunos.forEach((aluno) => {
                const curso = cursosPorId.get(String(aluno.curso_id));
                const rotulo = `${aluno.nome} — ${curso?.nome ?? 'Curso cadastrado'}`;
                alunosPorRotulo.set(rotulo, aluno);
                listaAlunos.appendChild(criarOpcao(rotulo, rotulo));
            });
            preencherCursos(seletorCurso);

            const liberarAlunoNovo = () => {
                alunoId = '';
                autocomplete.readOnly = false;
                seletorCurso.disabled = false;
                campoCursoId.value = '';
                const novoCurso = seletorCurso.value === NOVO_CURSO;
                campoNovoCurso.hidden = !novoCurso;
                campoCursoNome.disabled = !novoCurso;
                campoCursoNome.required = novoCurso;
            };

            autocomplete.addEventListener('input', () => {
                const aluno = alunosPorRotulo.get(autocomplete.value);

                if (!aluno) {
                    liberarAlunoNovo();

                    return;
                }

                alunoId = String(aluno.id);
                autocomplete.value = aluno.nome;
                autocomplete.readOnly = true;
                seletorCurso.value = aluno.curso_id;
                seletorCurso.disabled = true;
                campoCursoId.value = aluno.curso_id;
                campoCursoNome.value = '';
                campoCursoNome.disabled = true;
                campoCursoNome.required = false;
                campoNovoCurso.hidden = true;
            });

            seletorCurso.addEventListener('change', () => {
                liberarAlunoNovo();
                const novoCurso = seletorCurso.value === NOVO_CURSO;
                campoCursoId.value = novoCurso || !seletorCurso.value ? '' : seletorCurso.value;
                campoNovoCurso.hidden = !novoCurso;
                campoCursoNome.disabled = !novoCurso;
                campoCursoNome.required = novoCurso;

                if (!novoCurso) {
                    campoCursoNome.value = '';
                }
            });

            configurarSalvar(() => ({
                tipo: 'aluno',
                id: alunoId,
                nome: autocomplete.value.trim(),
                curso: {
                    id: campoCursoId.value,
                    nome: campoCursoNome.value.trim()
                        || cursosPorId.get(campoCursoId.value)?.nome
                        || '',
                },
            }));
        };

        const renderizarProfessor = () => {
            corpo.innerHTML = `
                <div class="grupo-campos">
                    <div class="campo">
                        <label for="participante-email">E-mail <span aria-hidden="true">*</span></label>
                        <input id="participante-email" data-participante-email type="email" maxlength="254" required>
                    </div>
                    <div class="campo">
                        <label for="participante-nome">Nome <span aria-hidden="true">*</span></label>
                        <input id="participante-nome" data-participante-nome type="text" maxlength="150" required>
                    </div>
                    <div class="campo campo--largo">
                        <label for="participante-instituicao">Unidade acadêmica <span aria-hidden="true">*</span></label>
                        <select id="participante-instituicao" data-professor-instituicao required></select>
                        <input data-professor-instituicao-id type="hidden">
                    </div>
                    <div class="campo campo--largo" data-professor-instituicao-nova hidden>
                        <label for="participante-instituicao-nova">Informe a unidade acadêmica <span aria-hidden="true">*</span></label>
                        <input id="participante-instituicao-nova" data-professor-instituicao-nome type="text" maxlength="150" disabled>
                    </div>
                </div>
            `;

            const email = corpo.querySelector('[data-participante-email]');
            const nome = corpo.querySelector('[data-participante-nome]');
            const seletorInstituicao = corpo.querySelector('[data-professor-instituicao]');
            const campoInstituicaoId = corpo.querySelector('[data-professor-instituicao-id]');
            const campoInstituicaoNome = corpo.querySelector('[data-professor-instituicao-nome]');
            const campoNovaInstituicao = corpo.querySelector('[data-professor-instituicao-nova]');
            const instituicoesPorId = new Map(instituicoes.map((instituicao) => [String(instituicao.id), instituicao]));
            let professorId = '';
            let consultaAtual = 0;

            preencherInstituicoes(seletorInstituicao);

            const liberarProfessorNovo = () => {
                professorId = '';
                nome.readOnly = false;
                seletorInstituicao.disabled = false;
                const outra = seletorInstituicao.value === OUTRA_INSTITUICAO;
                campoNovaInstituicao.hidden = !outra;
                campoInstituicaoNome.disabled = !outra;
                campoInstituicaoNome.required = outra;
            };

            seletorInstituicao.addEventListener('change', () => {
                const outra = seletorInstituicao.value === OUTRA_INSTITUICAO;
                campoInstituicaoId.value = outra || !seletorInstituicao.value ? '' : seletorInstituicao.value;
                campoNovaInstituicao.hidden = !outra;
                campoInstituicaoNome.disabled = !outra;
                campoInstituicaoNome.required = outra;

                if (!outra) {
                    campoInstituicaoNome.value = '';
                }
            });

            email.addEventListener('blur', async () => {
                const valor = email.value.trim();
                const consulta = ++consultaAtual;
                mensagem.textContent = '';
                liberarProfessorNovo();

                if (!valor || !email.validity.valid) {
                    return;
                }

                email.setCustomValidity('Aguarde a verificação do e-mail cadastrado.');
                mensagem.textContent = 'Verificando e-mail cadastrado…';

                try {
                    const dados = await buscarProfessor(valor);

                    if (consulta !== consultaAtual) {
                        return;
                    }

                    email.setCustomValidity('');

                    if (dados.professor === null) {
                        mensagem.textContent = '';

                        return;
                    }

                    professorId = String(dados.professor.id);
                    nome.value = dados.professor.nome;
                    nome.readOnly = true;
                    seletorInstituicao.value = dados.professor.instituicao.id;
                    seletorInstituicao.disabled = true;
                    campoInstituicaoId.value = dados.professor.instituicao.id;
                    campoInstituicaoNome.value = '';
                    campoInstituicaoNome.disabled = true;
                    campoInstituicaoNome.required = false;
                    campoNovaInstituicao.hidden = true;
                    mensagem.textContent = 'Professor cadastrado encontrado. Os dados foram bloqueados para edição.';
                } catch (erro) {
                    if (consulta !== consultaAtual) {
                        return;
                    }

                    email.setCustomValidity('Não foi possível consultar o professor. Tente novamente.');
                    mensagem.textContent = erro.message;
                }
            });

            email.addEventListener('input', () => {
                consultaAtual += 1;
                email.setCustomValidity('');
                mensagem.textContent = '';

                if (professorId) {
                    professorId = '';
                    nome.value = '';
                    nome.readOnly = false;
                    seletorInstituicao.disabled = false;
                    campoInstituicaoId.value = '';
                    seletorInstituicao.value = '';
                }
            });

            configurarSalvar(() => ({
                tipo: 'professor',
                id: professorId,
                nome: nome.value.trim(),
                email: email.value.trim(),
                instituicao: {
                    id: campoInstituicaoId.value,
                    nome: campoInstituicaoNome.value.trim()
                        || instituicoesPorId.get(campoInstituicaoId.value)?.nome
                        || '',
                },
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
        cursos = detail.cursos;
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

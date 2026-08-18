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
    const lista = formulario?.querySelector('[data-participantes]');
    const botaoAdicionar = formulario?.querySelector('[data-adicionar-participante]');
    const mensagemGeral = formulario?.querySelector('[data-participantes-mensagem]');

    if (!formulario || !lista || !botaoAdicionar || !mensagemGeral) {
        return;
    }

    let proximoIndice = 0;
    let cursos = [];
    let alunos = [];
    let instituicoes = [];

    try {
        const resposta = await fetch('/inscricoes/catalogo/instituicoes', {
            headers: { Accept: 'application/json' },
        });

        if (!resposta.ok) {
            throw new Error();
        }

        instituicoes = (await resposta.json()).instituicoes;
    } catch {
        mensagemGeral.textContent = 'Não foi possível carregar as instituições para professores participantes.';
    }

    const validarDuplicidades = () => {
        const vistos = new Map();
        let possuiDuplicidade = false;

        lista.querySelectorAll('[data-participante]').forEach((cartao) => {
            const tipo = cartao.querySelector('[data-tipo]:checked')?.value;
            const id = cartao.querySelector('[data-participante-id]').value;
            const mensagem = cartao.querySelector('[data-participante-mensagem]');
            const identificador = cartao.querySelector('[data-identificador]');
            const campoCursoId = cartao.querySelector('[data-aluno-curso-id]');
            const campoCursoNome = cartao.querySelector('[data-aluno-curso-nome]');
            const curso = campoCursoId?.value || normalizarChave(campoCursoNome?.value ?? '');
            const nome = normalizarChave(cartao.querySelector('[data-participante-nome]').value);
            const email = normalizarChave(cartao.querySelector('[data-participante-email]')?.value ?? '');
            let chave = null;

            mensagem.textContent = '';
            identificador?.setCustomValidity('');

            if (tipo === 'aluno' && id) {
                chave = `aluno:${id}`;
            }

            if (tipo === 'aluno' && !id && nome && curso) {
                chave = `aluno-novo:${nome}:${curso}`;
            }

            if (tipo === 'professor' && id) {
                chave = `professor:${id}`;
            }

            if (tipo === 'professor' && !id && email) {
                chave = `professor-novo:${email}`;
            }

            if (chave && vistos.has(chave)) {
                const texto = 'Um participante não pode ser incluído duas vezes.';
                mensagem.textContent = texto;
                identificador?.setCustomValidity(texto);
                possuiDuplicidade = true;
            }

            if (chave) {
                vistos.set(chave, cartao);
            }
        });

        mensagemGeral.textContent = possuiDuplicidade
            ? 'Remova ou corrija os participantes repetidos para continuar.'
            : '';
    };

    const preencherCursos = (seletor) => {
        seletor.replaceChildren(
            criarOpcao('', 'Selecione o curso'),
            ...cursos.map((curso) => criarOpcao(curso.id, curso.nome)),
            criarOpcao(NOVO_CURSO, 'Outro curso'),
        );
    };

    const preencherInstituicoes = (seletor) => {
        seletor.replaceChildren(
            criarOpcao('', 'Selecione a instituição'),
            ...instituicoes.map((instituicao) => criarOpcao(instituicao.id, instituicao.nome)),
            criarOpcao(OUTRA_INSTITUICAO, 'Outra instituição'),
        );
    };

    const criarParticipante = () => {
        const indice = proximoIndice++;
        const cartao = document.createElement('section');
        cartao.className = 'participante';
        cartao.dataset.participante = indice;
        cartao.innerHTML = `
            <div class="participante__cabecalho">
                <h3>Participante</h3>
                <button class="botao botao--secundario" type="button" data-remover-participante>Remover</button>
            </div>
            <fieldset class="campo campo--opcoes">
                <legend>Tipo de participante <span aria-hidden="true">*</span></legend>
                <label><input data-tipo name="participantes[${indice}][tipo]" type="radio" value="aluno" checked required> Aluno</label>
                <label><input data-tipo name="participantes[${indice}][tipo]" type="radio" value="professor" required> Professor</label>
            </fieldset>
            <input data-participante-id name="participantes[${indice}][id]" type="hidden">
            <div data-corpo-participante></div>
            <p class="mensagem-campo" data-participante-mensagem aria-live="polite"></p>
        `;

        const corpo = cartao.querySelector('[data-corpo-participante]');
        const renderizarAluno = () => {
            corpo.innerHTML = `
                <div class="grupo-campos">
                    <div class="campo campo--largo">
                        <label for="participante-${indice}-aluno">Aluno <span aria-hidden="true">*</span></label>
                        <input id="participante-${indice}-aluno" data-identificador data-aluno-autocomplete data-participante-nome name="participantes[${indice}][nome]" type="text" list="participante-${indice}-alunos" maxlength="150" autocomplete="off" placeholder="Digite ou escolha um aluno cadastrado" required>
                        <datalist id="participante-${indice}-alunos"></datalist>
                    </div>
                    <div class="campo campo--largo">
                        <label for="participante-${indice}-curso">Curso <span aria-hidden="true">*</span></label>
                        <select id="participante-${indice}-curso" data-aluno-curso required></select>
                        <input data-aluno-curso-id name="participantes[${indice}][curso][id]" type="hidden">
                        <input data-aluno-curso-nome name="participantes[${indice}][curso][nome]" type="text" maxlength="150" placeholder="Informe o novo curso" disabled>
                    </div>
                </div>
            `;

            const autocomplete = corpo.querySelector('[data-aluno-autocomplete]');
            const listaAlunos = corpo.querySelector('datalist');
            const nome = corpo.querySelector('[data-participante-nome]');
            const seletorCurso = corpo.querySelector('[data-aluno-curso]');
            const campoCursoId = corpo.querySelector('[data-aluno-curso-id]');
            const campoCursoNome = corpo.querySelector('[data-aluno-curso-nome]');
            const campoId = cartao.querySelector('[data-participante-id]');
            const cursosPorId = new Map(cursos.map((curso) => [String(curso.id), curso]));
            const alunosPorRotulo = new Map();

            listaAlunos.replaceChildren();
            alunos.forEach((aluno) => {
                const curso = cursosPorId.get(String(aluno.curso_id));
                const rotulo = `${aluno.nome} — ${curso?.nome ?? 'Curso cadastrado'}`;
                alunosPorRotulo.set(rotulo, aluno);
                listaAlunos.appendChild(criarOpcao(rotulo, rotulo));
            });
            preencherCursos(seletorCurso);

            const liberarAlunoNovo = () => {
                campoId.value = '';
                nome.readOnly = false;
                nome.required = true;
                seletorCurso.disabled = false;
                campoCursoId.value = '';
                campoCursoNome.disabled = seletorCurso.value !== NOVO_CURSO;
                campoCursoNome.required = seletorCurso.value === NOVO_CURSO;
            };

            autocomplete.addEventListener('input', () => {
                const aluno = alunosPorRotulo.get(autocomplete.value);

                if (!aluno) {
                    nome.value = autocomplete.value;
                    liberarAlunoNovo();
                    validarDuplicidades();

                    return;
                }

                campoId.value = aluno.id;
                nome.value = aluno.nome;
                nome.readOnly = true;
                seletorCurso.value = aluno.curso_id;
                seletorCurso.disabled = true;
                campoCursoId.value = aluno.curso_id;
                campoCursoNome.value = '';
                campoCursoNome.disabled = true;
                campoCursoNome.required = false;
                validarDuplicidades();
            });

            seletorCurso.addEventListener('change', () => {
                liberarAlunoNovo();
                const novoCurso = seletorCurso.value === NOVO_CURSO;
                campoCursoId.value = novoCurso || !seletorCurso.value ? '' : seletorCurso.value;
                campoCursoNome.disabled = !novoCurso;
                campoCursoNome.required = novoCurso;

                if (!novoCurso) {
                    campoCursoNome.value = '';
                }

                validarDuplicidades();
            });

            [nome, campoCursoNome].forEach((campo) => campo.addEventListener('input', validarDuplicidades));
        };

        const renderizarProfessor = () => {
            corpo.innerHTML = `
                <div class="grupo-campos">
                    <div class="campo">
                        <label for="participante-${indice}-email">E-mail <span aria-hidden="true">*</span></label>
                        <input id="participante-${indice}-email" data-identificador data-participante-email name="participantes[${indice}][email]" type="email" maxlength="254" required>
                    </div>
                    <div class="campo">
                        <label for="participante-${indice}-nome">Nome <span aria-hidden="true">*</span></label>
                        <input id="participante-${indice}-nome" data-participante-nome name="participantes[${indice}][nome]" type="text" maxlength="150" required>
                    </div>
                    <div class="campo campo--largo">
                        <label for="participante-${indice}-instituicao">Instituição <span aria-hidden="true">*</span></label>
                        <select id="participante-${indice}-instituicao" data-professor-instituicao required></select>
                        <input data-professor-instituicao-id name="participantes[${indice}][instituicao][id]" type="hidden">
                        <input data-professor-instituicao-nome name="participantes[${indice}][instituicao][nome]" type="text" maxlength="150" placeholder="Informe a instituição" disabled>
                    </div>
                </div>
            `;

            const email = corpo.querySelector('[data-participante-email]');
            const nome = corpo.querySelector('[data-participante-nome]');
            const seletorInstituicao = corpo.querySelector('[data-professor-instituicao]');
            const campoInstituicaoId = corpo.querySelector('[data-professor-instituicao-id]');
            const campoInstituicaoNome = corpo.querySelector('[data-professor-instituicao-nome]');
            const campoId = cartao.querySelector('[data-participante-id]');
            const mensagem = cartao.querySelector('[data-participante-mensagem]');

            preencherInstituicoes(seletorInstituicao);

            const liberarProfessorNovo = () => {
                campoId.value = '';
                nome.readOnly = false;
                seletorInstituicao.disabled = false;
            };

            seletorInstituicao.addEventListener('change', () => {
                const outra = seletorInstituicao.value === OUTRA_INSTITUICAO;
                campoInstituicaoId.value = outra || !seletorInstituicao.value ? '' : seletorInstituicao.value;
                campoInstituicaoNome.disabled = !outra;
                campoInstituicaoNome.required = outra;

                if (!outra) {
                    campoInstituicaoNome.value = '';
                }

                validarDuplicidades();
            });

            email.addEventListener('blur', async () => {
                const valor = email.value.trim();
                mensagem.textContent = '';
                liberarProfessorNovo();

                if (!valor || !email.validity.valid) {
                    validarDuplicidades();

                    return;
                }

                try {
                    const dados = await buscarProfessor(valor);

                    if (dados.professor === null) {
                        validarDuplicidades();

                        return;
                    }

                    campoId.value = dados.professor.id;
                    nome.value = dados.professor.nome;
                    nome.readOnly = true;
                    seletorInstituicao.value = dados.professor.instituicao.id;
                    seletorInstituicao.disabled = true;
                    campoInstituicaoId.value = dados.professor.instituicao.id;
                    campoInstituicaoNome.value = '';
                    campoInstituicaoNome.disabled = true;
                    campoInstituicaoNome.required = false;
                    mensagem.textContent = 'Professor cadastrado encontrado. Os dados foram bloqueados para edição.';
                } catch (erro) {
                    mensagem.textContent = erro.message;
                }

                validarDuplicidades();
            });

            email.addEventListener('input', () => {
                if (campoId.value) {
                    campoId.value = '';
                    nome.value = '';
                    nome.readOnly = false;
                    seletorInstituicao.disabled = false;
                    campoInstituicaoId.value = '';
                    seletorInstituicao.value = '';
                }

                validarDuplicidades();
            });
            [nome, campoInstituicaoNome].forEach((campo) => campo.addEventListener('input', validarDuplicidades));
        };

        const renderizar = () => {
            cartao.querySelector('[data-participante-id]').value = '';

            if (cartao.querySelector('[data-tipo]:checked').value === 'aluno') {
                renderizarAluno();
            } else {
                renderizarProfessor();
            }

            validarDuplicidades();
        };

        cartao.querySelectorAll('[data-tipo]').forEach((radio) => radio.addEventListener('change', renderizar));
        cartao.querySelector('[data-remover-participante]').addEventListener('click', () => {
            cartao.remove();
            validarDuplicidades();
        });
        renderizar();

        return cartao;
    };

    const adicionarParticipante = () => {
        lista.appendChild(criarParticipante());
    };

    formulario.addEventListener('inscricao:instituicao-alterada', ({ detail }) => {
        cursos = detail.cursos;
        alunos = detail.alunos;
        lista.replaceChildren();
        adicionarParticipante();
    });

    botaoAdicionar.addEventListener('click', adicionarParticipante);
    if (!lista.children.length) {
        adicionarParticipante();
    }
};

document.addEventListener('DOMContentLoaded', iniciarParticipantes);

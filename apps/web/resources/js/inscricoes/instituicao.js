const OUTRA_INSTITUICAO = '__nova__';
const NOVO_CURSO = '__novo__';

const criarOpcao = (valor, texto) => {
    const opcao = document.createElement('option');
    opcao.value = valor;
    opcao.textContent = texto;

    return opcao;
};

const buscarJson = async (url) => {
    const resposta = await fetch(url, {
        headers: { Accept: 'application/json' },
    });

    if (!resposta.ok) {
        throw new Error('Não foi possível carregar os dados cadastrados.');
    }

    return resposta.json();
};

const emitirAlteracao = (formulario, detalhe) => {
    formulario.dispatchEvent(new CustomEvent('inscricao:instituicao-alterada', {
        detail: detalhe,
    }));
};

const iniciarInstituicao = async () => {
    const formulario = document.querySelector('#formulario-inscricao');
    const seletor = formulario?.querySelector('[data-instituicao]');

    if (!formulario || !seletor) {
        return;
    }

    const campoId = formulario.querySelector('[data-instituicao-id]');
    const camposNovos = formulario.querySelector('[data-instituicao-nova]');
    const camposInstituicao = [
        formulario.querySelector('#instituicao-nome'),
        formulario.querySelector('#instituicao-instagram'),
        formulario.querySelector('#instituicao-facebook'),
        formulario.querySelector('#instituicao-site'),
        formulario.querySelector('#instituicao-outros-links'),
    ];
    const seletorCurso = formulario.querySelector('[data-curso-principal]');
    const campoCursoId = formulario.querySelector('[data-curso-principal-id]');
    const campoNovoCurso = formulario.querySelector('#curso-principal-nome');
    const camposNovoCurso = formulario.querySelector('[data-curso-principal-novo]');

    const limparCursos = () => {
        seletorCurso.replaceChildren(criarOpcao('', 'Selecione primeiro a instituição'));
        seletorCurso.disabled = true;
        campoCursoId.value = '';
        campoNovoCurso.value = '';
        campoNovoCurso.disabled = true;
        camposNovoCurso.hidden = true;
    };

    const configurarCurso = (cursos) => {
        seletorCurso.replaceChildren(
            criarOpcao('', 'Selecione o curso'),
            ...cursos.map((curso) => criarOpcao(curso.id, curso.nome)),
            criarOpcao(NOVO_CURSO, 'Outro curso'),
        );
        seletorCurso.disabled = false;
        campoCursoId.value = '';
        campoNovoCurso.value = '';
        campoNovoCurso.disabled = true;
        camposNovoCurso.hidden = true;
    };

    const configurarInstituicaoNova = (nova = true) => {
        campoId.value = '';
        camposNovos.hidden = false;
        camposInstituicao.forEach((campo) => {
            campo.disabled = false;
            campo.value = '';
        });
        camposInstituicao[0].required = true;
        limparCursos();
        emitirAlteracao(formulario, { instituicao: null, cursos: [], alunos: [], nova });
    };

    const configurarInstituicaoExistente = async (id) => {
        const dados = await buscarJson(`/inscricoes/catalogo/instituicoes/${id}`);
        const instituicao = dados.instituicao;

        campoId.value = instituicao.id;
        camposNovos.hidden = true;
        camposInstituicao.forEach((campo) => {
            const chave = campo.name.match(/\[(.+)]/)?.[1];
            campo.value = instituicao[chave] ?? '';
            campo.disabled = true;
        });
        camposInstituicao[0].required = false;
        configurarCurso(dados.cursos);
        emitirAlteracao(formulario, {
            instituicao,
            cursos: dados.cursos,
            alunos: dados.alunos,
            nova: false,
        });
    };

    seletorCurso.addEventListener('change', () => {
        const novoCurso = seletorCurso.value === NOVO_CURSO;
        campoCursoId.value = novoCurso || !seletorCurso.value ? '' : seletorCurso.value;
        camposNovoCurso.hidden = !novoCurso;
        campoNovoCurso.disabled = !novoCurso;
        campoNovoCurso.required = novoCurso;

        if (!novoCurso) {
            campoNovoCurso.value = '';
        }
    });

    seletor.addEventListener('change', async () => {
        if (seletor.value === OUTRA_INSTITUICAO) {
            configurarInstituicaoNova();

            return;
        }

        if (!seletor.value) {
            configurarInstituicaoNova(false);
            camposNovos.hidden = true;
            camposInstituicao[0].required = false;

            return;
        }

        seletor.disabled = true;

        try {
            await configurarInstituicaoExistente(seletor.value);
        } catch (erro) {
            emitirAlteracao(formulario, { instituicao: null, cursos: [], alunos: [], nova: false });
            window.alert(erro.message);
        } finally {
            seletor.disabled = false;
        }
    });

    try {
        const dados = await buscarJson('/inscricoes/catalogo/instituicoes');
        seletor.replaceChildren(
            criarOpcao('', 'Selecione a instituição'),
            ...dados.instituicoes.map((instituicao) => criarOpcao(instituicao.id, instituicao.nome)),
            criarOpcao(OUTRA_INSTITUICAO, 'Outra instituição'),
        );
        configurarInstituicaoNova(false);
        camposNovos.hidden = true;
        camposInstituicao[0].required = false;
    } catch (erro) {
        seletor.replaceChildren(criarOpcao('', 'Não foi possível carregar as instituições'));
        seletor.disabled = true;
        window.alert(erro.message);
    }
};

document.addEventListener('DOMContentLoaded', iniciarInstituicao);

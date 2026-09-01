const criarOpcao = (valor, texto) => {
    const opcao = document.createElement('option');
    opcao.value = valor;
    opcao.textContent = texto;

    return opcao;
};

const buscarJson = async (url) => {
    const resposta = await fetch(url, { headers: { Accept: 'application/json' } });

    if (!resposta.ok) {
        throw new Error('Não foi possível carregar os dados cadastrados.');
    }

    return resposta.json();
};

const iniciarInstituicao = async () => {
    const formulario = document.querySelector('#formulario-inscricao');
    const seletor = formulario?.querySelector('[data-instituicao]');
    const campoId = formulario?.querySelector('[data-instituicao-id]');
    const seletorCurso = formulario?.querySelector('[data-curso-principal]');
    const campoCursoId = formulario?.querySelector('[data-curso-principal-id]');

    if (!formulario || !seletor || !campoId || !seletorCurso || !campoCursoId) {
        return;
    }

    const limparCursos = () => {
        seletorCurso.replaceChildren(criarOpcao('', 'Selecione primeiro a unidade acadêmica'));
        seletorCurso.disabled = true;
        campoCursoId.value = '';
    };

    const emitirAlteracao = (detail) => formulario.dispatchEvent(new CustomEvent('inscricao:instituicao-alterada', { detail }));

    seletorCurso.addEventListener('change', () => {
        campoCursoId.value = seletorCurso.value;
    });

    seletor.addEventListener('change', async () => {
        campoId.value = seletor.value;
        limparCursos();
        emitirAlteracao({ instituicao: null, cursos: [], alunos: [] });

        if (!seletor.value) {
            return;
        }

        seletor.disabled = true;

        try {
            const dados = await buscarJson(`/inscricoes/catalogo/instituicoes/${seletor.value}`);
            seletorCurso.replaceChildren(
                criarOpcao('', 'Selecione o curso'),
                ...dados.cursos.map((curso) => criarOpcao(curso.id, curso.nome)),
            );
            seletorCurso.disabled = false;
            emitirAlteracao({ instituicao: dados.instituicao, cursos: dados.cursos, alunos: dados.alunos });
        } catch (erro) {
            window.alert(erro.message);
        } finally {
            seletor.disabled = false;
        }
    });

    try {
        const dados = await buscarJson('/inscricoes/catalogo/instituicoes');
        seletor.replaceChildren(
            criarOpcao('', 'Selecione a unidade acadêmica'),
            ...dados.instituicoes.map((instituicao) => criarOpcao(instituicao.id, instituicao.nome)),
        );
        limparCursos();
    } catch (erro) {
        seletor.replaceChildren(criarOpcao('', 'Não foi possível carregar as unidades acadêmicas'));
        seletor.disabled = true;
        window.alert(erro.message);
    }
};

document.addEventListener('DOMContentLoaded', iniciarInstituicao);

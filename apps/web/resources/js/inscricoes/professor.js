const buscarProfessor = async (email) => {
    const resposta = await fetch(`/inscricoes/professores/busca?email=${encodeURIComponent(email)}`, {
        headers: { Accept: 'application/json' },
    });

    if (!resposta.ok) {
        throw new Error('Não foi possível consultar o professor.');
    }

    return resposta.json();
};

const normalizarNome = (valor) => valor
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .trim()
    .replace(/\s+/g, ' ')
    .toLocaleLowerCase('pt-BR');

const iniciarProfessorResponsavel = () => {
    const formulario = document.querySelector('#formulario-inscricao');
    const email = formulario?.querySelector('[data-professor-responsavel-email]');
    const nome = formulario?.querySelector('[data-professor-responsavel-nome]');
    const mensagem = formulario?.querySelector('[data-professor-responsavel-mensagem]');

    if (!formulario || !email || !nome || !mensagem) {
        return;
    }

    let instituicaoSelecionada = null;
    let instituicaoNova = false;
    let professorEncontrado = null;

    const atualizarProfessorEncontrado = () => {
        email.setCustomValidity('');
        mensagem.textContent = '';

        if (professorEncontrado === null) {
            return;
        }

        if (
            instituicaoNova
            || (instituicaoSelecionada !== null && professorEncontrado.instituicao.id !== instituicaoSelecionada.id)
        ) {
            nome.readOnly = false;
            email.setCustomValidity(`E-mail já cadastrado para um professor da instituição ${professorEncontrado.instituicao.nome}.`);
            mensagem.textContent = email.validationMessage;

            return;
        }

        if (nome.value && normalizarNome(nome.value) !== normalizarNome(professorEncontrado.nome)) {
            nome.readOnly = false;
            email.setCustomValidity('E-mail já utilizado por outro professor.');
            mensagem.textContent = email.validationMessage;

            return;
        }

        nome.value = professorEncontrado.nome;
        nome.readOnly = true;
        mensagem.textContent = 'Professor cadastrado encontrado. O nome foi bloqueado para edição.';
    };

    formulario.addEventListener('inscricao:instituicao-alterada', ({ detail }) => {
        instituicaoSelecionada = detail.instituicao;
        instituicaoNova = detail.nova;
        atualizarProfessorEncontrado();
    });

    email.addEventListener('blur', async () => {
        const valor = email.value.trim();
        nome.readOnly = false;
        email.setCustomValidity('');
        mensagem.textContent = '';

        if (!valor || !email.validity.valid) {
            return;
        }

        try {
            const dados = await buscarProfessor(valor);

            if (dados.professor === null) {
                return;
            }

            professorEncontrado = dados.professor;
            atualizarProfessorEncontrado();
        } catch (erro) {
            mensagem.textContent = erro.message;
        }
    });

    email.addEventListener('input', () => {
        professorEncontrado = null;

        if (nome.readOnly) {
            nome.value = '';
            nome.readOnly = false;
        }

        email.setCustomValidity('');
        mensagem.textContent = '';
    });

    nome.addEventListener('input', atualizarProfessorEncontrado);
};

document.addEventListener('DOMContentLoaded', iniciarProfessorResponsavel);

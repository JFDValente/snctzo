const buscarProfessor = async (email) => {
    const resposta = await fetch(`/inscricoes/professores/busca?email=${encodeURIComponent(email)}`, {
        headers: { Accept: 'application/json' },
    });

    if (!resposta.ok) {
        throw new Error('Não foi possível consultar o professor.');
    }

    return resposta.json();
};

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

    formulario.addEventListener('inscricao:instituicao-alterada', ({ detail }) => {
        instituicaoSelecionada = detail.instituicao;
        instituicaoNova = detail.nova;
        email.setCustomValidity('');
        mensagem.textContent = '';

        if (nome.readOnly) {
            nome.value = '';
            nome.readOnly = false;
        }
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

            if (instituicaoSelecionada === null && !instituicaoNova) {
                email.setCustomValidity('Selecione a instituição antes de informar o professor responsável.');
                mensagem.textContent = email.validationMessage;

                return;
            }

            if (instituicaoNova || dados.professor.instituicao.id !== instituicaoSelecionada.id) {
                email.setCustomValidity('E-mail já utilizado por outro professor.');
                mensagem.textContent = 'E-mail já utilizado por outro professor.';

                return;
            }

            nome.value = dados.professor.nome;
            nome.readOnly = true;
            mensagem.textContent = 'Professor cadastrado encontrado. O nome foi bloqueado para edição.';
        } catch (erro) {
            mensagem.textContent = erro.message;
        }
    });
};

document.addEventListener('DOMContentLoaded', iniciarProfessorResponsavel);

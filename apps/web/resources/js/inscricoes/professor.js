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
    let consultaAtual = 0;
    let consultaPendente = false;

    const atualizarProfessorEncontrado = () => {
        if (consultaPendente) {
            return;
        }

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
        const consulta = ++consultaAtual;
        nome.readOnly = false;
        email.setCustomValidity('');
        mensagem.textContent = '';

        if (!valor || !email.validity.valid) {
            return;
        }

        consultaPendente = true;
        email.setCustomValidity('Aguarde a verificação do e-mail cadastrado.');
        mensagem.textContent = 'Verificando e-mail cadastrado…';

        try {
            const dados = await buscarProfessor(valor);

            if (consulta !== consultaAtual) {
                return;
            }

            consultaPendente = false;

            if (dados.professor === null) {
                professorEncontrado = null;
                atualizarProfessorEncontrado();

                return;
            }

            professorEncontrado = dados.professor;
            atualizarProfessorEncontrado();
        } catch (erro) {
            if (consulta !== consultaAtual) {
                return;
            }

            consultaPendente = false;
            email.setCustomValidity('Não foi possível consultar o professor. Tente novamente.');
            mensagem.textContent = erro.message;
        }
    });

    email.addEventListener('input', () => {
        consultaAtual += 1;
        consultaPendente = false;
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

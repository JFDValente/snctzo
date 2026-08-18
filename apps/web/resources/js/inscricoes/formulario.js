const mensagemDeErro = (campo) => {
    if (campo.validity.customError) {
        return campo.validationMessage;
    }

    if (campo.validity.valueMissing) {
        if (campo.type === 'checkbox' || campo.type === 'radio') {
            return 'Confirme esta declaração para continuar.';
        }

        return 'Este campo é obrigatório.';
    }

    if (campo.validity.typeMismatch && campo.type === 'email') {
        return 'Informe um e-mail válido.';
    }

    return 'Revise o valor informado neste campo.';
};

const iniciarFormulario = () => {
    const formulario = document.querySelector('#formulario-inscricao');

    if (!formulario) {
        return;
    }

    const etapas = [...formulario.querySelectorAll('[data-etapa]')];
    const progresso = [...document.querySelectorAll('[data-progresso]')];
    const botaoAnterior = formulario.querySelector('[data-anterior]');
    const botaoProxima = formulario.querySelector('[data-proxima]');
    const botaoEnviar = formulario.querySelector('[data-enviar]');
    let etapaAtual = 1;

    const limparErros = (etapa) => {
        etapa.querySelectorAll('.erro-campo').forEach((erro) => erro.remove());
        etapa.querySelectorAll('[aria-invalid="true"]').forEach((campo) => {
            campo.removeAttribute('aria-invalid');
        });
    };

    const adicionarErro = (campo, mensagem) => {
        campo.setAttribute('aria-invalid', 'true');
        const destino = campo.closest('.aceite, .campo') ?? campo.parentElement;
        const erro = document.createElement('p');
        erro.className = 'erro-campo';
        erro.textContent = mensagem;
        destino.appendChild(erro);
    };

    const validarDias = (etapa) => {
        const dias = [...etapa.querySelectorAll('[name^="atividade[participa_dia_"]')];

        if (!dias.length || dias.some((dia) => dia.checked)) {
            return true;
        }

        adicionarErro(dias[0], 'Selecione pelo menos um dia de participação.');

        return false;
    };

    const validarParticipantes = (etapa) => {
        const participantes = etapa.querySelectorAll('[data-participante]');

        if (participantes.length) {
            return true;
        }

        const mensagem = etapa.querySelector('[data-participantes-mensagem]');
        mensagem.textContent = 'Inclua pelo menos um participante para continuar.';

        return false;
    };

    const validarEtapa = (numero) => {
        const etapa = etapas.find((item) => Number(item.dataset.etapa) === numero);
        limparErros(etapa);
        let valido = true;

        etapa.querySelectorAll('input, select, textarea').forEach((campo) => {
            if (campo.disabled || campo.type === 'hidden' || campo.checkValidity()) {
                return;
            }

            adicionarErro(campo, mensagemDeErro(campo));
            valido = false;
        });

        if (numero === 3) {
            valido = validarDias(etapa) && validarParticipantes(etapa) && valido;
        }

        if (!valido) {
            etapa.querySelector('[aria-invalid="true"]')?.focus();
        }

        return valido;
    };

    const atualizarEtapas = () => {
        etapas.forEach((etapa) => {
            etapa.hidden = Number(etapa.dataset.etapa) !== etapaAtual;
        });
        progresso.forEach((item) => {
            const numero = Number(item.dataset.progresso);
            item.toggleAttribute('aria-current', numero === etapaAtual);
            item.classList.toggle('progresso__etapa--concluida', numero < etapaAtual);
        });
        botaoAnterior.hidden = etapaAtual === 1;
        botaoProxima.hidden = etapaAtual === etapas.length;
        botaoEnviar.hidden = etapaAtual !== etapas.length;
    };

    const focarEtapaAtual = () => {
        const legenda = etapas[etapaAtual - 1].querySelector('legend');
        legenda?.setAttribute('tabindex', '-1');
        legenda?.focus();
    };

    botaoProxima.addEventListener('click', () => {
        if (!validarEtapa(etapaAtual)) {
            return;
        }

        etapaAtual += 1;
        atualizarEtapas();
        focarEtapaAtual();
    });

    botaoAnterior.addEventListener('click', () => {
        etapaAtual -= 1;
        atualizarEtapas();
        focarEtapaAtual();
    });

    formulario.addEventListener('submit', (evento) => {
        if (etapaAtual !== etapas.length) {
            evento.preventDefault();

            return;
        }

        if (!validarEtapa(etapaAtual)) {
            evento.preventDefault();
        }
    });

    atualizarEtapas();
};

document.addEventListener('DOMContentLoaded', iniciarFormulario);

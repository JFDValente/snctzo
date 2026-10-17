import { inicializarSituacoesDePresenca } from './situacao';

document.querySelectorAll('[data-dialogo-participante-tardio]').forEach((dialogo) => {
    const abrir = document.querySelector('[data-abrir-participante-tardio]');
    const fechar = dialogo.querySelector('[data-fechar-dialogo]');
    const formulario = dialogo.querySelector('[data-formulario-participante-tardio]');
    const mensagemDeErro = formulario.querySelector('[data-erro-participante]');
    const lista = document.querySelector('.admin-presencas-lista');

    abrir?.addEventListener('click', () => {
        mensagemDeErro.hidden = true;
        formulario.reset();
        dialogo.showModal();
    });

    fechar?.addEventListener('click', () => dialogo.close());

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        mensagemDeErro.hidden = true;

        const resposta = await fetch(formulario.action, {
            body: new FormData(formulario),
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            method: 'POST',
        });

        if (!resposta.ok) {
            const dados = await resposta.json().catch(() => ({}));
            mensagemDeErro.textContent = Object.values(dados.errors ?? {})[0]?.[0] ?? 'Não foi possível incluir o participante. Tente novamente.';
            mensagemDeErro.hidden = false;

            return;
        }

        const dados = await resposta.json();
        lista.insertAdjacentHTML('beforeend', dados.html);
        inicializarSituacoesDePresenca(lista.lastElementChild);
        [...lista.children]
            .sort((primeiro, segundo) => primeiro.dataset.nomeParticipante.localeCompare(
                segundo.dataset.nomeParticipante,
                'pt-BR',
                { sensitivity: 'base' },
            ))
            .forEach((participante) => lista.append(participante));
        formulario.reset();
        dialogo.close();
    });
});

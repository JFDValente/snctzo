document.querySelectorAll('[data-formulario-presenca]').forEach((formulario) => {
    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const resposta = await fetch(formulario.dataset.situacaoUrl, {
            headers: { Accept: 'application/json' },
        });

        if (!resposta.ok) {
            formulario.submit();

            return;
        }

        const situacao = await resposta.json();
        const versaoAberta = formulario.dataset.versao;

        if (!situacao.versao || situacao.versao === versaoAberta) {
            formulario.submit();

            return;
        }

        const mensagem = `Este turno foi confirmado por ${situacao.confirmado_por}, em ${situacao.confirmado_em}, depois que você abriu a tela. Sua confirmação substituirá o registro atual. Deseja continuar?`;

        if (window.confirm(mensagem)) {
            formulario.submit();
        }
    });
});

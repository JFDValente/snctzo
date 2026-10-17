document.querySelectorAll('[data-dialogo-exclusao]').forEach((dialogo) => {
    const abrir = document.querySelector('[data-abrir-dialogo-exclusao]');

    abrir?.addEventListener('click', () => dialogo.showModal());

    dialogo.querySelectorAll('[data-cancelar-exclusao]').forEach((botao) => {
        botao.addEventListener('click', () => dialogo.close());
    });

    dialogo.addEventListener('click', (evento) => {
        if (evento.target === dialogo) {
            dialogo.close();
        }
    });
});

document.querySelectorAll('[data-dialogo-retorno]').forEach((dialogo) => {
    dialogo.showModal();

    dialogo.querySelectorAll('[data-fechar-dialogo]').forEach((botao) => {
        botao.addEventListener('click', () => dialogo.close());
    });
});

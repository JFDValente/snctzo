document.querySelectorAll('[data-situacao-presenca]').forEach((situacao) => {
    situacao.querySelectorAll('input[type="radio"]').forEach((opcao) => {
        opcao.addEventListener('pointerdown', () => {
            opcao.dataset.estavaSelecionada = opcao.checked ? 'sim' : 'nao';
        });

        opcao.addEventListener('click', () => {
            if (opcao.dataset.estavaSelecionada === 'sim') {
                opcao.checked = false;
            }

            delete opcao.dataset.estavaSelecionada;
        });

        opcao.addEventListener('keydown', (evento) => {
            if ((evento.key === ' ' || evento.key === 'Enter') && opcao.checked) {
                evento.preventDefault();
                opcao.checked = false;
            }
        });
    });
});

const normalizar = (valor) => valor
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLocaleLowerCase('pt-BR')
    .trim();

document.querySelectorAll('[data-lista-atividades]').forEach((lista) => {
    const campo = lista.querySelector('[data-busca-atividades]');
    const mensagemVazia = lista.querySelector('[data-busca-atividades-vazio]');
    const atividades = [...lista.querySelectorAll('[data-atividade]')];

    campo.addEventListener('input', () => {
        const termo = normalizar(campo.value);
        let encontradas = 0;

        atividades.forEach((atividade) => {
            const professorResponsavel = normalizar(atividade.dataset.professorResponsavel ?? '');
            const corresponde = professorResponsavel.includes(termo);

            atividade.hidden = !corresponde;
            encontradas += Number(corresponde);
        });

        mensagemVazia.hidden = encontradas > 0;
    });
});

# Validação manual do MVP

Este documento registra as verificações manuais feitas localmente. O Gate 5 só
deve ser considerado concluído quando todos os cenários pendentes estiverem
marcados como aprovados.

## Rodada no navegador — 17/08/2026

Ambiente: Laravel local, Chromium headless, sem dados permanentes de teste.

| Cenário | Resultado |
|---|---|
| Página pública em 390 × 844, 768 × 1024 e 1440 × 900 | Aprovado: sem estouro horizontal ou erros de console. |
| Navegação por teclado entre as etapas 1 e 2 | Aprovado: Enter aciona Avançar e Anterior. |
| Retorno de etapa com dados preenchidos | Aprovado: dados da instituição permanecem no formulário. |
| Erros na etapa 2 | Aprovado: avanço bloqueado e erros exibidos junto aos campos. |
| Instituição, curso, responsável, aluno e curso de participante novos | Aprovado: inscrição completa enviada e página de sucesso exibida. |
| Atividade em apenas um dia | Aprovado após correção da validação do backend. |
| CSRF inválido | Aprovado: POST recusado com 419. |
| Honeypot preenchido | Aprovado: inscrição recusada pelo backend. |
| Limite da busca de professor | Aprovado: a 31ª tentativa recebe 429 em PT-BR. |

Os registros temporários criados na inscrição completa foram removidos após a
validação.

## Cenários pendentes do Gate 5

- Instituição e curso existentes.
- Professor responsável existente e professor de instituição incompatível.
- Alunos existentes e professor participante interno ou externo.
- Duplicidades de aluno e professor na mesma atividade.
- Nenhum participante, nenhum dia e aceite incompleto.
- Resumo e observações no limite de 5.000 caracteres.
- Janela antes da abertura e depois do encerramento.
- Clique duplo e reenvio do mesmo token.
- Falha transacional durante uma submissão pela interface.

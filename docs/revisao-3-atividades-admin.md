# Revisão 3 — Módulo de atividades do admin

## 1. Objetivo

Permitir que um administrador retire uma atividade da operação da SNCTZO 2026 sem apagar fisicamente os seus dados. A ação será uma exclusão virtual: a atividade deixará de ser visível em toda a aplicação, mantendo-se os registros para eventual necessidade técnica futura.

Esta revisão amplia o módulo de atividades autenticado e não altera o formulário público nem os cadastros de pessoas.

## 2. Requisitos funcionais

| Código | Requisito |
|---|---|
| RF-R3-01 | Um usuário com acesso ao módulo de atividades poderá excluir virtualmente uma atividade pela sua tela de detalhes. |
| RF-R3-02 | Antes da confirmação, o sistema exibirá uma confirmação explícita, informando que a atividade deixará de aparecer na aplicação. |
| RF-R3-03 | Ao confirmar, o sistema registrará a exclusão virtual e retornará à listagem com mensagem de sucesso. |
| RF-R3-04 | Uma atividade excluída não aparecerá na listagem, na visualização, no controle de presença, no CSV de atividades, no relatório CSV de presença nem em consultas públicas ou administrativas usuais. |
| RF-R3-05 | Alunos, professores e seus cadastros individuais não serão excluídos ou modificados pela ação. |

## 3. Decisões técnicas e de segurança

| Tema | Decisão |
|---|---|
| Estratégia | Usar o mecanismo `SoftDeletes` do Laravel no model `Atividade`. |
| Persistência | Incluir `deleted_at` anulável e indexado em `atividades`; excluir significa preencher esse campo. |
| Visibilidade | O escopo global de `SoftDeletes` ocultará automaticamente as atividades excluídas de consultas Eloquent comuns. |
| Acesso | A rota será protegida por autenticação e pela permissão já aplicada ao módulo `atividades`; operadores de presença não terão acesso. |
| Rota | `DELETE /admin/atividades/{atividade}`, com nome `admin.atividades.destroy`. |
| Interface | A ação ficará na tela de detalhe, em área visualmente destrutiva, e exigirá confirmação antes do envio. |
| Reversão | Não haverá tela, rota ou fluxo de restauração nesta revisão. Uma restauração futura deverá ser uma funcionalidade administrativa explícita. |
| Exclusão física | Não haverá `forceDelete`, cascata nem limpeza de registros associados. |

## 4. Efeito sobre dados relacionados

A exclusão virtual afetará somente a linha de `atividades`.

- As relações `atividade_aluno` e `atividade_professor` permanecem no banco.
- Os registros de `alunos` e `professores` permanecem íntegros e continuam disponíveis para outras atividades, quando aplicável.
- Registros de períodos e presenças já persistidos também permanecem fisicamente no banco, mas ficam inacessíveis pelos fluxos normais porque a atividade-pai estará ocultada.
- Consultas futuras que usarem `withTrashed()` ou `onlyTrashed()` deverão ser deliberadas e não poderão alimentar telas, exportações ou relatórios operacionais sem uma regra específica aprovada.

## 5. Comportamento esperado

1. O administrador abre `/admin/atividades/{id}`.
2. Seleciona **Excluir atividade**.
3. Confirma a ação no diálogo de segurança.
4. A aplicação define `atividades.deleted_at` com a data e hora atual.
5. A atividade deixa de constar da listagem, detalhes, presenças e exportações.
6. A aplicação redireciona para `/admin/atividades` e exibe uma confirmação de exclusão.

Após a exclusão, uma tentativa de acessar diretamente `/admin/atividades/{id}` deve resultar em `404`, como ocorre para qualquer atividade não visível.

## 6. Critérios de aceite

- A exclusão não remove a linha física de `atividades`; apenas preenche `deleted_at`.
- A listagem administrativa e ambos os CSVs não contêm a atividade excluída.
- A atividade excluída não pode ser aberta nem selecionada no módulo de presença.
- Alunos e professores ligados à atividade continuam existentes e não têm seus dados alterados.
- A exclusão não pode ser disparada por um visitante nem por um usuário com perfil exclusivo de presença.
- A interface solicita confirmação antes de executar a operação.

## 7. Fora do escopo

- Restaurar atividades excluídas;
- listar itens excluídos;
- exclusão física e limpeza de dados relacionados;
- auditoria com identidade e motivo da exclusão;
- exclusão em lote.

## 8. Plano técnico

O detalhamento das fases está em [Plano — Revisão 3 do módulo de atividades](./planos-de-implementacao/revisao-3-atividades-admin.md).

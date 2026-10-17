# Plano de implementação — Revisão 3 do módulo de atividades

## Objetivo

Implementar a exclusão virtual de atividades administrativas, conforme a [Revisão 3 do módulo de atividades](../revisao-3-atividades-admin.md), sem remover participantes ou registros físicos associados.

## Fase 1 — Persistência e domínio

1. Criar uma migration para adicionar `deleted_at` anulável e indexado à tabela `atividades`.
2. Adicionar o trait `SoftDeletes` ao model `Atividade` e ajustar os casts necessários.
3. Validar que uma atividade excluída continua presente no banco quando consultada com `withTrashed()`, mas não aparece em consultas padrão.

**Resultado:** a ocultação passa a ser consistente para todas as consultas Eloquent normais de atividades, inclusive relações e exportações existentes.

## Fase 2 — Rota, autorização e ação administrativa

1. Criar a rota `DELETE /admin/atividades/{atividade}`, nomeada `admin.atividades.destroy`.
2. Aplicar os middlewares `auth` e `acesso.modulo:atividades`, reutilizando o controle de acesso já existente.
3. Implementar a ação no `Admin\\AtividadeController`, chamando a exclusão virtual e redirecionando para a listagem com mensagem de sucesso.
4. Garantir que o binding de rota padrão não encontre uma atividade já excluída e responda `404`.

**Resultado:** somente administradores autorizados conseguem efetivar a exclusão virtual.

## Fase 3 — Interface de exclusão

1. Inserir a ação **Excluir atividade** na página de detalhes, separada das ações de navegação e identificada como destrutiva.
2. Criar um diálogo de confirmação responsivo, compatível com desktop e mobile, explicando que a atividade será removida da operação, mas não apagada fisicamente.
3. Enviar o formulário com `@csrf` e `@method('DELETE')` somente após a confirmação.
4. Exibir a mensagem de sucesso na listagem após o redirecionamento.

**Resultado:** não há exclusão acidental por clique único e o retorno ao administrador é claro.

## Fase 4 — Regressão de integrações e validação

1. Cobrir a exclusão virtual por teste automatizado, verificando `deleted_at`, escopo padrão e preservação de alunos e professores relacionados.
2. Verificar que atividades excluídas não aparecem em:
   - listagem e detalhe administrativos;
   - CSV de atividades;
   - relatório CSV de presenças;
   - listagem e registro do módulo de presença.
3. Verificar que um operador exclusivo de presença não alcança a rota de exclusão.
4. Executar Pint, testes Laravel, cache de rotas e cache de views.

**Resultado:** a atividade excluída torna-se invisível em todos os fluxos operacionais sem destruir registros existentes.

## Arquivos previstos

| Área | Arquivos principais |
|---|---|
| Banco e domínio | nova migration em `database/migrations/`; `app/Models/Atividade.php` |
| HTTP | `routes/web.php`; `app/Http/Controllers/Admin/AtividadeController.php` |
| Interface | `resources/views/admin/atividades/show.blade.php`; estilos e JavaScript administrativos, se necessários |
| Qualidade | testes de feature/admin e testes de regressão de exportação e presença |

## Fora deste plano

- restauração de atividade;
- visualização de lixeira;
- exclusão física;
- limpeza de presenças, vínculos ou cadastros de participantes.

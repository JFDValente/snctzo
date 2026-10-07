# Plano de implementação — exportação CSV de atividades

## 1. Objetivo

Implementar o download autenticado de um CSV com todas as atividades cadastradas. O contrato funcional do arquivo, suas colunas e regras de segurança estão em [Gerenciamento administrativo](../admin-gerenciamento.md#53-exportação-csv).

O CSV terá uma linha por atividade. Todos os dados gerais ocuparão colunas próprias; os participantes serão consolidados em uma única coluna no formato `(tipo|nome)`.

## 2. Escopo técnico

- Rota `GET /admin/atividades/exportar`, protegida pelo middleware `auth` e declarada antes de `/admin/atividades/{atividade}`.
- Ação de exportação no controlador administrativo de atividades ou em ação de domínio dedicada, conforme preservar melhor a responsabilidade de consulta.
- Resposta de download em streaming, sem arquivo temporário no disco da Hostinger.
- Consulta sem paginação, ordenada por `created_at` e `id`, ambos crescentes.
- Carregamento antecipado de curso, instituição, professor responsável, alunos e professores participantes.
- Nenhuma migration, alteração de entidade ou modificação de dados.

## 3. Etapas de implementação

### [ ] 3.1 Rota e ponto de entrada

- Declarar a rota nomeada `admin.atividades.exportar` no grupo administrativo autenticado.
- Garantir que a rota está antes do parâmetro `{atividade}` para evitar conflito de resolução.
- Criar o método de controlador ou ação responsável pela resposta de download.

**Verificação:** visitante recebe redirecionamento para login; usuário autenticado recebe resposta de download.

### [ ] 3.2 Consulta e mapeamento

- Consultar todas as atividades sem `paginate()`.
- Carregar relações necessárias antecipadamente.
- Mapear cada atividade para uma única linha com as colunas definidas na especificação.
- Converter participação nos dias para `Sim` ou `Não`.
- Formatar data de inscrição em `dd/mm/aaaa às HH:mm`, no fuso da aplicação.
- Consolidar participantes com `; ` entre itens e `(Aluno|nome)` ou `(Professor|nome)` por item.
- Tratar valores opcionais vazios sem gerar erro nem colunas extras.

**Verificação:** 0, 1 e mais de 20 atividades; participante aluno, professor, ambos e nenhum participante adicional; dados opcionais vazios.

### [ ] 3.3 Geração segura do CSV

- Usar `response()->streamDownload()` e `fputcsv()` com separador `;`.
- Preceder o conteúdo com UTF-8 BOM.
- Gerar o nome de arquivo `atividades-snctzo-2026-AAAAMMDD-HHMM.csv`.
- Aplicar neutralização de fórmula em todos os valores exportados: quando o primeiro caractere for `=`, `+`, `-` ou `@`, prefixar o valor como texto antes de escrevê-lo.
- Não exportar token de submissão, identificadores internos, dados técnicos do e-mail nem checkboxes individuais de aceite.

**Verificação:** abertura correta em planilha com acentos; campos com ponto e vírgula, aspas ou quebras de linha preservados; valores potencialmente interpretáveis como fórmula permanecem texto.

### [ ] 3.4 Interface da listagem

- Adicionar o botão “Exportar CSV” no topo da listagem de atividades.
- Manter o botão acessível e visível sem interferir na paginação ou no link de detalhe.
- Exibir o botão também quando não existirem atividades; o download conterá somente o cabeçalho.

**Verificação:** botão presente em desktop e mobile; download iniciado; paginação continua funcional.

### [ ] 3.5 Qualidade e publicação

- Executar Pint nos arquivos PHP alterados.
- Executar build dos assets somente se houver alteração de CSS ou JavaScript.
- Executar caches de configuração, rotas e views.
- Verificar manualmente o conteúdo do CSV com usuário autenticado.
- Incluir o deploy no procedimento de publicação usual, sem migrations.

**Commit sugerido:** `feat(admin): Exporta atividades em CSV`

## 4. Critérios de aceite

- Apenas usuários autenticados conseguem baixar o arquivo.
- O arquivo contém todas as atividades, não somente a página atual.
- Cada atividade ocupa exatamente uma linha de dados.
- Dados gerais, responsável e e-mail do responsável ocupam colunas próprias.
- Participantes ficam em uma coluna única no formato acordado.
- O arquivo abre corretamente em planilhas comuns, preserva acentuação e não executa fórmulas fornecidas como dados.

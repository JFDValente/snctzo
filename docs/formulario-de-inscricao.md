# Formulário de inscrição de atividades — SNCTZO 2026

## 1. Objetivo

O formulário cadastra, individualmente, cada atividade que participará da SNCTZO 2026. O professor responsável representa o cadastrante e o contato oficial da inscrição.

“Atividade” é o termo oficial do sistema. Oficina, experiência e projeto são exemplos de atividades, não entidades ou classificações separadas.

## 2. Funcionamento geral

- Aplicação web pública, responsiva e sem autenticação.
- Um único formulário HTML dividido visualmente em cinco etapas.
- Os botões **Anterior** e **Próxima** alternam as etapas por JavaScript.
- Cada etapa só avança quando seus campos obrigatórios estiverem válidos.
- A navegação entre etapas preserva os dados na memória do navegador.
- Atualizar ou fechar a página descarta o preenchimento ainda não enviado.
- A etapa 5 realiza um único envio de todos os dados ao backend.
- O backend repete todas as validações antes de persistir a inscrição.
- Cada preenchimento recebe um token único. O backend aceita o token uma única vez para impedir duplicidade por clique duplo ou reenvio do navegador.
- Após o envio, o público não pode editar nem cancelar a atividade. Qualquer alteração depende da organização.
- A confirmação exibe somente: **“Inscrição enviada com sucesso”**, seguida de um botão para iniciar outra inscrição.
- Atualizar a página de confirmação redireciona para o início do formulário.
- O sistema não gera protocolo público.

## 3. Disponibilidade do formulário

A abertura e o encerramento das inscrições serão controlados por variáveis de ambiente, no fuso `America/Sao_Paulo`.

- Sem data de abertura, o sistema não aplica limite inicial.
- Sem data de encerramento, o sistema não aplica limite final.
- Sem ambas as datas, o formulário permanece aberto.
- O backend verifica o prazo novamente na submissão.
- Uma submissão posterior ao encerramento será rejeitada, mesmo que o formulário tenha sido aberto antes do prazo.
- O banner omite o prazo quando a data de encerramento não estiver configurada.

## 4. Etapa 1 — Apresentação e avisos

### Banner

**Inscrições de atividades SNCTZO 2026**

**Realização do evento:**

- 20 de outubro de 2026, das 9h às 21h;
- 21 de outubro de 2026, das 9h às 17h;
- local: Centro Esportivo Miécimo da Silva.

Quando houver prazo de encerramento configurado, exibir:

> Este formulário deve ser preenchido pelo responsável da instituição até {data e hora de encerramento}.

### Avisos

> Cada atividade deve ser cadastrada individualmente.

> Não conformidades nas respostas fornecidas neste formulário podem acarretar a suspensão da atividade.

### Lembretes

- É proibida a realização de vendas de qualquer espécie durante o evento.
- É proibida a divulgação ou manifestação partidária.
- É proibido o uso de material inflamável ou explosivo.
- É proibido o consumo, fornecimento ou porte de bebidas alcoólicas durante o evento.
- É proibido o uso, fornecimento ou porte de drogas ilícitas, armas brancas e armas de fogo.
- São proibidas nudez e outras ações de atentado ao pudor.
- São proibidas atividades que incitem violência ou discriminação de qualquer tipo.
- Os expositores devem zelar pelo espaço público onde ocorre o evento e por suas dependências.
- Os expositores devem respeitar os demais expositores, o público visitante e a comissão organizadora.
- As regras estabelecidas pela comissão organizadora devem ser acatadas para garantir a melhor execução possível do evento.
- Ao chegar ao evento, o responsável pela atividade deve apresentar-se à comissão organizadora para confirmar a presença e a participação da instituição.

Exibir também, em um aviso visual destacado:

> Realização do Evento: É proibido o uso de banner durante o evento.

## 5. Etapa 2 — Unidade acadêmica, curso e responsável

### Unidade acadêmica

No formulário, a entidade de instituição é apresentada ao usuário como **unidade acadêmica**. O campo tem lista fixa, com:

- FCBS — Faculdade de Ciências Biológicas e Saúde;
- FCEE — Faculdade de Ciências Exatas e Engenharias;

FCBS e FCEE são instituições independentes no sistema.

Não há opção pública para criar ou editar unidades acadêmicas.

### Curso principal

- Listar os cursos da instituição selecionada.
- A atividade pertence a um único curso principal.
- Os cursos existentes não podem ser editados pelo formulário público.

Cursos provisionados nesta revisão:

- FCEE: Computação (CC e TCADS), Engenharia de Materiais, Engenharia de Produção, Engenharia Metalúrgica e Tecnologia em Construção Naval.
- FCBS: Farmácia e Ciências Biológicas.

Durante a primeira versão, a migration reinicia os dados de inscrição, as unidades e os cursos, recriando somente esse catálogo com sequenciais reiniciados.

### Professor responsável

Toda atividade exige exatamente um professor responsável. Ele pertence à unidade acadêmica do curso principal e não entra automaticamente na lista de participantes.

Fluxo do campo:

1. Solicitar primeiro o e-mail.
2. Fazer uma busca exata no backend ao concluir o preenchimento do e-mail.
3. Se o professor existir na mesma instituição, preencher e bloquear o nome, sem alterar seus dados cadastrados.
4. Se o e-mail existir em outra instituição, exibir uma mensagem que informe a instituição do professor já cadastrado.
5. Se o e-mail existir na mesma instituição, mas o nome já informado for diferente, sem diferenciar maiúsculas, acentos ou espaços, exibir: **“E-mail já utilizado por outro professor”**.
6. Se o e-mail não existir, solicitar o nome e vincular o novo professor à instituição da atividade.

O e-mail é obrigatório e identifica o professor de forma única, embora a chave primária continue sendo o `id` interno.

## 6. Etapa 3 — Dados da atividade e participantes

### Nome da atividade

Campo textual obrigatório.

Texto de orientação:

> Exemplos: Oficina — Desvendando os números da vida; Experiência — Brincando com formas e volumes.

Oficina e experiência permanecem apenas como exemplos. O formulário não possui um campo separado para tipo de atividade.

### Dias de participação

Caixas de seleção:

- 20/10/2026, das 9h às 21h;
- 21/10/2026, das 9h às 17h.

O usuário pode selecionar um ou ambos os dias. Pelo menos um dia é obrigatório.

### Ciência sobre o responsável

Exibir:

> **Importante:** o nome do professor responsável deve constar na lista de participantes caso ele deva ser incluído no resumo ou e-book.

Exigir uma caixa **Ciente**. O professor responsável poderá ser adicionado manualmente como participante.

### Participantes

- Exigir pelo menos um participante.
- Aceitar aluno ou professor como primeiro participante.
- A ficha de inclusão apresenta **Tipo de participante** e as opções **Aluno** e **Professor** na mesma linha.
- O botão `+` ao final da ficha inclui o participante de fato, limpa a ficha e adiciona uma linha à tabela de participantes.
- A tabela exibe tipo, nome e uma ação `X` para remover a linha.
- Permitir a inclusão dinâmica de quantos participantes forem necessários.
- Exigir escolha exclusiva entre **Aluno** e **Professor** em cada registro.
- Permitir que a mesma pessoa participe de várias atividades.
- Impedir a repetição da mesma pessoa dentro de uma atividade.

#### Participante aluno

Campos:

- nome completo.

Regras:

- O frontend pré-carrega alunos já associados à unidade acadêmica e alunos sem curso para autocomplete.
- Ao selecionar um aluno existente, o nome fica bloqueado.
- Um aluno novo é criado sem curso informado.
- O sistema permite duplicidade global de alunos porque não existe uma chave forte, como matrícula.

#### Participante professor

Campos:

- nome completo.

Regras:

- O e-mail não é solicitado para professor participante.
- Um professor participante novo é criado sem unidade acadêmica informada.

#### Duplicidades dentro da atividade

Bloquear:

- o mesmo registro existente adicionado duas vezes;
- dois professores novos com o mesmo nome, ignorando maiúsculas, acentos e espaços excedentes;
- dois alunos novos com o mesmo nome, ignorando maiúsculas, acentos e espaços excedentes.

### Resumo da atividade

Campo obrigatório com altura inicial de seis linhas e limite de 3.000 caracteres, informado ao usuário na tela.

Texto de orientação:

> Espaço destinado à descrição da atividade. Informe o tipo de abordagem e o resultado esperado para o público. Esta informação constará no resumo ou e-book.

### Observações

Único campo textual opcional da atividade, além dos links opcionais da instituição. Terá altura inicial de seis linhas e limite de 5.000 caracteres.

Texto de orientação:

> Espaço destinado a informações adicionais sobre a atividade, como preferência de localização em relação ao som ou a outras unidades acadêmicas. Informe também a necessidade de tomada elétrica.

## 7. Etapa 4 — Informações sobre a realização da atividade

Exigir uma caixa **Ciente** para cada declaração, nesta ordem inicial:

- É proibido o uso de banner durante o evento.
- Cada professor terá direito a um espaço, independente da quantidade de projetos. O número de mesas disponibilizado para o professor será informado pela organização, em momento oportuno.
- A montagem dos estandes ocorrerá na tarde de 20/10/2026.
- As atividades devem ser dinâmicas e interativas.
- Não é permitido nenhum tipo de comércio no interior do ginásio durante o evento.
- Os expositores devem chegar com pelo menos 30 minutos de antecedência.
- Não são permitidas bebidas alcoólicas nem objetos perfurocortantes.
- A realização das atividades é voluntária. A organização do evento não oferecerá remuneração nem auxílio financeiro às pessoas envolvidas.

## 8. Etapa 5 — Condições de participação

### Conformidade da atividade

Exigir a resposta **Sim** para todas as declarações:

- A atividade não faz divulgação ou manifestação partidária por citação direta ou indireta, como o uso de termos associados a determinado candidato.
- A atividade não faz apologia ao uso de material inflamável ou explosivo.
- A atividade não faz apologia ao consumo de bebidas alcoólicas.
- A atividade não faz apologia ao uso, fornecimento ou porte de drogas ilícitas, armas brancas ou armas de fogo.
- A atividade não apresenta nudez nem qualquer outra ação de atentado ao pudor.
- A atividade não incita violência nem discriminação de qualquer tipo.

### Cessão de imagem

Exibir e exigir **De acordo**:

> Ao participar do evento, de forma presencial ou remota, o responsável pela atividade e todas as pessoas do grupo cedem voluntária e gratuitamente seus direitos de imagem. Também declaram ciência e concordância com a publicação do material no canal do YouTube e nas redes sociais da SNCT ZO — Semana Nacional de Ciência e Tecnologia na Zona Oeste.

### Confirmação de presença

Exibir e exigir **De acordo**:

> Um representante da instituição ou atividade deve apresentar-se à comissão organizadora para confirmar a presença no evento. A ausência dessa confirmação pode causar a perda do direito ao certificado e à publicação do resumo.

### Ciência geral

Exibir e exigir **Sim**:

> Li e estou ciente das informações deste formulário, necessárias à participação e à realização das atividades durante a SNCTZO 2026.

### Persistência dos aceites

- A submissão só será aceita quando todas as caixas obrigatórias estiverem marcadas.
- O banco não armazenará uma coluna para cada aceite.
- A atividade armazenará a data e hora do aceite e a versão dos termos exibidos.
- O texto versionado dos termos permanecerá no código-fonte.

## 9. Submissão e transação

Na submissão final, o backend deverá:

1. Verificar a janela de inscrições e o limite de requisições.
2. Verificar o token de idempotência.
3. Validar todos os campos e aceites.
4. Resolver registros existentes e preparar registros novos.
5. Persistir instituição, cursos, pessoas, atividade e participações em uma única transação.
6. Confirmar a transação.
7. Tentar enviar o e-mail de confirmação.
8. Exibir a mensagem de sucesso.

Uma falha no e-mail não desfaz a inscrição. O sistema registra o erro no log e mantém o campo de confirmação de envio vazio.

## 10. E-mail de confirmação

O e-mail será enviado ao professor responsável via SMTP após a persistência da atividade. Ele conterá uma cópia completa da inscrição, incluindo:

- unidade acadêmica;
- curso principal;
- professor responsável;
- nome, dias, resumo, observações e links da atividade;
- lista de participantes;
- todos os textos aceitos;
- versão e data dos termos.

O e-mail será o último item implementado. A primeira versão poderá ser publicada sem ele caso a cota de desenvolvimento termine antes dessa etapa.

## 11. Proteções do formulário

- Proteção CSRF.
- Campo invisível antirobô (*honeypot*).
- Limite configurável de 100 submissões por IP por hora.
- Token único de idempotência por preenchimento.
- Validação completa no backend.
- Escape de conteúdo na apresentação.
- Registros existentes bloqueados contra edição pública.

## 12. Melhorias futuras

- Persistência de rascunhos.
- Edição ou cancelamento público após a submissão.
- Busca de alunos no backend em vez de pré-carga no frontend.
- Administração da abertura e do encerramento das inscrições.
- Administração dos dados de instituições, cursos e pessoas.
- Reenvio administrativo de confirmações por e-mail.

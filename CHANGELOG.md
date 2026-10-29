# Histórico de mudanças

Tudo o que mudou no projeto, da primeira versão para a mais recente.
O formato segue a ideia do [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/) e as versões seguem o [versionamento semântico](https://semver.org/lang/pt-BR/).

## [0.1.0] 28/10/2026: primeira versão

Nasceu com o nome de trabalho **Profissionais Progressistas**, para substituir a troca de contatos que acontecia no grupo.

### Base
- Estrutura em PHP puro com um único ponto de entrada (`public/index.php`) e rotas simples.
- Banco com estados e os 5.570 municípios do IBGE, mais 186 tipos de serviço em 16 grupos.
- Instalador de linha de comando que cria as tabelas, importa os dados e cria o administrador.

### Cadastro e conta
- Cadastro com foto ou logo, nome do responsável, empresa ou nome completo, telefone, WhatsApp, Instagram, descrição, tipo de serviço, estado, cidade e área de atendimento.
- Opção "Outro" no tipo de serviço, com campo de texto livre.
- Aceite da política de privacidade registrado com data, e página de política de privacidade.
- Login por e-mail e senha, recuperação de senha por e-mail e troca de senha.
- Limite de 2 alterações de perfil por pessoa. Salvar sem mudanças não conta.
- Pedido de nova alteração com motivo, quando o limite acaba.
- Exclusão da própria conta com confirmação por senha.

### Busca e perfil
- Busca só para membros logados, com filtros por serviço, estado e cidade que mostram apenas opções com cadastro.
- Busca por texto em nome, serviço e descrição.
- Resultados consideram a área de atendimento, com quem atende a região numa seção separada.
- Perfil com botões de WhatsApp, ligar, Instagram, compartilhar e salvar contato na agenda (vCard).

### Tela inicial
- Contadores de profissionais ativos e cadastros no total, sem contar a administração.
- Carrossel com os cadastros mais recentes, serviços presentes na rede e profissionais da cidade de quem está logado.

### Administração
- Painel com números da rede.
- Gestão de usuários: editar dados, ajustar ou zerar alterações, bloquear com motivo, desbloquear, definir senha e excluir.
- Fila de pedidos de alteração com aprovação, recusa e resposta.
- Histórico com antes e depois de cada edição. Contas excluídas continuam guardadas no histórico, com a foto movida para uma pasta fora da web.
- Gestão dos tipos de serviço, incluindo transformar textos de "Outro" em tipo oficial.

### Visual
- Identidade própria em branco e vermelho, com textos em preto.
- Ícones desenhados para o projeto e componentes próprios no lugar dos padrões do navegador: seletores com busca, caixas de marcar, chaves, diálogos e avisos.
- PWA instalável, com tela de "sem conexão".

## [0.1.1] 28/10/2026: ajustes depois dos primeiros testes

### Corrigido
- Ao editar o perfil, as cidades extras da área de atendimento se perdiam e a edição era recusada.
- A tela inicial tinha rolagem lateral no celular por causa de um elemento decorativo.
- O carrossel de novos cadastros começava colado na borda da tela.
- Títulos em fonte estreita ficavam com as palavras grudadas.
- O ícone de "Sair" apontava para dentro em vez de para fora.
- O instalador mostrava avisos de terminal quando a senha era enviada por outro programa.

### Alterado
- A fonte Archivo passou a ser servida pelo próprio app, sem depender do Google Fonts.
- Campos de texto longos crescem conforme o conteúdo e não mostram mais a alça de redimensionar do navegador.
- Datas nos dados guardados de contas excluídas aparecem no formato brasileiro.

## [0.2.0] 28/10/2026: novo nome, Esquerda Indica

### Alterado
- O projeto passou a se chamar **Esquerda Indica**. O nome anterior era longo para domínio e para o ícone no celular.
- Nova marca "Ei", com o pingo do "i" destacado como quem aponta uma indicação.
- Ícones do app instalado, e-mails, textos de compartilhamento, mensagem do WhatsApp e política de privacidade atualizados com o novo nome.
- Nova frase na tela inicial: "Precisa de alguém? A rede indica."

### Corrigido
- A tela de entrar mostrava a logo duas vezes.

## [0.3.0] 28/10/2026: publicação

### Adicionado
- Script `bin/publicar.sh` para publicar e atualizar o app num servidor Ubuntu: instala o que faltar, cria banco e configuração, configura Nginx ou Apache sem mexer em outros sites e ativa o HTTPS com Let's Encrypt.
- O script funciona tanto a partir do repositório clonado quanto de um arquivo zip, e preserva banco, configuração e fotos nas atualizações.
- `bin/dados-exemplo.php` com perfis fictícios para desenvolvimento.
- Documentação: README, guia de instalação, histórico de mudanças, roadmap e capturas de tela.

## [0.4.0] 29/10/2026: vagas de emprego

### Adicionado
- **Dados da empresa:** CNPJ e razão social, opcionais no cadastro e numa área própria em Minha conta. O CNPJ é conferido pelos dígitos verificadores, aceita o novo formato com letras da Receita Federal e não pode se repetir entre contas. Aparece no perfil para dar mais confiança.
- **Vagas de emprego:** empresas com CNPJ e razão social anunciam vagas com cargo, tipo de contratação, modalidade, local, quantidade, salário (a combinar, valor ou faixa), descrição, requisitos, benefícios e forma de candidatura (WhatsApp, e-mail ou link).
- Cada empresa pode manter até 3 vagas ativas ao mesmo tempo. Vagas pausadas, encerradas ou vencidas não contam.
- As vagas ficam no ar por 30 dias e podem ser renovadas, pausadas, republicadas ou encerradas em Minhas vagas.
- Nova aba **Vagas** com busca por texto, estado, cidade, contratação e modalidade. Como na busca de profissionais, só aparecem opções que têm vaga. Vagas remotas aparecem em qualquer local filtrado.
- Página de cada vaga com botão de candidatura direto para a empresa e compartilhamento.
- Vagas mais recentes na tela inicial e vagas abertas no perfil da empresa.
- Cartão em Minha conta que mostra o que falta para liberar as vagas e quanto do limite está em uso.
- **Administração:**
  - Na ficha de cada usuário: acesso a vagas automático, sempre liberado ou bloqueado, limite próprio de vagas e plano.
  - Lista de vagas com filtros, pausa e remoção com motivo, que a empresa vê.
  - Tela de planos, com nome, preço, período e limite de vagas.
  - Tela de configurações: regra de edição do perfil, vagas ligadas ou desligadas no site, limite padrão e validade das vagas.
- **Base para planos pagos:** tabelas de planos, assinaturas e pagamentos. O limite de cada empresa segue esta ordem: ajuste da administração, plano ativo, padrão do site. Hoje existe só o plano Gratuito.
- **Migrações de banco:** `bin/migrar.php` aplica as mudanças pendentes. O `publicar.sh` roda as migrações sozinho nas atualizações.

### Alterado
- Nova regra padrão para editar o perfil: **uma alteração a cada 24 horas**, sem limite total. A regra anterior, com quantidade fixa e pedido à administração, continua disponível nas configurações.
- No modo de 24 horas, quem precisa mudar algo antes do prazo pode pedir liberação. Se aprovado, a edição é liberada na hora.
- Incluir ou corrigir os dados da empresa, trocar a senha e editar vagas não gastam alterações do perfil.
- Contas excluídas guardam também os dados da empresa e as vagas no histórico.
- Política de privacidade atualizada com CNPJ e vagas.

### Corrigido
- Ao escolher "Outro" no tipo de serviço, o campo de texto podia roubar o foco de quem já estava digitando em outro campo.

## [0.4.1] 29/10/2026: nova identidade visual

### Alterado
- Novo ícone "ei" em todo o app: topo das telas, ícone instalado no celular (Android e iPhone), favicon e cabeçalho dos e-mails.
- Logo completo "Esquerda Indica" no topo da tela de apresentação, que passa a abrir direto no logo.
- As telas de apresentação e inicial mostram só o número de profissionais ativos. O total de cadastros fica apenas no painel da administração.

### Removido
- Marca "Ei" desenhada em código, substituída pelo ícone oficial.

## [0.4.2] 29/10/2026: repositório público e documentação

### Adicionado
- Guia de [arquitetura](docs/arquitetura.md) com o caminho de uma requisição, o padrão dos formulários, o modelo do banco em diagrama, as migrações e receitas para as mudanças mais comuns.
- Guia de contribuição (`CONTRIBUTING.md`) com as convenções de código, textos, visual, commits e versões.
- Política de segurança (`SECURITY.md`) com o relato privado de vulnerabilidades.
- Modelos de issue (problema e sugestão) e de pull request.
- Verificação automática no GitHub: sintaxe do PHP nas versões 8.1, 8.3 e 8.4, `shellcheck` no script de publicação e busca por travessões nos textos.

### Alterado
- README reorganizado, com sumário, tabela de decisões de projeto, diagrama do funcionamento e índice da documentação.
- Domínio de exemplo da configuração trocado pelo oficial, `esquerdaindica.com`.

### Corrigido
- O `publicar.sh` pedia pacotes `php8.3-*`, que não existem no Ubuntu 26.04. Agora usa os pacotes genéricos da distribuição e funciona em qualquer versão a partir da 24.04.

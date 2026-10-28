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

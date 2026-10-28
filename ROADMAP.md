# Próximos passos

O que está planejado, na ordem em que deve ser feito. A ordem pode mudar conforme o grupo for usando o app e pedindo coisas.

Itens marcados já estão prontos e aparecem no [CHANGELOG.md](CHANGELOG.md).

## Feito até aqui

- [x] Cadastro completo com foto, área de atendimento e aceite da LGPD (0.1.0)
- [x] Busca por serviço, estado e cidade, só com opções que têm cadastro (0.1.0)
- [x] Limite de alterações de perfil com pedido de liberação (0.1.0)
- [x] Painel de administração com histórico completo (0.1.0)
- [x] App instalável no celular (0.1.0)
- [x] Novo nome e marca: Esquerda Indica (0.2.0)
- [x] Script de publicação no servidor (0.3.0)

## 0.4: deixar a operação segura

Antes de abrir para o grupo inteiro.

- [ ] Botão no painel para enviar um e-mail de teste e conferir o SMTP.
- [ ] Confirmação de e-mail no cadastro, para evitar contas com e-mail errado.
- [ ] Aprovação de novos cadastros pela administração, que pode ser ligada ou desligada na configuração.
- [ ] Botão "Denunciar perfil", com fila de denúncias no painel.
- [ ] Aviso por e-mail para a administração quando chegar um pedido de alteração ou uma denúncia.
- [ ] Cópia de segurança diária do banco e das fotos.

## 0.5: indicações de verdade

A ideia que dá nome ao projeto: quem contratou alguém da rede pode deixar uma indicação.

- [ ] Membros indicam profissionais com quem já trabalharam, com um texto curto.
- [ ] Quantidade de indicações no perfil e opção de ordenar a busca pelos mais indicados.
- [ ] Moderação das indicações pela administração.
- [ ] Lista de profissionais salvos, para encontrar de novo rápido.

## 0.6: busca melhor

- [ ] Mais de um tipo de serviço por perfil (por exemplo, pedreiro e pintor).
- [ ] Busca por distância, usando a localização dos municípios ("até 50 km de mim").
- [ ] Busca que tolera erros de digitação e palavras parecidas.
- [ ] Sugestões de serviço enquanto a pessoa digita.

## 0.7: comunicação

- [ ] Notificações no celular: pedido aprovado, novos profissionais na sua cidade.
- [ ] Link de convite pessoal, para saber quem trouxe cada membro.
- [ ] Mensagem de boas-vindas com dicas para completar o perfil.

## 1.0: perfil mais completo

- [ ] Galeria com até 6 fotos de trabalhos feitos.
- [ ] Página pública opcional do perfil, para quem quiser divulgar fora do grupo (com consentimento separado).
- [ ] Exportar a lista de usuários em planilha e gráficos de crescimento no painel.
- [ ] Verificação em duas etapas para quem é administrador.

## Por baixo dos panos

Melhorias que não aparecem na tela, mas facilitam manter o projeto.

- [ ] Testes automatizados dos fluxos principais (cadastro, edição, pedidos, exclusão).
- [ ] Verificação automática a cada envio para o GitHub.
- [ ] Migrações de banco versionadas, para atualizar a estrutura sem rodar SQL à mão.
- [ ] Limpeza automática de tokens e tentativas de login antigos.

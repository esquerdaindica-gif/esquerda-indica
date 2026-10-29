# Segurança

O Esquerda Indica guarda dados pessoais de membros do grupo: nome, telefone, WhatsApp, cidade e, para empresas, CNPJ. Por isso, qualquer falha de segurança é tratada com prioridade.

## Como relatar uma vulnerabilidade

**Não abra uma issue pública.** Uma falha descrita em público pode ser explorada antes da correção.

Use o relato privado do GitHub:

1. Abra a aba [Security](https://github.com/esquerdaindica-gif/esquerda-indica/security) do repositório.
2. Clique em **Report a vulnerability**.
3. Descreva o problema, os passos para reproduzir e o que alguém conseguiria fazer com ele.

Só o mantenedor vê o relato.

## O que esperar

- Confirmação de recebimento em até 3 dias.
- Uma avaliação do risco e um prazo para a correção.
- Aviso quando a correção for publicada, com crédito a quem relatou, se a pessoa quiser.

## Versões atendidas

Só a versão mais recente do branch `main` recebe correções de segurança. Quem mantém uma instalação própria deve atualizar com `git pull` e `bin/publicar.sh`, como explica o [guia de instalação](docs/instalacao.md).

## O que já está protegido

As proteções do app estão descritas em [Segurança e privacidade](README.md#segurança-e-privacidade), no README.

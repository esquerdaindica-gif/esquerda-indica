# Como contribuir

Obrigado por querer ajudar o Esquerda Indica. Este guia reúne o que é preciso saber para propor uma mudança que seja fácil de revisar e de publicar.

## Antes de começar

- **Problema ou ideia?** Abra uma [issue](https://github.com/esquerdaindica-gif/esquerda-indica/issues/new/choose) usando um dos modelos. Para mudanças grandes, combine a ideia na issue antes de escrever o código.
- **Falha de segurança?** Não abra issue pública. Siga o [SECURITY.md](SECURITY.md).
- **Entendendo o código:** o guia de [arquitetura](docs/arquitetura.md) mostra o caminho de uma requisição, o padrão dos formulários e receitas para as mudanças mais comuns.

## Preparando o ambiente

Siga [Rodando no seu computador](README.md#rodando-no-seu-computador) no README. Com `php bin/dados-exemplo.php` o app fica cheio de perfis fictícios, o que ajuda a testar busca, filtros e o painel.

## Fluxo de trabalho

1. Crie um branch a partir do `main` com um nome curto e descritivo:
   ```bash
   git switch -c vagas-filtro-salario
   ```
2. Faça a mudança em commits pequenos, cada um com um objetivo.
3. Teste no navegador, no tamanho de celular, as telas que você mexeu.
4. Rode as verificações (as mesmas que o GitHub roda em cada pull request):
   ```bash
   find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
   shellcheck -S warning bin/publicar.sh
   ```
5. Registre a mudança no [CHANGELOG.md](CHANGELOG.md).
6. Abra o pull request e preencha o modelo.

## Convenções do projeto

### Código

- **PHP puro, sem dependências.** Nada de framework, Composer ou biblioteca de interface. Se algo parecer exigir uma dependência, converse antes na issue.
- **Nomes em português**, como no resto do código: `exigir_login()`, `vagas_ativas_count()`, `$pendente`.
- **Formatação:** o [.editorconfig](.editorconfig) define 4 espaços no PHP e 2 no CSS, JS e Markdown, UTF-8 e quebra de linha LF.
- **Banco:** sempre com `?` e os valores em array (`q()`, `one()`, `all()`, `val()`). Nunca coloque variável dentro do SQL.
- **Telas:** todo valor que vem do banco ou do usuário passa por `e()`. Todo formulário `POST` tem `<?= csrf_field() ?>`.
- **Regras de negócio** ficam em `app/lib/regras.php`, não espalhadas pelas páginas.
- **Mudança no banco** é sempre uma migração nova em `database/migracoes/`. Nunca edite uma que já foi publicada.
- **Ações importantes** (editar, bloquear, aprovar, excluir) chamam `historico()`.

### Textos da interface

- Português do Brasil, frases curtas e diretas, falando com a pessoa ("Você já pode editar").
- **Sem travessão em nenhum texto.** Use ponto, vírgula ou dois-pontos.
- Botões dizem a ação ("Salvar alterações", "Pausar vaga"), não "OK" ou "Enviar".
- Mensagens de erro explicam como resolver ("Use no máximo 500 caracteres.").

### Visual

- Branco predominante com vermelho da marca e texto preto sobre branco.
- Ícones e componentes são do próprio projeto (`app/views/icones.php` e `public/assets/css/app.css`). Não use elementos padrão do navegador quando já existir um componente próprio.
- Se mudar CSS, JS ou ícones, aumente `VERSAO` em `public/sw.js` para que quem já instalou o app receba a novidade.

### Commits

Mensagens em português, descrevendo o que muda para quem usa ou mantém o app. A primeira linha tem até 72 caracteres, sem ponto final. Se precisar explicar o porquê, deixe uma linha em branco e escreva o corpo.

```
Filtro de salário na busca de vagas

Empresas pediam para destacar vagas com salário informado. O filtro
mostra só as vagas com valor ou faixa e esconde as "a combinar".
```

### Versões

O projeto segue o [versionamento semântico](https://semver.org/lang/pt-BR/):

- **correção** (0.4.**1**): conserta algo sem mudar o comportamento esperado;
- **menor** (0.**5**.0): funcionalidade nova, compatível com o que existe;
- **maior** (**1**.0.0): mudança que exige ação de quem administra o app.

## Checklist do pull request

- [ ] A mudança tem um objetivo claro e está descrita no pull request
- [ ] Testei no celular (ou no modo celular do navegador) as telas afetadas
- [ ] `php -l` e `shellcheck` passam sem erros
- [ ] Mudanças no banco estão numa migração nova
- [ ] Textos novos seguem as convenções acima, sem travessão
- [ ] O CHANGELOG.md foi atualizado

<header class="capa-logo">
  <picture>
    <source srcset="<?= e(asset('img/logo.webp')) ?>" type="image/webp">
    <img src="<?= e(asset('img/logo.png')) ?>" alt="Esquerda Indica" width="1100" height="415">
  </picture>
</header>
<section class="capa">
  <h1 class="capa-titulo">Os contatos do grupo, agora na palma da mão.</h1>
  <p class="capa-texto">Encontre profissionais de confiança da nossa rede por serviço, estado e cidade. E deixe seu trabalho fácil de achar.</p>
  <div class="numeros">
    <div class="numero"><span class="numero-valor"><?= numero($cont['ativos']) ?></span><span class="numero-rotulo"><?= $cont['ativos'] === 1 ? 'profissional ativo' : 'profissionais ativos' ?></span></div>
  </div>
  <div class="botoes">
    <a class="botao botao-branco" href="<?= e(url('cadastro')) ?>">Criar conta</a>
    <a class="botao botao-contorno-branco" href="<?= e(url('entrar')) ?>">Entrar</a>
  </div>
</section>

<section class="secao">
  <h2 class="secao-titulo" style="margin-bottom:4px">Como funciona</h2>
  <div class="passos">
    <div class="passo"><span class="passo-num">1</span><div><h3>Faça seu cadastro</h3><p>Conte o que você faz, onde atende e como falar com você.</p></div></div>
    <div class="passo"><span class="passo-num">2</span><div><h3>Busque quem você precisa</h3><p>Filtre por tipo de serviço, estado e cidade.</p></div></div>
    <div class="passo"><span class="passo-num">3</span><div><h3>Chame direto</h3><p>WhatsApp, ligação ou Instagram em um toque, sem intermediários.</p></div></div>
  </div>
</section>

<section class="secao">
  <div class="aviso"><?= icon('cadeado') ?><p>Os contatos só aparecem para quem tem cadastro. Seus dados não ficam abertos na internet.</p></div>
  <p class="centro texto-pequeno texto-fraco" style="margin-top:18px"><a class="link" href="<?= e(url('privacidade')) ?>">Política de privacidade</a></p>
</section>

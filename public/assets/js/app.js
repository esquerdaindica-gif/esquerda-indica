/* Profissionais Progressistas: interações da interface */
(function () {
  'use strict';

  var BASE = document.body.getAttribute('data-base') || '';
  var DADOS = {};
  var fonteDados = document.getElementById('pp-dados');
  if (fonteDados) { try { DADOS = JSON.parse(fonteDados.textContent); } catch (e) {} }

  function $(s, el) { return (el || document).querySelector(s); }
  function $$(s, el) { return Array.prototype.slice.call((el || document).querySelectorAll(s)); }
  function icone(nome) { return '<svg class="ic" aria-hidden="true"><use href="#i-' + nome + '"/></svg>'; }
  function semAcento(s) { return (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); }
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

  /* ========== Avisos flutuantes ========== */
  function sumirToast(t) {
    t.classList.add('saindo');
    setTimeout(function () { t.remove(); }, 300);
  }
  function toast(msg, tipo) {
    var t = document.createElement('div');
    t.className = 'toast' + (tipo === 'erro' ? ' toast-erro' : '');
    t.innerHTML = icone(tipo === 'erro' ? 'alerta' : 'check') + '<span>' + esc(msg) + '</span>';
    $('#toasts').appendChild(t);
    setTimeout(function () { sumirToast(t); }, 4200);
  }
  $$('[data-toast]').forEach(function (t, i) {
    setTimeout(function () { sumirToast(t); }, 4800 + i * 400);
    t.addEventListener('click', function () { sumirToast(t); });
  });
  window.ppToast = toast;

  /* ========== Folha inferior de seleção ========== */
  var folha = $('#folha');
  var folhaLista = $('#folha-lista');
  var folhaFiltro = $('#folha-filtro');
  var folhaRodape = $('#folha-rodape');
  var atual = null; // { el, opcoes, multiplo, selecionados }
  var ultimoFoco = null;
  var cacheUrl = {};

  function obterOpcoes(el) {
    var url = el.getAttribute('data-url');
    if (url) {
      var dep = el.getAttribute('data-depende');
      var depVal = dep ? valorCampo(el.closest('form'), dep) : '';
      if (dep && !depVal) return Promise.resolve([]);
      var u = BASE + url + encodeURIComponent(depVal);
      if (cacheUrl[u]) return Promise.resolve(cacheUrl[u]);
      return fetch(u, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) { cacheUrl[u] = d; return d; });
    }
    return Promise.resolve(DADOS[el.getAttribute('data-fonte')] || []);
  }

  function valorCampo(form, nome) {
    var i = form && form.querySelector('input[name="' + nome + '"]');
    return i ? i.value : '';
  }

  function valoresAtuais(el) {
    if (el.hasAttribute('data-multiplo')) {
      return $$('input[type="hidden"]', el).map(function (i) { return i.value; });
    }
    var i = $('input[type="hidden"]', el);
    return i && i.value ? [i.value] : [];
  }

  function abrirSeletor(el) {
    ultimoFoco = document.activeElement;
    var multiplo = el.hasAttribute('data-multiplo');
    atual = { el: el, opcoes: [], multiplo: multiplo, selecionados: valoresAtuais(el) };
    $('#folha-titulo').textContent = el.getAttribute('data-titulo') || 'Escolha uma opção';
    folhaFiltro.value = '';
    folhaRodape.hidden = !multiplo;
    folhaLista.innerHTML = '<div class="folha-vazia">Carregando…</div>';
    folha.hidden = false;
    document.documentElement.style.overflow = 'hidden';
    obterOpcoes(el).then(function (ops) {
      if (!atual || atual.el !== el) return;
      var lista = ops.slice();
      var limpar = el.getAttribute('data-limpar');
      if (limpar) lista.unshift({ v: '', t: limpar, especial: true });
      if (el.hasAttribute('data-outro')) lista.push({ v: 'outro', t: 'Outro (vou escrever)', especial: true, g: 'Não encontrou?' });
      atual.opcoes = lista;
      $('.folha-busca').hidden = lista.length <= 10;
      desenharOpcoes('');
      if (lista.length > 10 && window.matchMedia('(min-width: 560px)').matches) folhaFiltro.focus();
      else {
        var sel = $('.folha-op[aria-selected="true"]', folhaLista) || $('.folha-op', folhaLista);
        if (sel) { sel.focus({ preventScroll: true }); sel.scrollIntoView({ block: 'center' }); }
      }
    }).catch(function () {
      folhaLista.innerHTML = '<div class="folha-vazia">Não foi possível carregar as opções. Verifique a conexão.</div>';
    });
  }

  function desenharOpcoes(termo) {
    var t = semAcento(termo.trim());
    var frag = document.createDocumentFragment();
    var grupoAtual = null, total = 0;
    atual.opcoes.forEach(function (o) {
      if (t && !o.especial && semAcento(o.t + ' ' + (o.s || '') + ' ' + (o.g || '')).indexOf(t) === -1) return;
      if (t && o.especial && o.v === '') return;
      if (o.g && o.g !== grupoAtual) {
        grupoAtual = o.g;
        var g = document.createElement('div');
        g.className = 'folha-grupo';
        g.textContent = o.g;
        frag.appendChild(g);
      }
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'folha-op' + (o.especial ? ' especial' : '');
      b.setAttribute('role', 'option');
      b.setAttribute('data-v', o.v);
      var sel = atual.selecionados.indexOf(String(o.v)) !== -1 || (o.v === '' && !atual.selecionados.length && !atual.multiplo);
      b.setAttribute('aria-selected', sel ? 'true' : 'false');
      b.innerHTML = '<i class="marca-op">' + icone('check') + '</i><span>' + esc(o.t) + '</span>' +
        (o.n != null ? '<small>' + o.n + '</small>' : (o.s ? '<small>' + esc(o.s) + '</small>' : ''));
      b._op = o;
      frag.appendChild(b);
      total++;
    });
    folhaLista.innerHTML = '';
    if (!total) {
      folhaLista.innerHTML = '<div class="folha-vazia">Nada encontrado para “' + esc(termo) + '”.</div>';
      return;
    }
    folhaLista.appendChild(frag);
  }

  function fecharFolha() {
    folha.hidden = true;
    document.documentElement.style.overflow = '';
    if (atual && atual.multiplo) aplicarMultiplo();
    atual = null;
    if (ultimoFoco && ultimoFoco.focus) ultimoFoco.focus({ preventScroll: true });
  }

  function aplicarSimples(el, op) {
    var input = $('input[type="hidden"]', el);
    var mudou = input.value !== String(op.v);
    input.value = op.v;
    rotular(el, op.v === '' ? '' : (op.r || op.t));
    if (mudou) {
      input.dispatchEvent(new Event('change', { bubbles: true }));
      atualizarDependentes(el);
    }
    el.classList.remove('tem-erro');
    if (mudou && el.hasAttribute('data-enviar')) {
      var f = el.closest('form');
      $$('[data-limpar-ao-mudar="' + input.name + '"]', f).forEach(function (i) { i.value = ''; });
      f.submit();
    }
  }

  function rotular(el, texto) {
    var span = $('.seletor-botao > span, .filtro > span', el);
    var vazio = el.getAttribute('data-vazio') || 'Selecione';
    span.textContent = texto || vazio;
    span.classList.toggle('vazio', !texto);
    var b = $('button', el);
    if (b.classList.contains('filtro')) b.classList.toggle('ativo', !!texto);
  }

  function atualizarDependentes(pai) {
    var nome = $('input[type="hidden"]', pai).name;
    var form = pai.closest('form');
    $$('[data-seletor][data-depende="' + nome + '"]', form).forEach(function (filho) {
      var v = $('input[type="hidden"]', pai).value;
      if (filho.hasAttribute('data-multiplo')) {
        $$('input[type="hidden"]', filho).forEach(function (i) { i.remove(); });
        desenharChips(filho, []);
      } else {
        $('input[type="hidden"]', filho).value = '';
        rotular(filho, '');
      }
      $('button', filho).disabled = !v;
    });
  }

  function aplicarMultiplo() {
    var el = atual.el;
    var nome = el.getAttribute('data-nome');
    $$('input[type="hidden"]', el).forEach(function (i) { i.remove(); });
    var escolhidos = [];
    atual.selecionados.forEach(function (v) {
      var op = atual.opcoes.filter(function (o) { return String(o.v) === String(v); })[0];
      var rot = op ? op.t : ($('[data-chip="' + v + '"]', el) || {}).textContent;
      var i = document.createElement('input');
      i.type = 'hidden'; i.name = nome + '[]'; i.value = v;
      el.appendChild(i);
      escolhidos.push({ v: v, t: rot || v });
    });
    desenharChips(el, escolhidos);
  }

  function desenharChips(el, itens) {
    var box = $('.seletor-chips', el);
    if (!box) return;
    box.innerHTML = '';
    itens.forEach(function (it) {
      var c = document.createElement('span');
      c.className = 'chip';
      c.innerHTML = '<span data-chip="' + esc(it.v) + '">' + esc(it.t) + '</span><button type="button" aria-label="Remover ' + esc(it.t) + '">' + icone('fechar') + '</button>';
      $('button', c).addEventListener('click', function () {
        var inp = $$('input[type="hidden"]', el).filter(function (i) { return i.value === String(it.v); })[0];
        if (inp) inp.remove();
        c.remove();
      });
      box.appendChild(c);
    });
    var rot = $('.seletor-botao > span', el);
    var n = itens.length;
    rot.textContent = n ? (n === 1 ? '1 cidade escolhida' : n + ' cidades escolhidas') : (el.getAttribute('data-vazio') || 'Adicionar');
    rot.classList.toggle('vazio', !n);
  }

  if (folha) {
    $$('[data-fechar-folha]', folha).forEach(function (b) { b.addEventListener('click', fecharFolha); });
    $('[data-folha-concluir]', folha).addEventListener('click', fecharFolha);
    folhaFiltro.addEventListener('input', function () { desenharOpcoes(folhaFiltro.value); });
    folhaFiltro.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); var p = $('.folha-op', folhaLista); if (p) p.click(); }
      if (e.key === 'ArrowDown') { e.preventDefault(); var q = $('.folha-op', folhaLista); if (q) q.focus(); }
    });
    folhaLista.addEventListener('click', function (e) {
      var b = e.target.closest('.folha-op');
      if (!b || !atual) return;
      var op = b._op;
      if (atual.multiplo) {
        var v = String(op.v), ix = atual.selecionados.indexOf(v);
        if (ix === -1) {
          var max = parseInt(atual.el.getAttribute('data-max') || '99', 10);
          if (atual.selecionados.length >= max) { toast('Você pode escolher no máximo ' + max + '.', 'erro'); return; }
          atual.selecionados.push(v);
        } else atual.selecionados.splice(ix, 1);
        b.setAttribute('aria-selected', ix === -1 ? 'true' : 'false');
        return;
      }
      var el = atual.el;
      aplicarSimples(el, op);
      fecharFolha();
    });
    folhaLista.addEventListener('keydown', function (e) {
      var ops = $$('.folha-op', folhaLista), i = ops.indexOf(document.activeElement);
      if (e.key === 'ArrowDown' && i < ops.length - 1) { e.preventDefault(); ops[i + 1].focus(); }
      if (e.key === 'ArrowUp') { e.preventDefault(); if (i > 0) ops[i - 1].focus(); else if (!$('.folha-busca').hidden) folhaFiltro.focus(); }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !folha.hidden) fecharFolha();
    });
  }

  $$('[data-seletor]').forEach(function (el) {
    var b = $('button', el);
    b.addEventListener('click', function () { abrirSeletor(el); });
    var dep = el.getAttribute('data-depende');
    if (dep && !valorCampo(el.closest('form'), dep)) b.disabled = true;
    if (el.hasAttribute('data-multiplo')) {
      var iniciais = $$('[data-chip-inicial]', el).map(function (s) { return { v: s.getAttribute('data-chip-inicial'), t: s.textContent }; });
      $$('[data-chip-inicial]', el).forEach(function (s) { s.remove(); });
      desenharChips(el, iniciais);
    }
  });

  /* Campo "Outro": aparece quando a opção especial é escolhida */
  $$('[data-seletor][data-outro]').forEach(function (el) {
    var inp = $('input[type="hidden"]', el);
    var form = el.closest('form');
    var campo = form && $('[data-campo-outro]', form);
    if (!campo) return;
    function ver() {
      campo.hidden = inp.value !== 'outro';
      var t = $('input', campo);
      if (!campo.hidden && t && !t.value) setTimeout(function () { t.focus(); }, 50);
    }
    inp.addEventListener('change', ver);
    campo.hidden = inp.value !== 'outro';
  });

  /* ========== Mostrar e esconder por escolha ========== */
  $$('[data-se]').forEach(function (alvo) {
    var regra = alvo.getAttribute('data-se').split('=');
    var form = alvo.closest('form') || document;
    var inputs = $$('[name="' + regra[0] + '"]', form);
    function ver() {
      var v = '';
      inputs.forEach(function (i) { if ((i.type === 'radio' || i.type === 'checkbox') ? i.checked : true) v = i.type === 'checkbox' ? (i.checked ? i.value : '') : i.value; });
      var valores = regra[1].split('|');
      var neg = valores[0].charAt(0) === '!';
      var ok = neg ? valores[0].slice(1) !== v : valores.indexOf(v) !== -1;
      alvo.hidden = !ok;
    }
    inputs.forEach(function (i) { i.addEventListener('change', ver); });
    ver();
  });

  /* ========== Máscara de telefone ========== */
  function mascararTelefone(v) {
    var d = v.replace(/\D/g, '').slice(0, 11);
    if (d.length <= 2) return d.length ? '(' + d : '';
    if (d.length <= 6) return '(' + d.slice(0, 2) + ') ' + d.slice(2);
    if (d.length <= 10) return '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
    return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
  }
  $$('[data-mascara="telefone"]').forEach(function (i) {
    i.value = mascararTelefone(i.value);
    i.addEventListener('input', function () {
      var fim = i.selectionStart === i.value.length;
      i.value = mascararTelefone(i.value);
      if (fim) i.setSelectionRange(i.value.length, i.value.length);
    });
  });
  $$('[data-instagram]').forEach(function (i) {
    i.addEventListener('blur', function () {
      var v = i.value.trim(), m = v.match(/instagram\.com\/([^\/?#\s]+)/i);
      if (m) v = m[1];
      i.value = v.replace(/^@+/, '').replace(/\/+$/, '');
    });
  });

  /* ========== Mostrar senha ========== */
  $$('[data-ver-senha]').forEach(function (b) {
    b.addEventListener('click', function () {
      var i = b.parentNode.querySelector('input');
      var ver = i.type === 'password';
      i.type = ver ? 'text' : 'password';
      b.innerHTML = icone(ver ? 'olho-fechado' : 'olho');
      b.setAttribute('aria-label', ver ? 'Esconder senha' : 'Mostrar senha');
    });
  });

  /* ========== Contador de caracteres ========== */
  $$('[data-contador]').forEach(function (t) {
    var max = parseInt(t.getAttribute('data-contador'), 10);
    var c = document.createElement('div');
    c.className = 'contador';
    t.insertAdjacentElement('afterend', c);
    function at() {
      var n = t.value.length;
      c.textContent = n + ' de ' + max;
      c.classList.toggle('passou', n > max);
    }
    t.addEventListener('input', at);
    at();
  });

  /* ========== Foto ========== */
  $$('[data-foto]').forEach(function (inp) {
    var box = inp.closest('.foto-envio');
    var prev = $('[data-foto-preview]', box);
    var remover = $('[data-foto-remover]', box);
    var flag = $('input[name="remover_foto"]', box);
    inp.addEventListener('change', function () {
      var f = inp.files && inp.files[0];
      if (!f) return;
      if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { toast('Envie uma imagem JPG, PNG ou WEBP.', 'erro'); inp.value = ''; return; }
      if (f.size > 8 * 1024 * 1024) { toast('A foto pode ter no máximo 8 MB.', 'erro'); inp.value = ''; return; }
      var r = new FileReader();
      r.onload = function () {
        prev.classList.remove('avatar-vazio');
        prev.innerHTML = '<img src="' + r.result + '" alt="">';
        if (remover) remover.hidden = false;
        if (flag) flag.value = '';
      };
      r.readAsDataURL(f);
    });
    if (remover) remover.addEventListener('click', function () {
      inp.value = '';
      prev.classList.add('avatar-vazio');
      prev.innerHTML = icone('camera');
      remover.hidden = true;
      if (flag) flag.value = '1';
    });
  });

  /* ========== Diálogos ========== */
  function abrirDialogo(d, origem) {
    if (origem && origem.hasAttribute('data-campos')) {
      try {
        var campos = JSON.parse(origem.getAttribute('data-campos'));
        Object.keys(campos).forEach(function (k) {
          $$('[name="' + k + '"]', d).forEach(function (i) { i.value = campos[k]; });
          $$('[data-texto="' + k + '"]', d).forEach(function (i) { i.textContent = campos[k]; });
        });
      } catch (e) {}
    }
    d.hidden = false;
    document.documentElement.style.overflow = 'hidden';
    var f = $('input:not([type="hidden"]), textarea', d) || $('.botao', d);
    if (f) setTimeout(function () { f.focus(); }, 60);
  }
  function fecharDialogo(d) {
    d.hidden = true;
    document.documentElement.style.overflow = '';
  }
  $$('[data-abrir]').forEach(function (b) {
    b.addEventListener('click', function (e) {
      e.preventDefault();
      var d = $(b.getAttribute('data-abrir'));
      if (d) abrirDialogo(d, b);
    });
  });
  $$('.dialogo').forEach(function (d) {
    $$('[data-fechar]', d).forEach(function (b) { b.addEventListener('click', function () { fecharDialogo(d); }); });
    d.addEventListener('keydown', function (e) { if (e.key === 'Escape') fecharDialogo(d); });
    if (d.hasAttribute('data-abrir-agora')) abrirDialogo(d);
  });

  /* ========== Compartilhar ========== */
  $$('[data-compartilhar]').forEach(function (b) {
    b.addEventListener('click', function () {
      var dados = { title: b.getAttribute('data-titulo') || document.title, text: b.getAttribute('data-texto') || '', url: b.getAttribute('data-compartilhar') };
      if (navigator.share) {
        navigator.share(dados).catch(function () {});
        return;
      }
      var texto = (dados.text ? dados.text + ' ' : '') + dados.url;
      if (navigator.clipboard) navigator.clipboard.writeText(texto).then(function () { toast('Link copiado. É só colar onde quiser.'); });
      else toast(dados.url);
    });
  });
  $$('[data-copiar]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (navigator.clipboard) navigator.clipboard.writeText(b.getAttribute('data-copiar')).then(function () { toast('Copiado.'); });
    });
  });

  /* ========== Envio de formulários ========== */
  $$('form').forEach(function (f) {
    f.setAttribute('novalidate', '');
    f.addEventListener('submit', function (e) {
      var ok = true;
      $$('[data-obrigatorio]', f).forEach(function (el) {
        if (el.closest('[hidden]')) return;
        var campo = el.closest('.campo') || el;
        var vazio = el.type === 'checkbox' ? !el.checked : !String(el.value || '').trim();
        campo.classList.toggle('tem-erro', vazio);
        if (vazio) ok = false;
      });
      if (!ok) {
        e.preventDefault();
        var p = $('.tem-erro', f);
        if (p) p.scrollIntoView({ behavior: 'smooth', block: 'center' });
        toast('Preencha os campos destacados.', 'erro');
        return;
      }
      if (f.getAttribute('method') && f.getAttribute('method').toLowerCase() === 'get') return;
      if (f._enviando) { e.preventDefault(); return; }
      f._enviando = true;
      var b = e.submitter || $('button[type="submit"]', f);
      if (b) b.classList.add('carregando');
    });
  });
  $$('.tem-erro').forEach(function (c, i) { if (i === 0) setTimeout(function () { c.scrollIntoView({ block: 'center' }); }, 100); });
  $$('.campo').forEach(function (c) {
    c.addEventListener('input', function () { c.classList.remove('tem-erro'); var er = $('.campo-erro', c); if (er) er.remove(); });
  });

  /* ========== Passo numérico (admin) ========== */
  $$('[data-passo]').forEach(function (box) {
    var i = $('input', box);
    $$('button', box).forEach(function (b) {
      b.addEventListener('click', function () {
        var v = (parseInt(i.value, 10) || 0) + parseInt(b.getAttribute('data-delta'), 10);
        i.value = Math.max(parseInt(i.min || '0', 10), Math.min(parseInt(i.max || '99', 10), v));
      });
    });
  });

  /* ========== App instalável ========== */
  if ('serviceWorker' in navigator && location.protocol === 'https:') {
    window.addEventListener('load', function () { navigator.serviceWorker.register(BASE + '/sw.js').catch(function () {}); });
  }
})();

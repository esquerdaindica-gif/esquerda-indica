// Service worker: guarda os arquivos visuais para o app abrir rápido e mostra aviso quando estiver sem internet.
const VERSAO = 'ei-v1';
const ARQUIVOS = ['offline', 'assets/css/app.css', 'assets/js/app.js', 'assets/img/icone.svg', 'assets/img/icone-192.png'];

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(VERSAO).then((c) => c.addAll(ARQUIVOS)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(caches.keys().then((ks) => Promise.all(ks.filter((k) => k !== VERSAO).map((k) => caches.delete(k)))).then(() => self.clients.claim()));
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return;

  // Páginas: sempre da rede (dados pessoais não ficam em cache); sem internet, mostra a tela offline
  if (req.mode === 'navigate') {
    e.respondWith(fetch(req).catch(() => caches.match('offline')));
    return;
  }
  // Arquivos visuais: cache primeiro, atualizando em segundo plano
  if (url.pathname.includes('/assets/')) {
    e.respondWith(caches.open(VERSAO).then((c) => c.match(req).then((r) => {
      const rede = fetch(req).then((res) => { if (res.ok) c.put(req, res.clone()); return res; }).catch(() => r);
      return r || rede;
    })));
  }
});

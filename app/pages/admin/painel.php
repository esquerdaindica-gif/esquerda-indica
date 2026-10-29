<?php
$eu = exigir_admin();

$cont = contadores();
$ind = one("SELECT
    SUM(status = 'bloqueado') AS bloqueados,
    SUM(criado_em > NOW() - INTERVAL 7 DAY) AS novos7,
    SUM(edicoes_usadas >= edicoes_limite) AS sem_alteracoes
  FROM usuarios WHERE papel = 'usuario'");
$pendentes = (int)val("SELECT COUNT(*) FROM solicitacoes WHERE status = 'pendente'");
$excluidos = (int)val("SELECT COUNT(*) FROM historico WHERE acao IN ('exclusao_usuario','exclusao_admin')");
$ultimos = all('SELECT * FROM historico ORDER BY id DESC LIMIT 8');
$vagasNoAr = (int)val("SELECT COUNT(*) FROM vagas v JOIN usuarios u ON u.id = v.usuario_id WHERE " . VAGA_VISIVEL);
$empresas = (int)val("SELECT COUNT(*) FROM usuarios WHERE papel = 'usuario' AND cnpj IS NOT NULL");

render('admin/painel', [
    'titulo' => 'Administração',
    'aba' => 'admin',
    'cont' => $cont,
    'ind' => array_map('intval', $ind),
    'pendentes' => $pendentes,
    'excluidos' => $excluidos,
    'ultimos' => $ultimos,
    'vagasNoAr' => $vagasNoAr,
    'empresas' => $empresas,
    'eu' => $eu,
]);

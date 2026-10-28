<?php
// Lista todas as cidades de um estado (usada nos formulários de perfil)
$estado = (int)input('estado', 0);
if (!$estado) json_out([]);
header('Cache-Control: public, max-age=86400');
json_out(array_map(
    fn($c) => ['v' => (string)$c['id'], 't' => $c['nome']],
    all('SELECT id, nome FROM cidades WHERE estado_id = ? ORDER BY nome', [$estado])
));

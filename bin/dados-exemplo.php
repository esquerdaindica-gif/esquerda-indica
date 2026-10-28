<?php
/**
 * Cria perfis fictícios para desenvolvimento e testes de interface.
 * Só roda com 'debug' => true no app/config.php.
 *   php bin/dados-exemplo.php
 * Todos os usuários criados usam a senha: senha1234
 */
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/app/bootstrap.php';
if (!config('debug')) exit("Ative 'debug' => true no app/config.php para usar dados de exemplo.\n");
if (val("SELECT COUNT(*) FROM usuarios WHERE email LIKE '%@exemplo.com'")) exit("Os dados de exemplo já existem.\n");
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
mt_srand(7);
$pessoas = [
 ['Ateliê Raízes', 'Marina Lopes', 'Costura e ajustes de roupas', 'Juiz de Fora', 'MG', 'Ajustes, consertos e peças sob medida com tecidos de reaproveitamento. Atendo com hora marcada no Centro.'],
 ['Elétrica Solidária', 'Carlos Henrique Souza', 'Eletricista', 'Juiz de Fora', 'MG', 'Instalações residenciais e comerciais, quadros de distribuição e laudos. 15 anos de experiência.'],
 ['Dra. Ana Beatriz Ramos', 'Ana Beatriz Ramos', 'Advocacia', 'Belo Horizonte', 'MG', 'Advocacia trabalhista e previdenciária. Primeira consulta online sem custo para membros do grupo.'],
 ['Pedro Antunes', 'Pedro Antunes', 'Desenvolvimento de sites e sistemas', 'São Paulo', 'SP', 'Sites rápidos, lojas virtuais e sistemas sob medida para pequenos negócios e coletivos.'],
 ['Horta Viva Orgânicos', 'Joana Prado', 'Produtos orgânicos e agroecológicos', 'Viçosa', 'MG', 'Cestas semanais de hortaliças agroecológicas da agricultura familiar, entrega às sextas.'],
 ['Clínica Mente Aberta', 'Rafael Nogueira', 'Psicologia', 'Rio de Janeiro', 'RJ', 'Psicoterapia para adultos e casais, abordagem humanista. Valores sociais disponíveis.'],
 ['Lucas Ferreira Contabilidade', 'Lucas Ferreira', 'Contabilidade', 'Juiz de Fora', 'MG', 'Contabilidade para MEI, pequenas empresas e associações. Abertura de CNPJ e imposto de renda.'],
 ['Sabor de Casa Marmitas', 'Dona Lúcia Matos', 'Marmitas e refeições', 'Juiz de Fora', 'MG', 'Marmitas caseiras com opção vegetariana, entrega no Centro e São Mateus de segunda a sexta.'],
 ['Fotografia Olhar Coletivo', 'Bianca Teixeira', 'Fotografia', 'Belo Horizonte', 'MG', 'Ensaios, eventos e cobertura de atos e movimentos sociais. Edição inclusa.'],
 ['Oficina Feito à Laser', 'Rodrigo Alves', 'Corte e gravação a laser', 'Juiz de Fora', 'MG', 'Corte e gravação a laser em MDF, acrílico e couro. Brindes, sinalização e peças personalizadas.'],
 ['Mestre João Reformas', 'João Batista', 'Pedreiro(a)', 'Ubá', 'MG', 'Reformas, alvenaria, reboco e acabamento. Orçamento sem compromisso.'],
 ['Profa. Helena Duarte', 'Helena Duarte', 'Aulas particulares e reforço escolar', 'Curitiba', 'PR', 'Reforço em matemática e física para ensino fundamental e médio, presencial ou online.'],
 ['Cuidar Bem', 'Sandra Oliveira', 'Cuidador(a) de idosos', 'Juiz de Fora', 'MG', 'Cuidadora com curso técnico em enfermagem. Plantões diurnos e noturnos.'],
 ['Bike Livre Oficina', 'Thiago Mendes', 'Bicicletaria', 'Porto Alegre', 'RS', 'Manutenção de bicicletas, revisão completa e montagem. Oficina comunitária aos sábados.'],
 ['Marcos Vieira', 'Marcos Vieira', 'Mecânica automotiva', 'Recife', 'PE', 'Mecânica geral, injeção eletrônica e suspensão. Atendimento honesto e transparente.'],
 ['Estúdio Trama', 'Larissa Campos', 'Design gráfico', 'São Paulo', 'SP', 'Identidade visual, cartazes e materiais para campanhas e coletivos culturais.'],
 ['Doce Resistência', 'Patrícia Lima', 'Confeitaria e doces', 'Juiz de Fora', 'MG', 'Bolos, doces finos e encomendas para festas. Opções sem lactose.'],
 ['Rede Verde Paisagismo', 'André Salles', 'Paisagismo e jardinagem', 'Niterói', 'RJ', 'Projetos de jardins, hortas urbanas e manutenção mensal de áreas verdes.'],
 ['Fernanda Rocha', 'Fernanda Rocha', 'Nutrição', 'Salvador', 'BA', 'Nutrição clínica e comportamental, atendimento online para todo o Brasil.'],
 ['Coletivo Som Popular', 'Diego Martins', 'Música ao vivo', 'Belo Horizonte', 'MG', 'Samba, MPB e forró para eventos, festas e atos culturais.'],
];
$outros = [['Consultoria em economia popular', 'Juiz de Fora', 'MG'], ['Consultoria em economia popular', 'Belo Horizonte', 'MG'], ['Restauração de móveis antigos', 'Juiz de Fora', 'MG']];
$cores = [[214,17,31],[30,30,30],[245,180,40],[30,120,80],[40,80,180],[160,40,120]];
$i = 0;
foreach ($pessoas as $k => [$emp, $resp, $serv, $cid, $uf]) {
    $desc = $pessoas[$k][5];
    $sid = val('SELECT id FROM servicos WHERE nome = ?', [$serv]);
    $c = one('SELECT c.id, c.estado_id FROM cidades c JOIN estados e ON e.id = c.estado_id WHERE c.nome = ? AND e.uf = ?', [$cid, $uf]);
    $tel = sprintf('%02d9%08d', $uf === 'MG' ? 32 : 11, mt_rand(10000000, 99999999));
    $foto = null;
    if ($k % 3 !== 2) {
        $im = imagecreatetruecolor(400, 400);
        [$r,$g,$b] = $cores[$k % count($cores)];
        imagefill($im, 0, 0, imagecolorallocate($im, $r, $g, $b));
        $w = imagecolorallocate($im, 255, 255, 255);
        imagefilledellipse($im, 200 + mt_rand(-60,60), 200 + mt_rand(-60,60), 220, 220, imagecolorallocatealpha($im, 255,255,255, 90));
        imagefilledrectangle($im, 110, 250, 290, 270, $w);
        $foto = bin2hex(random_bytes(12)) . '.webp';
        imagewebp($im, UPLOAD_DIR . '/' . $foto, 80);
    }
    $atend = ['cidade','cidade','estado','cidades','brasil'][$k % 5];
    q("INSERT INTO usuarios (email, senha_hash, responsavel, empresa, telefone, whatsapp, instagram, descricao, servico_id, estado_id, cidade_id, atendimento, atende_online, foto, lgpd_aceite_em, criado_em, edicoes_usadas)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(), NOW() - INTERVAL ? HOUR, ?)", [
        'teste' . $k . '@exemplo.com', password_hash('senha1234', PASSWORD_DEFAULT), $resp, $emp, $tel, $tel,
        $k % 4 === 3 ? null : preg_replace('/[^a-z]/', '', strtolower(iconv('UTF-8','ASCII//TRANSLIT',$emp))),
        $desc, $sid, $c['estado_id'], $c['id'], $atend, $k % 4 === 0 ? 1 : 0, $foto, (count($pessoas) - $k) * 20, $k === 0 ? 2 : 0
    ]);
    $uid = (int)db()->lastInsertId();
    if ($atend === 'cidades') {
        foreach (all('SELECT id FROM cidades WHERE estado_id = ? AND id <> ? ORDER BY RAND() LIMIT 3', [$c['estado_id'], $c['id']]) as $x)
            q('INSERT INTO usuario_cidades VALUES (?,?)', [$uid, $x['id']]);
    }
    if (!$sid) echo "sem servico: $serv\n";
}
foreach ($outros as $k => [$txt, $cid, $uf]) {
    $c = one('SELECT c.id, c.estado_id FROM cidades c JOIN estados e ON e.id = c.estado_id WHERE c.nome = ? AND e.uf = ?', [$cid, $uf]);
    q("INSERT INTO usuarios (email, senha_hash, responsavel, empresa, telefone, whatsapp, descricao, servico_outro, estado_id, cidade_id, lgpd_aceite_em, criado_em)
       VALUES (?,?,?,?,?,?,?,?,?,?,NOW(), NOW() - INTERVAL 30 DAY)", ['outro'.$k.'@exemplo.com', password_hash('senha1234', PASSWORD_DEFAULT), 'Pessoa Outro '.$k, 'Iniciativa '.($k+1), '32988887777', '32988887777', 'Trabalho com '.$txt.' há muitos anos, atendendo coletivos.', $txt, $c['estado_id'], $c['id']]);
}
q("UPDATE usuarios SET status='bloqueado', bloqueio_motivo='Perfil duplicado' WHERE email='teste14@exemplo.com'");
q("INSERT INTO solicitacoes (usuario_id, motivo) SELECT id, 'Troquei de número de WhatsApp e preciso atualizar meu Instagram, que mudou de nome.' FROM usuarios WHERE email='teste0@exemplo.com'");
echo "Perfis de exemplo criados. Senha de todos: senha1234\n";

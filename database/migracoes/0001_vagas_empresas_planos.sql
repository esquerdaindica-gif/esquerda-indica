# Versão 0.4.0: dados da empresa, vagas de emprego, configurações gerais e base para planos pagos.

ALTER TABLE usuarios
  ADD COLUMN cnpj CHAR(14) NULL AFTER empresa,
  ADD COLUMN razao_social VARCHAR(150) NULL AFTER cnpj,
  ADD COLUMN vagas_modo ENUM('auto','liberado','bloqueado') NOT NULL DEFAULT 'auto' AFTER foto,
  ADD COLUMN vagas_limite TINYINT UNSIGNED NULL AFTER vagas_modo,
  ADD COLUMN ultima_edicao_em DATETIME NULL AFTER edicoes_limite,
  ADD UNIQUE KEY uq_usuario_cnpj (cnpj);

# Configurações que a administração altera pelo painel
CREATE TABLE IF NOT EXISTS configuracoes (
  chave VARCHAR(60) NOT NULL PRIMARY KEY,
  valor VARCHAR(255) NOT NULL,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes (chave, valor) VALUES
  ('edicao_modo', 'intervalo'),
  ('edicao_intervalo_horas', '24'),
  ('edicoes_padrao', '2'),
  ('vagas_ativas', '1'),
  ('vagas_limite_padrao', '3'),
  ('vagas_validade_dias', '30');

# Planos: hoje só existe o gratuito. Os pagos entram quando houver meio de pagamento.
CREATE TABLE IF NOT EXISTS planos (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(40) NOT NULL,
  nome VARCHAR(80) NOT NULL,
  descricao VARCHAR(255) NULL,
  preco_centavos INT UNSIGNED NOT NULL DEFAULT 0,
  periodo ENUM('mensal','trimestral','anual','unico') NOT NULL DEFAULT 'mensal',
  limite_vagas TINYINT UNSIGNED NOT NULL DEFAULT 3,
  publico TINYINT(1) NOT NULL DEFAULT 1,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  ordem SMALLINT NOT NULL DEFAULT 0,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_plano_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO planos (codigo, nome, descricao, preco_centavos, periodo, limite_vagas, ordem)
VALUES ('gratuito', 'Gratuito', 'Plano padrão de toda empresa com CNPJ cadastrado.', 0, 'mensal', 3, 0);

# Assinatura de um plano por uma conta. Sem assinatura ativa, vale o limite padrão.
CREATE TABLE IF NOT EXISTS assinaturas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  plano_id SMALLINT UNSIGNED NOT NULL,
  status ENUM('pendente','ativa','cancelada','expirada') NOT NULL DEFAULT 'pendente',
  origem ENUM('admin','pagamento') NOT NULL DEFAULT 'admin',
  inicio_em DATETIME NULL,
  fim_em DATETIME NULL,
  renovacao_automatica TINYINT(1) NOT NULL DEFAULT 0,
  gateway VARCHAR(30) NULL,
  gateway_ref VARCHAR(120) NULL,
  observacao VARCHAR(255) NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  KEY idx_assinatura_usuario (usuario_id, status, fim_em),
  KEY idx_assinatura_gateway (gateway, gateway_ref),
  CONSTRAINT fk_assinatura_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_assinatura_plano FOREIGN KEY (plano_id) REFERENCES planos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

# Pagamentos de assinaturas. Usuário sem chave estrangeira para o registro sobreviver à exclusão da conta.
CREATE TABLE IF NOT EXISTS pagamentos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  assinatura_id INT UNSIGNED NULL,
  usuario_id INT UNSIGNED NULL,
  valor_centavos INT UNSIGNED NOT NULL,
  metodo ENUM('pix','cartao','boleto','manual') NOT NULL DEFAULT 'pix',
  status ENUM('pendente','pago','falhou','estornado','cancelado') NOT NULL DEFAULT 'pendente',
  gateway VARCHAR(30) NULL,
  gateway_ref VARCHAR(120) NULL,
  dados LONGTEXT NULL,
  pago_em DATETIME NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pagamento_assinatura (assinatura_id),
  KEY idx_pagamento_usuario (usuario_id, criado_em),
  UNIQUE KEY uq_pagamento_gateway (gateway, gateway_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vagas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  titulo VARCHAR(100) NOT NULL,
  contrato ENUM('clt','pj','estagio','temporario','freelancer','aprendiz') NOT NULL,
  modalidade ENUM('presencial','hibrido','remoto') NOT NULL,
  estado_id TINYINT UNSIGNED NOT NULL,
  cidade_id INT UNSIGNED NOT NULL,
  quantidade TINYINT UNSIGNED NOT NULL DEFAULT 1,
  salario_tipo ENUM('combinar','valor','faixa') NOT NULL DEFAULT 'combinar',
  salario_min INT UNSIGNED NULL,
  salario_max INT UNSIGNED NULL,
  descricao TEXT NOT NULL,
  requisitos TEXT NULL,
  beneficios TEXT NULL,
  contato_tipo ENUM('whatsapp','email','link') NOT NULL,
  contato_valor VARCHAR(255) NOT NULL,
  status ENUM('ativa','pausada','encerrada','removida') NOT NULL DEFAULT 'ativa',
  removida_motivo VARCHAR(255) NULL,
  publicada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expira_em DATETIME NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  KEY idx_vaga_lista (status, expira_em, publicada_em),
  KEY idx_vaga_local (estado_id, cidade_id),
  KEY idx_vaga_usuario (usuario_id, status),
  CONSTRAINT fk_vaga_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_vaga_estado FOREIGN KEY (estado_id) REFERENCES estados(id),
  CONSTRAINT fk_vaga_cidade FOREIGN KEY (cidade_id) REFERENCES cidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

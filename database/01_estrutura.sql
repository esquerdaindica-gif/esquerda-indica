# Profissionais Progressistas: estrutura do banco (MySQL 8 / MariaDB 10.6+)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS estados (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  uf CHAR(2) NOT NULL UNIQUE,
  nome VARCHAR(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cidades (
  id INT UNSIGNED NOT NULL PRIMARY KEY,
  estado_id TINYINT UNSIGNED NOT NULL,
  nome VARCHAR(80) NOT NULL,
  KEY idx_cidade_estado (estado_id, nome),
  CONSTRAINT fk_cidade_estado FOREIGN KEY (estado_id) REFERENCES estados(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servicos (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  grupo VARCHAR(60) NOT NULL,
  nome VARCHAR(80) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_servico_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  papel ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
  status ENUM('ativo','bloqueado') NOT NULL DEFAULT 'ativo',
  bloqueio_motivo VARCHAR(255) NULL,
  responsavel VARCHAR(120) NOT NULL,
  empresa VARCHAR(120) NULL,
  telefone VARCHAR(11) NULL,
  whatsapp VARCHAR(11) NULL,
  instagram VARCHAR(30) NULL,
  descricao VARCHAR(400) NULL,
  servico_id SMALLINT UNSIGNED NULL,
  servico_outro VARCHAR(80) NULL,
  estado_id TINYINT UNSIGNED NULL,
  cidade_id INT UNSIGNED NULL,
  atendimento ENUM('cidade','cidades','estado','brasil') NOT NULL DEFAULT 'cidade',
  atende_online TINYINT(1) NOT NULL DEFAULT 0,
  foto VARCHAR(80) NULL,
  edicoes_usadas TINYINT UNSIGNED NOT NULL DEFAULT 0,
  edicoes_limite TINYINT UNSIGNED NOT NULL DEFAULT 2,
  lgpd_aceite_em DATETIME NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  ultimo_login_em DATETIME NULL,
  UNIQUE KEY uq_usuario_email (email),
  KEY idx_usuario_busca (papel, status, estado_id, cidade_id),
  KEY idx_usuario_servico (servico_id),
  KEY idx_usuario_criado (criado_em),
  CONSTRAINT fk_usuario_servico FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE SET NULL,
  CONSTRAINT fk_usuario_estado FOREIGN KEY (estado_id) REFERENCES estados(id),
  CONSTRAINT fk_usuario_cidade FOREIGN KEY (cidade_id) REFERENCES cidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

# Cidades extras atendidas (quando atendimento = 'cidades')
CREATE TABLE IF NOT EXISTS usuario_cidades (
  usuario_id INT UNSIGNED NOT NULL,
  cidade_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (usuario_id, cidade_id),
  KEY idx_uc_cidade (cidade_id),
  CONSTRAINT fk_uc_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_uc_cidade FOREIGN KEY (cidade_id) REFERENCES cidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitacoes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  motivo VARCHAR(500) NOT NULL,
  status ENUM('pendente','aprovada','recusada') NOT NULL DEFAULT 'pendente',
  resposta VARCHAR(500) NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolvido_em DATETIME NULL,
  resolvido_por INT UNSIGNED NULL,
  KEY idx_solic_status (status, criado_em),
  CONSTRAINT fk_solic_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

# Histórico permanente: não tem chave estrangeira para sobreviver à exclusão do usuário
CREATE TABLE IF NOT EXISTS historico (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NULL,
  usuario_label VARCHAR(255) NULL,
  autor_id INT UNSIGNED NULL,
  autor_tipo ENUM('usuario','admin','sistema') NOT NULL DEFAULT 'sistema',
  acao VARCHAR(40) NOT NULL,
  resumo VARCHAR(255) NOT NULL,
  dados LONGTEXT NULL,
  ip VARCHAR(45) NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_hist_usuario (usuario_id, criado_em),
  KEY idx_hist_acao (acao, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS senha_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expira_em DATETIME NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  KEY idx_token (token_hash),
  CONSTRAINT fk_token_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tentativas_login (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  email VARCHAR(190) NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_tent (ip, criado_em),
  KEY idx_tent_email (email, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

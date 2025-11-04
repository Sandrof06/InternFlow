# InternFlow

DROP DATABASE IF EXISTS sistema_gestao_estagios;
CREATE DATABASE sistema_gestao_estagios
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;
USE sistema_gestao_estagios;

-- ==========================================================
-- 1. USUÁRIOS (com hash seguro de senha)
-- ==========================================================
CREATE TABLE usuarios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(120) NOT NULL UNIQUE,
  senha_hash    VARCHAR(255) NOT NULL, -- Armazenar com PASSWORD_HASH (Argon2)
  tipo          ENUM('aluno','professor','admin') NOT NULL,
  ativo         TINYINT(1) DEFAULT 1,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_tipo (tipo)
) COMMENT 'Usuários do sistema (alunos, professores, admin)';

-- ==========================================================
-- 2. ADMIN
-- ==========================================================
CREATE TABLE admin (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario    INT NOT NULL UNIQUE,
  nome          VARCHAR(100) NOT NULL,
  telefone      VARCHAR(20),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Dados específicos do administrador';

-- ==========================================================
-- 3. CURSOS
-- ==========================================================
CREATE TABLE cursos (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  designacao    VARCHAR(100) NOT NULL,
  departamento  VARCHAR(100),
  ativo         TINYINT(1) DEFAULT 1,
  UNIQUE KEY uq_designacao (designacao),
  INDEX idx_departamento (departamento)
) COMMENT 'Cursos oferecidos pela instituição';

-- ==========================================================
-- 4. PROFESSORES
-- ==========================================================
CREATE TABLE professores (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario    INT NOT NULL UNIQUE,
  nome          VARCHAR(100) NOT NULL,
  disciplina    VARCHAR(100),
  telefone      VARCHAR(20),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Dados específicos do professor';

-- ==========================================================
-- 5. ALUNOS
-- ==========================================================
CREATE TABLE alunos (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario    INT NOT NULL UNIQUE,
  nr_processo   INT NOT NULL UNIQUE,
  id_curso      INT,
  ano_ingresso  YEAR DEFAULT (YEAR(CURDATE())),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (id_curso) REFERENCES cursos(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_nr_processo CHECK (nr_processo > 0)
) COMMENT 'Dados específicos do aluno';

-- ==========================================================
-- 6. ESTÁGIOS
-- ==========================================================
CREATE TABLE estagios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_aluno      INT NOT NULL,
  local         VARCHAR(150) NOT NULL,
  data_inicio   DATE NOT NULL,
  data_fim      DATE,
  status        ENUM('planejado','em_andamento','concluido','cancelado') 
                  DEFAULT 'planejado',
  horas_totais  INT DEFAULT 0,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_aluno) REFERENCES alunos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_datas CHECK (data_fim IS NULL OR data_fim >= data_inicio),
  INDEX idx_status (status),
  INDEX idx_periodo (data_inicio, data_fim)
) COMMENT 'Registro de estágios dos alunos';

-- ==========================================================
-- 7. SUPERVISÃO DE ESTÁGIO (N:N Professor ↔ Estágio)
-- ==========================================================
CREATE TABLE estagio_supervisao (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_estagio    INT NOT NULL,
  id_professor  INT NOT NULL,
  data_atribuicao DATE DEFAULT (CURDATE()),
  PRIMARY KEY (id_estagio, id_professor),
  FOREIGN KEY (id_estagio) REFERENCES estagios(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (id_professor) REFERENCES professores(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Professores supervisores por estágio (muitos-para-muitos)';

-- ==========================================================
-- 8. RELATÓRIOS
-- ==========================================================
CREATE TABLE relatorios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_estagio    INT NOT NULL,
  titulo        VARCHAR(150) NOT NULL,
  tipo          ENUM('parcial','final','anexo') DEFAULT 'parcial',
  entrega       DATE NOT NULL,
  caminho_arquivo VARCHAR(255),
  nota          DECIMAL(4,2) CHECK (nota BETWEEN 0 AND 20),
  observacoes   TEXT,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_estagio) REFERENCES estagios(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_entrega (entrega)
) COMMENT 'Relatórios entregues no estágio';

-- ==========================================================
-- 9. CONVERSAS (Chat entre aluno e professor)
-- ==========================================================
CREATE TABLE conversas (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_aluno      INT NOT NULL,
  id_professor  INT, -- pode ser NULL se for mensagem geral
  mensagem      TEXT NOT NULL,
  data_envio    DATETIME DEFAULT CURRENT_TIMESTAMP,
  lido          TINYINT(1) DEFAULT 0,
  FOREIGN KEY (id_aluno) REFERENCES alunos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (id_professor) REFERENCES professores(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_data (data_envio DESC)
) COMMENT 'Mensagens entre aluno e professor';

-- ==========================================================
-- 10. FEEDBACKS
-- ==========================================================
CREATE TABLE feedbacks (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_professor  INT NOT NULL,
  id_aluno      INT NOT NULL,
  mensagem      TEXT,
  data          DATE DEFAULT (CURDATE()),
  FOREIGN KEY (id_professor) REFERENCES professores(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (id_aluno) REFERENCES alunos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_data (data)
) COMMENT 'Feedback do professor ao aluno';

-- ==========================================================
-- 11. ÍNDICES ADICIONAIS PARA PERFORMANCE
-- ==========================================================
CREATE INDEX idx_alunos_curso ON alunos(id_curso);
CREATE INDEX idx_estagios_aluno ON estagios(id_aluno);
CREATE FULLTEXT INDEX ft_conversas_mensagem ON conversas(mensagem);

-- ==========================================================
-
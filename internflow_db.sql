-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 18-Dez-2025 às 03:45
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `internflow_db`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `estagios`
--

CREATE TABLE `estagios` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `orientador_id` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `curso` varchar(100) NOT NULL,
  `area` varchar(100) NOT NULL,
  `estado` enum('pendente','aceite','nao_apto','concluido') DEFAULT 'pendente',
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `estagios`
--

INSERT INTO `estagios` (`id`, `aluno_id`, `orientador_id`, `titulo`, `empresa`, `curso`, `area`, `estado`, `data_inicio`, `data_fim`, `criado_em`) VALUES
(1, 3, 2, 'Estágio em Desenvolvimento Web Full-stack', 'Tech Solutions', 'Engenharia de Software', 'Full-stack', 'aceite', NULL, NULL, '2025-12-17 22:36:44'),
(2, 4, 1, 'Estágio em Obras Públicas', 'Mota Engil', 'Engenharia Civil', 'Construção Civil', 'aceite', NULL, NULL, '2025-12-17 22:36:44'),
(3, 5, 1, 'Estágio em IA', 'SoftDev', 'Ciência da Computação', 'Desenvolvimento de Software', 'pendente', NULL, NULL, '2025-12-17 22:36:44');

-- --------------------------------------------------------

--
-- Estrutura da tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `id` int(11) NOT NULL,
  `remetente_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `conteudo` text NOT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `enviada_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `mensagens`
--

INSERT INTO `mensagens` (`id`, `remetente_id`, `destinatario_id`, `conteudo`, `lida`, `enviada_em`) VALUES
(1, 3, 1, 'Olá Professor Constantino, recebi o feedback do meu relatório.', 0, '2025-12-17 22:36:44'),
(2, 1, 3, 'Que bom, Guilherme! Fico feliz em ajudar.', 1, '2025-12-17 22:36:44');

-- --------------------------------------------------------

--
-- Estrutura da tabela `relatorios`
--

CREATE TABLE `relatorios` (
  `id` int(11) NOT NULL,
  `estagio_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `conteudo` text NOT NULL,
  `feedback` text DEFAULT NULL,
  `classificacao` int(11) DEFAULT NULL,
  `submetido_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `avaliado_em` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `relatorios`
--

INSERT INTO `relatorios` (`id`, `estagio_id`, `titulo`, `conteudo`, `feedback`, `classificacao`, `submetido_em`, `avaliado_em`) VALUES
(1, 1, 'Relatório sobre Programação: Fundamentos', 'A programação é a arte e a ciência de instruir computadores...', 'Excelente trabalho, Guilherme! A análise está muito clara.', 5, '2025-12-17 22:36:44', '2025-12-17 22:36:44');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_utilizador` enum('aluno','professor','admin') NOT NULL DEFAULT 'aluno',
  `avatar_url` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id`, `nome`, `email`, `senha`, `tipo_utilizador`, `avatar_url`, `criado_em`) VALUES
(1, 'Professor Constantino', 'prof@esg.ipsantarem.pt', '123456', 'professor', NULL, '2025-12-17 22:36:44'),
(2, 'Prof. Carla Mendes', 'carla@esg.ipsantarem.pt', '123456', 'professor', NULL, '2025-12-17 22:36:44'),
(3, 'Guilherme S.', 'guilherme@aluno.ipsantarem.pt', '123456', 'aluno', NULL, '2025-12-17 22:36:44'),
(4, 'Maria Silva', 'maria@aluno.ipsantarem.pt', '123456', 'aluno', NULL, '2025-12-17 22:36:44'),
(5, 'João Pereira', 'joao@aluno.ipsantarem.pt', '123456', 'aluno', NULL, '2025-12-17 22:36:44'),
(7, 'Administrador', 'admin@internflow.com', '123456', 'admin', NULL, '2025-12-17 22:52:33'),
(8, 'joao aurelio', 'bado@esg.pt', '123456', 'professor', NULL, '2025-12-17 22:53:37');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `estagios`
--
ALTER TABLE `estagios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aluno_id` (`aluno_id`),
  ADD KEY `orientador_id` (`orientador_id`);

--
-- Índices para tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remetente_id` (`remetente_id`),
  ADD KEY `destinatario_id` (`destinatario_id`);

--
-- Índices para tabela `relatorios`
--
ALTER TABLE `relatorios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estagio_id` (`estagio_id`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `estagios`
--
ALTER TABLE `estagios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `relatorios`
--
ALTER TABLE `relatorios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `estagios`
--
ALTER TABLE `estagios`
  ADD CONSTRAINT `estagios_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estagios_ibfk_2` FOREIGN KEY (`orientador_id`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD CONSTRAINT `mensagens_ibfk_1` FOREIGN KEY (`remetente_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensagens_ibfk_2` FOREIGN KEY (`destinatario_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `relatorios`
--
ALTER TABLE `relatorios`
  ADD CONSTRAINT `relatorios_ibfk_1` FOREIGN KEY (`estagio_id`) REFERENCES `estagios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

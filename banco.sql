-- =============================================
-- SISTEMA DE AGENDAMENTO - Banco de Dados MySQL
-- =============================================

CREATE DATABASE IF NOT EXISTS agendamento_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agendamento_db;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('usuario', 'admin') DEFAULT 'usuario',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Recursos
CREATE TABLE IF NOT EXISTS recursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    disponivel TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Agendamentos
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    recurso_id INT NOT NULL,
    data DATE NOT NULL,
    horario TIME NOT NULL,
    status ENUM('ATIVO', 'CANCELADO') DEFAULT 'ATIVO',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (recurso_id) REFERENCES recursos(id)
);

-- =============================================
-- Dados iniciais de exemplo
-- =============================================

-- Admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Maria Admin', 'admin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('João Silva',  'joao@email.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario');
-- Senha padrão dos dois: "password"

-- Recursos de exemplo
INSERT INTO recursos (nome, tipo) VALUES
('Sala de Estudo A', 'Sala'),
('Sala de Reunião B', 'Sala'),
('Computador 01', 'Computador'),
('Computador 02', 'Computador'),
('Projetor Principal', 'Equipamento');

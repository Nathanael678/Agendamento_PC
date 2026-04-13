<?php
// =============================================
// CONFIGURAÇÃO DO BANCO DE DADOS
// =============================================
// Altere as credenciais abaixo conforme seu ambiente

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // seu usuário MySQL
define('DB_PASS', '');           // sua senha MySQL
define('DB_NAME', 'agendamento_db');

function conectar() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['erro' => 'Falha na conexão com o banco de dados.']);
        exit();
    }

    return $conn;
}

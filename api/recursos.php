<?php
// =============================================
// API - RECURSOS
// GET  /api/recursos.php          → lista todos
// POST /api/recursos.php          → cria (admin)
// DELETE /api/recursos.php?id=X   → remove (admin)
// =============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once 'config.php';
require_once 'auth.php';

exigirLogin();
$conn = conectar();

// --- GET: listar recursos ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $resultado = $conn->query("SELECT * FROM recursos WHERE disponivel = 1 ORDER BY tipo, nome");
    $recursos = [];
    while ($r = $resultado->fetch_assoc()) {
        $recursos[] = $r;
    }
    echo json_encode($recursos);

// --- POST: criar recurso (admin) ---
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['erro' => 'Acesso restrito a administradores.']);
        exit();
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    $nome  = trim($dados['nome'] ?? '');
    $tipo  = trim($dados['tipo'] ?? '');

    if (empty($nome) || empty($tipo)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Nome e tipo são obrigatórios.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO recursos (nome, tipo) VALUES (?, ?)");
    $stmt->bind_param("ss", $nome, $tipo);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    echo json_encode(['sucesso' => true, 'id' => $id, 'mensagem' => 'Recurso criado com sucesso.']);

// --- DELETE: remover recurso (admin) ---
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['erro' => 'Acesso restrito a administradores.']);
        exit();
    }

    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID do recurso é obrigatório.']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE recursos SET disponivel = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['sucesso' => true, 'mensagem' => 'Recurso removido.']);
}

$conn->close();

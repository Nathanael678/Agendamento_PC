<?php
// =============================================
// API - AGENDAMENTOS
// GET    /api/agendamentos.php         → lista
// POST   /api/agendamentos.php         → cria
// DELETE /api/agendamentos.php?id=X    → cancela
// =============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once 'config.php';
require_once 'auth.php';

exigirLogin();
$usuario = usuarioLogado();
$conn = conectar();

// --- GET: listar agendamentos ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Admin vê todos; usuário comum vê só os seus
    if (isAdmin()) {
        $sql = "SELECT a.id, a.data, a.horario, a.status,
                       u.nome AS usuario_nome,
                       r.nome AS recurso_nome, r.tipo AS recurso_tipo
                FROM agendamentos a
                JOIN usuarios u ON a.usuario_id = u.id
                JOIN recursos  r ON a.recurso_id = r.id
                ORDER BY a.data DESC, a.horario DESC";
        $resultado = $conn->query($sql);
    } else {
        $sql = "SELECT a.id, a.data, a.horario, a.status,
                       u.nome AS usuario_nome,
                       r.nome AS recurso_nome, r.tipo AS recurso_tipo
                FROM agendamentos a
                JOIN usuarios u ON a.usuario_id = u.id
                JOIN recursos  r ON a.recurso_id = r.id
                WHERE a.usuario_id = ?
                ORDER BY a.data DESC, a.horario DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario['id']);
        $stmt->execute();
        $resultado = $stmt->get_result();
    }

    $agendamentos = [];
    while ($r = $resultado->fetch_assoc()) {
        $agendamentos[] = $r;
    }
    echo json_encode($agendamentos);

// --- POST: criar agendamento ---
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados      = json_decode(file_get_contents('php://input'), true);
    $recurso_id = intval($dados['recurso_id'] ?? 0);
    $data       = $dados['data'] ?? '';
    $horario    = $dados['horario'] ?? '';

    if (!$recurso_id || empty($data) || empty($horario)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Recurso, data e horário são obrigatórios.']);
        exit();
    }

    // Verificar disponibilidade
    $stmt = $conn->prepare(
        "SELECT id FROM agendamentos
         WHERE recurso_id = ? AND data = ? AND horario = ? AND status = 'ATIVO'"
    );
    $stmt->bind_param("iss", $recurso_id, $data, $horario);
    $stmt->execute();
    $conflito = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($conflito) {
        http_response_code(409);
        echo json_encode(['erro' => 'Horário indisponível! Já existe um agendamento para esse recurso nesse horário.']);
        exit();
    }

    // Criar agendamento
    $stmt = $conn->prepare(
        "INSERT INTO agendamentos (usuario_id, recurso_id, data, horario) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("iiss", $usuario['id'], $recurso_id, $data, $horario);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    echo json_encode(['sucesso' => true, 'id' => $id, 'mensagem' => 'Agendamento realizado com sucesso!']);

// --- DELETE: cancelar agendamento ---
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID do agendamento é obrigatório.']);
        exit();
    }

    // Usuário comum só pode cancelar o próprio
    if (isAdmin()) {
        $stmt = $conn->prepare("UPDATE agendamentos SET status = 'CANCELADO' WHERE id = ?");
        $stmt->bind_param("i", $id);
    } else {
        $stmt = $conn->prepare("UPDATE agendamentos SET status = 'CANCELADO' WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $id, $usuario['id']);
    }

    $stmt->execute();
    $afetados = $stmt->affected_rows;
    $stmt->close();

    if ($afetados === 0) {
        http_response_code(404);
        echo json_encode(['erro' => 'Agendamento não encontrado ou sem permissão.']);
    } else {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Agendamento cancelado.']);
    }
}

$conn->close();

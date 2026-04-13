<?php
// =============================================
// VERIFICAÇÃO DE AUTENTICAÇÃO
// =============================================

session_start();

function exigirLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['erro' => 'Não autorizado. Faça login.']);
        exit();
    }
}

function usuarioLogado() {
    return [
        'id'   => $_SESSION['usuario_id'] ?? null,
        'nome' => $_SESSION['usuario_nome'] ?? null,
        'tipo' => $_SESSION['usuario_tipo'] ?? null,
    ];
}

function isAdmin() {
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';
}

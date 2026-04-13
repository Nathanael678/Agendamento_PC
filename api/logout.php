<?php
// =============================================
// API - LOGOUT
// POST /api/logout.php
// =============================================

header('Content-Type: application/json');
require_once 'auth.php';

session_destroy();
echo json_encode(['sucesso' => true, 'mensagem' => 'Sessão encerrada.']);

<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? null;

    if (!$token) {
        echo json_encode(['error' => 'Token missing']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT userId FROM tokens WHERE token = ?');
        $stmt->execute([$token]);
        $userId = $stmt->fetch();

        if ($userId) {
            // Generování tokenu
            $stmt2 = $pdo->prepare('SELECT userName, mail FROM users WHERE id = ?');
            $stmt2->execute([$userId['userId']]);
            $userInfo = $stmt2->fetch();

            echo json_encode(['userName' => $userInfo['userName'], 'mail' => $userInfo['mail']]);

            http_response_code(200);
        } else {
            echo json_encode(['error' => 'Tokenn 401']);
            http_response_code(200);
        }
    } catch (\PDOException $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
}
?>
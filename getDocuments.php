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
            $stmt2 = $pdo->prepare('SELECT name, expiration, type FROM document WHERE userId = ?');
            $stmt2->execute([$userId['userId']]);
            $documents = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($documents);

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



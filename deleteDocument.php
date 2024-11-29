<?php 
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'] ?? null;
    $token = $data['token'] ?? null;

    if (!$token || !$name) {
        echo json_encode(['error' => 'Missing name or token']);
        http_response_code(400); // Chybný požadavek
        exit;
    }

    try {
        // Ověření tokenu
        $stmt = $pdo->prepare('SELECT userId FROM tokens WHERE token = ?');
        $stmt->execute([$token]);
        $userId = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userId) {
            // Pokus o smazání dokumentu
            $stmt2 = $pdo->prepare('
                DELETE FROM images WHERE documentId IN ( SELECT id FROM document WHERE userId = ? AND name = ? );
            ');
            $stmt2->execute([$userId['userId'], $name]);
            $stmt3 = $pdo->prepare('
                DELETE FROM document 
                WHERE userId = ? AND name = ?
            ');
            $stmt3->execute([$userId['userId'], $name]);

            if ($stmt3->rowCount() > 0) {
                echo json_encode(['message' => 'Document deleted successfully']);
                http_response_code(200);
            } else {
                echo json_encode(['error' => 'Document not found']);
                http_response_code(404);
            }
        } else {
            echo json_encode(['error' => 'Invalid token']);
            http_response_code(401);
        }
    } catch (\PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        http_response_code(500);
    }
}

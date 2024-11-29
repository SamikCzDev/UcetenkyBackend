<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'] ?? null;
    $token = $data['token'] ?? null;

    if (!$token || !$name) {
        echo json_encode(['error' => 'Missing name or token']);
        exit;
    }

    try {
        // Ověření tokenu
        $stmt = $pdo->prepare('SELECT userId FROM tokens WHERE token = ?');
        $stmt->execute([$token]);
        $userId = $stmt->fetch();

        if ($userId) {
            // Získání URL obrázků na základě jména
            $stmt2 = $pdo->prepare('SELECT images.image_path FROM `images` JOIN document ON images.documentId = document.id WHERE document.name = ? AND document.userId = ? ORDER BY images.id ASC LIMIT 2;');
            $stmt2->execute([$name, $userId['userId']]);
            $imageUrls = $stmt2->fetchAll();
            
            if ($imageUrls) {
                echo json_encode($imageUrls);
                http_response_code(200);
            } else {
                echo json_encode(['error' => 'Images not found']);
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

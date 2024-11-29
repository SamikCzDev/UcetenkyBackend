<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Zpracování JSON dat z požadavku
    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? null;
    $name = $data['name'] ?? null;
    $type = $data['type'] ?? null;
    $date = $data['date'] ?? null;

    if (!$token) {
        echo json_encode(['error' => 'Token missing']);
        exit;
    }

    try {
        // Kontrola platnosti tokenu a získání userId
        $stmt = $pdo->prepare('SELECT userId FROM tokens WHERE token = ?');
        $stmt->execute([$token]);
        $userId = $stmt->fetch();

        if ($userId) {
            // Vložení nového dokumentu do databáze
            $stmt2 = $pdo->prepare('INSERT INTO `document`(`userId`, `name`, `type`, `expiration`) VALUES (?, ?, ?, ?)');
            $stmt2->execute([$userId['userId'], $name, $type, $date]);

            // Získání ID vytvořeného dokumentu
            $documentId = $pdo->lastInsertId();

            // Odpověď s ID vytvořeného dokumentu
            echo json_encode([
                'success' => true,
                'documentId' => $documentId
            ]);
            http_response_code(200);
        } else {
            echo json_encode(['error' => 'Invalid token']);
            http_response_code(401);
        }
    } catch (\PDOException $e) {
        // Ošetření chyb při práci s databází
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        http_response_code(500);
    }
}
?>

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
            // Získání jednoho dokumentu na základě jména a uživatele
            $stmt2 = $pdo->prepare('
                SELECT document.name, document.type, document.expiration, document.createDate 
                FROM document 
                WHERE document.userId = ? AND document.name = ?
                LIMIT 1;
            ');
            $stmt2->execute([$userId['userId'], $name]);
            $document = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($document) {
                // Výpočet dní do expirace
                $currentDate = new DateTime();
                $expirationDate = new DateTime($document['expiration']);
                $interval = $currentDate->diff($expirationDate);

                if ($currentDate > $expirationDate) {
                    $daysToExpire = 'expirace vypršela';
                } else {
                    $daysToExpire = $interval->days; // Počet dní do expirace
                }

                // Připravení dat pro výstup
                $response = [
                    'documentName' => $document['name'],
                    'documentType' => $document['type'],
                    'expiration' => $document['expiration'],
                    'daysToExpire' => $daysToExpire,
                    'createdAt' => $document['createDate']
                ];

                echo json_encode($response);
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

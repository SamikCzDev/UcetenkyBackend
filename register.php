<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $userName = $data['userName'] ?? null;
    $mail = $data['mail'] ?? null;
    $password = $data['password'] ?? null;

    if (!$userName || !$mail || !$password) {
        echo json_encode(['error' => 'Všechna pole jsou povinná.']);
        exit;
    }

    try {
        // Kontrola, zda uživatelské jméno nebo e-mail již existují
        $stmt = $pdo->prepare('SELECT id FROM users WHERE userName = ? OR mail = ?');
        $stmt->execute([$userName, $mail]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            echo json_encode(['error' => 'Uživatel s tímto uživatelským jménem nebo e-mailem již existuje.']);
            exit;
        }

        // Hash hesla
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Vložení nového uživatele do databáze
        $stmt = $pdo->prepare('INSERT INTO users (userName, mail, hash) VALUES (?, ?, ?)');
        $stmt->execute([$userName, $mail, $hash]);

        echo json_encode(['success' => 'Uživatel byl úspěšně zaregistrován.']);
    } catch (\PDOException $e) {
        echo json_encode(['error' => 'Chyba při registraci: ' . $e->getMessage()]);
    }
}
?>

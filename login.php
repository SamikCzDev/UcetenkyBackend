<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $mail = $data['mail'] ?? null;
    $password = $data['password'] ?? null;

    if (!$mail || !$password) {
        echo json_encode(['error' => 'E-mail a heslo jsou povinné.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT id, hash FROM users WHERE mail = ?');
        $stmt->execute([$mail]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['hash'])) {
            // Generování tokenu
            $token = bin2hex(random_bytes(16));
            echo json_encode(['token' => $token, 'userId' => $user['id']]);
            $stmt2 = $pdo->prepare('INSERT INTO `tokens`(`userId`, `token`) VALUES (?,?)');
            $stmt2->execute([$user['id'],$token]);
        } else {
            echo json_encode(['error' => 'Nesprávný e-mail nebo heslo.']);
        }
    } catch (\PDOException $e) {
        echo json_encode(['error' => 'Chyba při přihlašování: ' . $e->getMessage()]);
    }
}
?>

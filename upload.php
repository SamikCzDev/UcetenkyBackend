<?php
header("Content-Type: application/json");

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES["image"])) {
    $token = $_GET['token'];
    $docId = $_GET['docId'];

    if (!$token) {
        echo json_encode(['error' => 'Token missing']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT userId FROM tokens WHERE token = ?');
        $stmt->execute([$token]);
        $userId = $stmt->fetch();

        $uploadDir = "uploads/"; // Adresář pro ukládání obrázků
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Vytvoří adresář, pokud neexistuje
        }

        $imageName = uniqid("img_", true) . "." . pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $uploadFile = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $uploadFile)) {
            $stmt2 = $pdo->prepare("INSERT INTO images (documentId,image_path) VALUES (?,?)");
            if ($stmt2->execute([$docId,$uploadFile])) {
                echo json_encode(["status" => "success", "message" => "Image uploaded successfully.", "path" => $uploadFile]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to save image path in database."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
        }


    } catch (\PDOException $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
}
?>

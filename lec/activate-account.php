<?php

$token = $_GET["token"] ?? '';

// Validasi jika token tidak diberikan
if (empty($token)) {
    die("Invalid token.");
}

// Hash token
$token_hash = hash("sha256", $token);

// Menghubungkan ke database menggunakan PDO
$pdo = require __DIR__ . "/config/database.php";

// Query untuk memeriksa apakah hash token ada
$sql = "SELECT * FROM users WHERE account_activation_hash = :token_hash";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':token_hash', $token_hash);
$stmt->execute();

// Mengambil hasil
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user === false) {
    die("Invalid or expired activation token.");
}

// Query untuk menghapus hash aktivasi akun
$sql = "UPDATE users SET account_activation_hash = NULL WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $user["id"]);
$stmt->execute();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Activated</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>

    <h1>Account Activated</h1>

    <p>Account activated successfully. You can now
       <a href="login.php">log in</a>.</p>

</body>
</html>

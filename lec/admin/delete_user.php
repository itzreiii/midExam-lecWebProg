<?php
// delete_user.php

// Inklusi koneksi database
require_once '../config/database.php'; // Pastikan ini sesuai dengan jalur ke file koneksi Anda

// Memastikan ada ID yang diberikan melalui URL
if (isset($_GET['id'])) {
    // Mengambil ID user dari URL dan mengamankan dari SQL Injection
    $userId = intval($_GET['id']);
    
    try {
        // Mengambil koneksi database
        $conn = $database->getConnection(); // Panggil getConnection untuk mendapatkan objek PDO
        
        // SQL untuk menghapus user berdasarkan ID
        $sql = "DELETE FROM users WHERE id = :id";
        
        // Menyiapkan statement untuk mencegah SQL Injection
        $stmt = $conn->prepare($sql);
        
        // Mengikat parameter ID ke statement
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        
        // Mengeksekusi statement
        // Kode untuk menghapus user
        if ($stmt->execute()) {
            // Jika berhasil, arahkan kembali ke halaman yang diinginkan
            header("Location: user-management.php?status=success");
            exit();
        } else {
            echo "Terjadi kesalahan saat menghapus user.";
        }

    } catch (PDOException $e) {
        echo "Kesalahan: " . $e->getMessage();
    }
} else {
    echo "ID user tidak ditemukan.";
}
?>


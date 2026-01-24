<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();
requireAdmin();
require_once __DIR__ . '/../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    try {
        $stmt->execute([$id]);
        redirect('index.php'); // Message could be passed via session
    } catch (PDOException $e) {
        die("Error deleting quiz: " . $e->getMessage());
    }
} else {
    redirect('index.php');
}
?>
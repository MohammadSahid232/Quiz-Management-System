<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Quiz System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">QuizMaster</a>
            <ul class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="random_quiz.php">Random Quiz</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="add.php">Add Quiz</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn-logout">Logout (
                            <?= htmlspecialchars($_SESSION['username']); ?>)
                        </a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <div class="main-content container">
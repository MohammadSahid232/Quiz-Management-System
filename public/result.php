<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$attempt_id = $_GET['attempt_id'] ?? null;
if (!$attempt_id)
    redirect('index.php');

$stmt = $pdo->prepare("
    SELECT r.*, q.title 
    FROM results r 
    JOIN quizzes q ON r.quiz_id = q.id 
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$attempt_id, getCurrentUserId()]);
$result = $stmt->fetch();

if (!$result) {
    echo "<h2>Result not found or access denied.</h2>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$percentage = ($result['score'] / $result['total_questions']) * 100;
$message = $percentage >= 50 ? "Great Job!" : "Better luck next time.";
$color = $percentage >= 50 ? "green" : "red";
?>

<div class="card" style="text-align: center; max-width: 600px; margin: 50px auto;">
    <h1>Quiz Result:
        <?= htmlspecialchars($result['title']) ?>
    </h1>

    <div style="font-size: 4rem; font-weight: bold; color: <?= $color ?>; margin: 20px 0;">
        <?= round($percentage) ?>%
    </div>

    <h3>You scored
        <?= $result['score'] ?> out of
        <?= $result['total_questions'] ?>
    </h3>
    <p>
        <?= $message ?>
    </p>

    <div style="margin-top: 30px;">
        <a href="index.php" class="btn btn-primary">Take Another Quiz</a>
        <a href="attempt.php?quiz_id=<?= $result['quiz_id'] ?>" class="btn btn-secondary">Retry This Quiz</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
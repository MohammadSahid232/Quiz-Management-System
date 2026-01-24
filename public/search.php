<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check valid call
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    exit;

// Init session if needed for role checks (already done in functions but this is AJAX)
if (session_status() === PHP_SESSION_NONE)
    session_start();
$isAdmin = isAdmin();

$query = $_POST['query'] ?? '';
$difficulty = $_POST['difficulty'] ?? '';

$sql = "SELECT * FROM quizzes WHERE 1=1";
$params = [];

if (!empty($query)) {
    $sql .= " AND (title LIKE ? OR subject LIKE ?)";
    $params[] = "%$query%";
    $params[] = "%$query%";
}

if (!empty($difficulty)) {
    $sql .= " AND difficulty = ?";
    $params[] = $difficulty;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$quizzes = $stmt->fetchAll();

if (empty($quizzes)) {
    echo "<p>No quizzes found.</p>";
    exit;
}

foreach ($quizzes as $quiz) {
    ?>
    <div class="card">
        <h3>
            <?= htmlspecialchars($quiz['title']) ?>
        </h3>
        <p style="color: var(--text-muted); margin-bottom: 1rem;">
            <?= htmlspecialchars($quiz['subject']) ?>
        </p>

        <div class="quiz-meta">
            <span class="badge badge-<?= $quiz['difficulty'] ?>">
                <?= $quiz['difficulty'] ?>
            </span>
            <span>
                <?= $quiz['duration'] ?> mins
            </span>
        </div>

        <a href="attempt.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-primary"
            style="width: 100%; text-align: center; margin-top: 10px;">Start Quiz</a>

        <?php if ($isAdmin): ?>
            <div style="margin-top: 10px; display: flex; gap: 5px;">
                <a href="edit.php?id=<?= $quiz['id'] ?>" class="btn btn-secondary" style="flex: 1; text-align: center;">Edit</a>
                <a href="delete.php?id=<?= $quiz['id'] ?>" class="btn btn-danger" style="flex: 1; text-align: center;"
                    onclick="return confirm('Delete this quiz?')">Delete</a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>
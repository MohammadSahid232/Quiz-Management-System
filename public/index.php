<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

// Fetch initial quizzes
$stmt = $pdo->query("SELECT * FROM quizzes ORDER BY created_at DESC");
$quizzes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h1>Available Quizzes</h1>

<div class="filters">
    <div class="form-group search-input" style="margin-bottom: 0;">
        <input type="text" id="search-quiz" class="form-control" placeholder="Search by title or subject...">
    </div>
    <div class="form-group" style="margin-bottom: 0;">
        <select id="filter-difficulty" class="form-control">
            <option value="">All Difficulties</option>
            <option value="Easy">Easy</option>
            <option value="Medium">Medium</option>
            <option value="Hard">Hard</option>
        </select>
    </div>
</div>

<div id="quiz-grid" class="quiz-grid">
    <?php foreach ($quizzes as $quiz): ?>
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

            <?php if (isAdmin()): ?>
                <div style="margin-top: 10px; display: flex; gap: 5px;">
                    <a href="edit.php?id=<?= $quiz['id'] ?>" class="btn btn-secondary"
                        style="flex: 1; text-align: center;">Edit</a>
                    <a href="delete.php?id=<?= $quiz['id'] ?>" class="btn btn-danger" style="flex: 1; text-align: center;"
                        onclick="return confirm('Delete this quiz?')">Delete</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (empty($quizzes)): ?>
        <p>No quizzes available.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
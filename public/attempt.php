<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$quiz_id = $_GET['quiz_id'] ?? null;
if (!$quiz_id)
    redirect('index.php');

// Fetch Quiz Info
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    echo "<h2>Quiz not found!</h2>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Fetch Questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Attempt Quiz:
        <?= htmlspecialchars($quiz['title']) ?>
    </h1>
    <div id="timer" data-duration="<?= $quiz['duration'] ?>">Loading Timer...</div>
</div>

<?php if (empty($questions)): ?>
    <div class="alert alert-error">This quiz has no questions yet. <a href="index.php">Go Back</a></div>
<?php else: ?>
    <form id="quiz-form" action="submit.php" method="POST">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="card">
                <p><strong>Q
                        <?= $index + 1 ?>:
                        <?= htmlspecialchars($q['question_text']) ?>
                    </strong></p>
                <div style="margin-top: 10px;">
                    <div class="form-check">
                        <label>
                            <input type="radio" name="answers[<?= $q['id'] ?>]" value="A">
                            <?= htmlspecialchars($q['option_a']) ?>
                        </label>
                    </div>
                    <div class="form-check">
                        <label>
                            <input type="radio" name="answers[<?= $q['id'] ?>]" value="B">
                            <?= htmlspecialchars($q['option_b']) ?>
                        </label>
                    </div>
                    <div class="form-check">
                        <label>
                            <input type="radio" name="answers[<?= $q['id'] ?>]" value="C">
                            <?= htmlspecialchars($q['option_c']) ?>
                        </label>
                    </div>
                    <div class="form-check">
                        <label>
                            <input type="radio" name="answers[<?= $q['id'] ?>]" value="D">
                            <?= htmlspecialchars($q['option_d']) ?>
                        </label>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.2rem;">Submit Answers</button>
    </form>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
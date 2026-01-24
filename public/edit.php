<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$quiz_id = $_GET['id'] ?? null;
if (!$quiz_id) {
    redirect('index.php');
}

// Handle Question Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_questions'])) {
    if (isset($_POST['questions']) && is_array($_POST['questions'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $pdo->beginTransaction();
            foreach ($_POST['questions'] as $q) {
                $q_text = sanitize($q['text']);
                $opt_a = sanitize($q['opt_a']);
                $opt_b = sanitize($q['opt_b']);
                $opt_c = sanitize($q['opt_c']);
                $opt_d = sanitize($q['opt_d']);
                $correct = $q['correct'];

                if (!empty($q_text) && !empty($opt_a)) { // Basic validation
                    $stmt->execute([$quiz_id, $q_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct]);
                }
            }
            $pdo->commit();
            redirect("edit.php?id=$quiz_id");
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error adding questions: " . $e->getMessage();
        }
    }
}

// Handle Question Deletion
if (isset($_GET['delete_q'])) {
    $q_id = $_GET['delete_q'];
    try {
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ? AND quiz_id = ?");
        $stmt->execute([$q_id, $quiz_id]);
        redirect("edit.php?id=$quiz_id");
    } catch (PDOException $e) {
        $error = "Error deleting question: " . $e->getMessage();
    }
}

// Fetch Quiz
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    redirect('index.php');
}

// Fetch Questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h1>Edit Quiz: <?= htmlspecialchars($quiz['title']) ?></h1>

<?php if (isset($error))
    echo "<div class='alert alert-danger'>$error</div>"; ?>

<div class="card">
    <h3>Existing Questions</h3>
    <?php if (empty($questions)): ?>
        <p>No questions added yet.</p>
    <?php else: ?>
        <ul style="list-style: none; padding: 0;">
            <?php foreach ($questions as $q): ?>
                <li
                    style="border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>Q: <?= $q['question_text'] ?></strong><br>
                        <small>Ans: <?= $q['correct_option'] ?></small>
                    </div>
                    <a href="edit.php?id=<?= $quiz_id ?>&delete_q=<?= $q['id'] ?>" class="btn btn-danger"
                        style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Delete question?')">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Add Questions</h3>
    <form method="POST" id="questionsForm">
        <input type="hidden" name="save_questions" value="1">

        <div id="questions-container">
            <!-- Initial Question Block -->
            <div class="question-block"
                style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                <h4 class="q-number">Question 1</h4>
                <div class="form-group">
                    <label>Question Text</label>
                    <textarea name="questions[0][text]" class="form-control" required></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group"><label>Option A</label><input type="text" name="questions[0][opt_a]"
                            class="form-control" required></div>
                    <div class="form-group"><label>Option B</label><input type="text" name="questions[0][opt_b]"
                            class="form-control" required></div>
                    <div class="form-group"><label>Option C</label><input type="text" name="questions[0][opt_c]"
                            class="form-control" required></div>
                    <div class="form-group"><label>Option D</label><input type="text" name="questions[0][opt_d]"
                            class="form-control" required></div>
                </div>
                <div class="form-group">
                    <label>Correct Option</label>
                    <select name="questions[0][correct]" class="form-control" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-secondary" onclick="addQuestion()" style="margin-right: 10px;">+ Add
            Another Question</button>
        <button type="submit" class="btn btn-primary">Save All Questions</button>
    </form>
</div>

<script>
    let questionCount = 1;

    function addQuestion() {
        questionCount++;
        const container = document.getElementById('questions-container');
        const index = questionCount - 1;

        const html = `
        <div class="question-block" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; position: relative;">
            <button type="button" class="btn btn-danger" onclick="this.parentElement.remove(); updateQuestionNumbers();" style="position: absolute; top: 10px; right: 10px; padding: 2px 8px; font-size: 0.8rem;">×</button>
            <h4 class="q-number">Question ${questionCount}</h4>
            <div class="form-group">
                <label>Question Text</label>
                <textarea name="questions[${index}][text]" class="form-control" required></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group"><label>Option A</label><input type="text" name="questions[${index}][opt_a]" class="form-control" required></div>
                <div class="form-group"><label>Option B</label><input type="text" name="questions[${index}][opt_b]" class="form-control" required></div>
                <div class="form-group"><label>Option C</label><input type="text" name="questions[${index}][opt_c]" class="form-control" required></div>
                <div class="form-group"><label>Option D</label><input type="text" name="questions[${index}][opt_d]" class="form-control" required></div>
            </div>
            <div class="form-group">
                <label>Correct Option</label>
                <select name="questions[${index}][correct]" class="form-control" required>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
        </div>
    `;
        container.insertAdjacentHTML('beforeend', html);
        updateQuestionNumbers();
    }

    function updateQuestionNumbers() {
        const blocks = document.querySelectorAll('.question-block');
        blocks.forEach((block, index) => {
            block.querySelector('.q-number').textContent = `Question ${index + 1}`;
        });
        questionCount = blocks.length;
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
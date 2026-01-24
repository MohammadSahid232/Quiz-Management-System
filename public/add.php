<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $subject = sanitize($_POST['subject']);
    $difficulty = sanitize($_POST['difficulty']);
    $duration = (int) $_POST['duration'];
    $creator_id = getCurrentUserId();

    $stmt = $pdo->prepare("INSERT INTO quizzes (title, subject, difficulty, duration, created_by) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$title, $subject, $difficulty, $duration, $creator_id]);
        $quiz_id = $pdo->lastInsertId();
        redirect("edit.php?id=$quiz_id"); // Redirect to edit to add questions
    } catch (PDOException $e) {
        $error = "Error adding quiz: " . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<h1>Create New Quiz</h1>
<?php if ($error)
    echo "<div class='alert alert-error'>$error</div>"; ?>

<form method="POST" class="card">
    <div class="form-group">
        <label>Quiz Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Subject</label>
        <input type="text" name="subject" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Difficulty</label>
        <select name="difficulty" class="form-control">
            <option value="Easy">Easy</option>
            <option value="Medium">Medium</option>
            <option value="Hard">Hard</option>
        </select>
    </div>
    <div class="form-group">
        <label>Duration (Minutes)</label>
        <input type="number" name="duration" class="form-control" require min="1">
    </div>
    <button type="submit" class="btn btn-primary">Create & Add Questions</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
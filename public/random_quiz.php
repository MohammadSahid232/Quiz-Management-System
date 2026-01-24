<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$categories = [
    9 => 'General Knowledge',
    17 => 'Science & Nature',
    18 => 'Computers',
    21 => 'Sports',
    23 => 'History',
    27 => 'Animals'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = (int) $_POST['category'];
    $difficulty = $_POST['difficulty'];
    $amount = 10; // Default amount

    // 1. Fetch from OpenTriviaDB
    $url = "https://opentdb.com/api.php?amount=$amount&category=$category&difficulty=$difficulty&type=multiple";

    $json = file_get_contents($url);
    $data = json_decode($json, true);

    if (isset($data['results']) && count($data['results']) > 0) {
        try {
            // 2. Create Temporal Quiz in DB
            $pdo->beginTransaction();

            $title = "Random Quiz: " . $categories[$category];
            $subject = $categories[$category];
            $duration = 10; // 1 min per question
            $user_id = getCurrentUserId();

            // Insert Quiz
            $stmt = $pdo->prepare("INSERT INTO quizzes (title, subject, difficulty, duration, created_by, is_api_generated) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$title, $subject, ucfirst($difficulty), $duration, $user_id]);
            $quiz_id = $pdo->lastInsertId();

            // Insert Questions
            $q_stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($data['results'] as $item) {
                // Decode HTML entities from API
                $question_text = html_entity_decode($item['question'], ENT_QUOTES, 'UTF-8');
                $correct = html_entity_decode($item['correct_answer'], ENT_QUOTES, 'UTF-8');
                $incorrect = array_map(function ($a) {
                    return html_entity_decode($a, ENT_QUOTES, 'UTF-8');
                }, $item['incorrect_answers']);

                // Shuffle options
                $options = array_merge([$correct], $incorrect);
                shuffle($options);

                // Find content for A, B, C, D
                $opt_a = $options[0];
                $opt_b = $options[1];
                $opt_c = $options[2];
                $opt_d = $options[3];

                // Determine correct option letter
                $correct_char = '';
                if ($correct === $opt_a)
                    $correct_char = 'A';
                elseif ($correct === $opt_b)
                    $correct_char = 'B';
                elseif ($correct === $opt_c)
                    $correct_char = 'C';
                elseif ($correct === $opt_d)
                    $correct_char = 'D';

                $q_stmt->execute([$quiz_id, $question_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct_char]);
            }

            $pdo->commit();

            // Redirect to Attempt
            redirect("attempt.php?quiz_id=$quiz_id");

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to generate quiz: " . $e->getMessage();
        }
    } else {
        $error = "No questions found for this selection. Try different settings.";
    }
}
?>

<h1>Take a Random Quiz</h1>
<p>Generate a quiz instantly using questions from the Open Trivia Database.</p>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <?= $error ?>
    </div>
<?php endif; ?>

<form method="POST" class="card" style="max-width: 500px">
    <div class="form-group">
        <label>Category</label>
        <select name="category" class="form-control">
            <?php foreach ($categories as $id => $name): ?>
                <option value="<?= $id ?>">
                    <?= $name ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Difficulty</label>
        <select name="difficulty" class="form-control">
            <option value="easy">Easy</option>
            <option value="medium">Medium</option>
            <option value="hard">Hard</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Generate & Start</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$quiz_id = $_POST['quiz_id'];
$answers = $_POST['answers'] ?? [];
$user_id = getCurrentUserId();

// Calculate Score
$score = 0;
$total = 0;

// Fetch Correct Answers
$stmt = $pdo->prepare("SELECT id, correct_option FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // id => correct_option

$total = count($questions);

foreach ($questions as $q_id => $correct_opt) {
    if (isset($answers[$q_id]) && $answers[$q_id] === $correct_opt) {
        $score++;
    }
}

// Insert Attempt
try {
    $stmt = $pdo->prepare("INSERT INTO results (user_id, quiz_id, score, total_questions) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $quiz_id, $score, $total]);
    $attempt_id = $pdo->lastInsertId();

    echo json_encode(['status' => 'success', 'attempt_id' => $attempt_id]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
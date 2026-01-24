<?php
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Analytics Queries
$total_users_stmt = $pdo->query("SELECT count(*) FROM users WHERE role = 'student'");
$total_users = $total_users_stmt->fetchColumn();

$total_quizzes_stmt = $pdo->query("SELECT count(*) FROM quizzes");
$total_quizzes = $total_quizzes_stmt->fetchColumn();

$total_attempts_stmt = $pdo->query("SELECT count(*) FROM results");
$total_attempts = $total_attempts_stmt->fetchColumn();

$avg_score_stmt = $pdo->query("SELECT AVG((score/total_questions)*100) FROM results");
$avg_score = number_format($avg_score_stmt->fetchColumn(), 1);

// Recent Attempts
$recent_stmt = $pdo->query("
    SELECT r.*, u.username, q.title 
    FROM results r 
    JOIN users u ON r.user_id = u.id 
    JOIN quizzes q ON r.quiz_id = q.id 
    ORDER BY r.attempted_at DESC 
    LIMIT 10
");
$recent_attempts = $recent_stmt->fetchAll();
?>

<h1>Admin Dashboard</h1>

<div
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div class="card" style="text-align: center;">
        <h3>Total Students</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--primary-color);">
            <?= $total_users ?>
        </p>
    </div>
    <div class="card" style="text-align: center;">
        <h3>Total Quizzes</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-color);">
            <?= $total_quizzes ?>
        </p>
    </div>
    <div class="card" style="text-align: center;">
        <h3>Total Attempts</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--danger-color);">
            <?= $total_attempts ?>
        </p>
    </div>
    <div class="card" style="text-align: center;">
        <h3>Avg Score</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #F59E0B;">
            <?= $avg_score ?>%
        </p>
    </div>
</div>

<div class="card">
    <h3>Recent Attempts</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background: var(--background-color); text-align: left;">
                <th style="padding: 10px; border-bottom: 2px solid var(--border-color);">User</th>
                <th style="padding: 10px; border-bottom: 2px solid var(--border-color);">Quiz</th>
                <th style="padding: 10px; border-bottom: 2px solid var(--border-color);">Score</th>
                <th style="padding: 10px; border-bottom: 2px solid var(--border-color);">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_attempts as $attempt): ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <?= htmlspecialchars($attempt['username']) ?>
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <?= htmlspecialchars($attempt['title']) ?>
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <?php
                        $pct = ($attempt['score'] / $attempt['total_questions']) * 100;
                        $color = $pct >= 50 ? 'green' : 'red';
                        echo "<span style='color: $color; font-weight: bold;'>$attempt[score]/$attempt[total_questions] (" . round($pct) . "%)</span>";
                        ?>
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <?= htmlspecialchars($attempt['attempted_at']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_attempts)): ?>
                <tr>
                    <td colspan="4" style="padding: 20px; text-align: center;">No attempts yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
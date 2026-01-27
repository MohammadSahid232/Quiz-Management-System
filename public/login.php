<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        redirect('index.php');
    } else {
        $error = "Invalid credentials";
    }
}
?>

<div class="card" style="max-width: 400px; margin: 40px auto;">
    <h2>Login</h2>
    <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success">Registration successful! Please login.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%">Login</button>
    </form>
    <p style="margin-top: 10px; text-align: center;">Don't have an account? <a href="register.php"
            style="color: var(--primary-color)">Register</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
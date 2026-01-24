<?php
require_once __DIR__ . '/../includes/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']); // In a real app, role selection might be restricted

    if (empty($username))
        $errors[] = "Username is required";
    if (empty($email))
        $errors[] = "Email is required";
    if (empty($password))
        $errors[] = "Password is required";

    // Check if any admin exists
    $adminCheckStmt = $pdo->query("SELECT count(*) FROM users WHERE role = 'admin'");
    $adminExists = $adminCheckStmt->fetchColumn() > 0;

    if ($role === 'admin' && $adminExists) {
        $errors[] = "An admin account already exists. Only student registration is allowed.";
    }

    if (empty($errors)) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username or Email already exists";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            try {
                $stmt->execute([$username, $email, $hashed_password, $role]);
                // Auto login or redirect
                redirect('login.php?registered=1');
            } catch (PDOException $e) {
                $errors[] = "Error registering user";
            }
        }
    }
}
// UI 
$adminCheckStmt = $pdo->query("SELECT count(*) FROM users WHERE role = 'admin'");
$adminExists = $adminCheckStmt->fetchColumn() > 0;
?>

<div class="card" style="max-width: 500px; margin: 40px auto;">
    <h2>Register</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error)
                echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="student">Student</option>
                <?php if (!$adminExists): ?>
                    <option value="admin">Admin</option>
                <?php endif; ?>
            </select>
            <?php if ($adminExists): ?>
                <small style="color: var(--text-muted); display: block; margin-top: 5px;">* Admin registration is closed
                    (Admin already exists).</small>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%">Register</button>
    </form>
    <p style="margin-top: 10px; text-align: center;">Already have an account? <a href="login.php"
            style="color: var(--primary-color)">Login</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<?php
require_once 'config.php';

if (isAuthenticated() && !isset($_GET['logout'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

if (isset($_GET['logout'])) {
    // Clear all session data
    $_SESSION = [];
    
    // Destroy the session
    if (session_id()) {
        session_destroy();
    }
    
    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Prevent caching and redirect
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MYLES Inventory Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login - MYLES Inventory Management</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Username:   admin</label>
            <input type="text" name="username" required>
            <label>Password:   admin123</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
<?php
require_once 'config.php';
requireAuth();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$csrf_token = generateCsrfToken();

$error = '';
$success = '';

// Fetch current username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$current_username = $user['username'];
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token_post = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf_token_post)) {
        $error = 'Invalid CSRF token.';
    } else {
        $new_username = trim($_POST['username'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate inputs
        if (empty($new_username)) {
            $error = 'Username cannot be empty.';
        } elseif ($new_username !== $current_username && strlen($new_username) < 3) {
            $error = 'Username must be at least 3 characters.';
        } elseif (!empty($new_password) && (strlen($new_password) < 6)) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New password and confirmation do not match.';
        } elseif (empty($current_password)) {
            $error = 'Current password is required to make changes.';
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!password_verify($current_password, $user['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                // Check if new username is taken
                if ($new_username !== $current_username) {
                    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                    $stmt->bind_param("si", $new_username, $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $error = 'Username is already taken.';
                        $stmt->close();
                    }
                }

                if (!$error) {
                    // Update username and/or password
                    if ($new_username !== $current_username && !empty($new_password)) {
                        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt->bind_param("ssi", $new_username, $hashed_password, $user_id);
                    } elseif ($new_username !== $current_username) {
                        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                        $stmt->bind_param("si", $new_username, $user_id);
                    } elseif (!empty($new_password)) {
                        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt->bind_param("si", $hashed_password, $user_id);
                    }

                    if (isset($stmt)) {
                        $success = $stmt->execute() ? 'Profile updated successfully.' : 'Failed to update profile.';
                        $stmt->close();
                        if ($success) {
                            $_SESSION['username'] = $new_username; // Update session
                        }
                    } else {
                        $success = 'No changes made.';
                    }
                }
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Inventory Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-container h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .profile-container label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .profile-container input[type="text"],
        .profile-container input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .profile-container button {
            background-color: #2c3e50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .profile-container button:hover {
            background-color: #34495e;
        }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
        .back-link {
            display: inline-block;
            margin-top: 10px;
            color: #2c3e50;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Update Profile</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <label>Current Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($current_username); ?>" required>
            <label>Current Password:</label>
            <input type="password" name="current_password" required>
            <label>New Password (leave blank to keep current):</label>
            <input type="password" name="new_password">
            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password">
            <button type="submit">Update Profile</button>
        </form>
        <a href="index.php" class="back-link">Back to Inventory</a>
    </div>
</body>
</html>
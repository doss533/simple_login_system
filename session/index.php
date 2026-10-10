
<?php
session_start();
require_once "db.php";

if (isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: " .
        ($_SESSION["role"] === "admin" ? "home2.php" : "home.php"));
    exit;
}

$error = "";
$success = "";
$page = $_GET["page"] ?? "login";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // =========================
    // REGISTRATION
    // =========================
    if ($action === "register") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirm = $_POST["confirm_password"] ?? "";
        $page = "register";

        if ($username === "" || $password === "" || $confirm === "") {
            $error = "All fields are required.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $error = "Username must be 3-50 characters using letters, numbers, or underscores.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $check = $conn->prepare(
                "SELECT id FROM users WHERE username = ? LIMIT 1"
            );
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Public registration always creates a regular user.
                $stmt = $conn->prepare(
                    "INSERT INTO users (username, password, role)
                     VALUES (?, ?, 'user')"
                );
                $stmt->bind_param("ss", $username, $hashedPassword);

                if ($stmt->execute()) {
                    $success = "Registration successful. You can now log in.";
                    $page = "login";
                } else {
                    $error = "Registration failed. Please try again.";
                }

                $stmt->close();
            }

            $check->close();
        }

    // =========================
    // LOGIN
    // =========================
    } elseif ($action === "login") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $page = "login";

        if ($username === "" || $password === "") {
            $error = "Please fill in all fields.";
        } else {
            $stmt = $conn->prepare(
                "SELECT id, username, password, role
                 FROM users
                 WHERE username = ?
                 LIMIT 1"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user["password"])) {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                header("Location: " .
                    ($user["role"] === "admin" ? "home2.php" : "home.php"));
                exit;
            } else {
                $error = "Invalid username or password.";
            }

            $stmt->close();
        }
    } else {
        $error = "Invalid request.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login and Registration</title>
</head>
<body>

<?php if ($error !== ""): ?>
    <p style="color:red;">
        <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
    </p>
<?php endif; ?>

<?php if ($success !== ""): ?>
    <p style="color:green;">
        <?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?>
    </p>
<?php endif; ?>

<?php if ($page === "register"): ?>

    <h2>Registration</h2>

    <form method="POST" action="index.php?page=register">
        <input type="hidden" name="action" value="register">

        <label>Username:</label><br>
        <input type="text" name="username"
               minlength="3" maxlength="50" required>
        <br><br>

        <label>Password (minimum 8 characters):</label><br>
        <input type="password" name="password"
               minlength="8" required>
        <br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password"
               minlength="8" required>
        <br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already registered? <a href="index.php">Login here</a></p>

<?php else: ?>

    <h2>Login</h2>

    <form method="POST" action="index.php">
        <input type="hidden" name="action" value="login">

        <label>Username:</label><br>
        <input type="text" name="username" maxlength="50" required>
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required>
        <br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account?
        <a href="index.php?page=register">Register here</a>
    </p>

<?php endif; ?>

</body>
</html>

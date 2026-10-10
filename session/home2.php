
<?php
session_start();

if (!isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

if (isset($_GET["logout"])) {
    $_SESSION = [];
    session_destroy();

    header("Location: index.php");
    exit;
}

$username = htmlspecialchars(
    $_SESSION["username"],
    ENT_QUOTES,
    "UTF-8"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrator Homepage</title>
</head>
<body>

<h1>Welcome, Admin <?= $username ?>!</h1>

<p>You are logged in as an administrator.</p>
<p>Role: admin</p>

<h3>Admin Dashboard</h3>
<p>You have access to administrator-only content.</p>

<a href="home2.php?logout=1">Logout</a>

</body>
</html>

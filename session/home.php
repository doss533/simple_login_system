
<?php
session_start();

if (!isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION["role"] !== "user") {
    header("Location: home2.php");
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
    <title>User Homepage</title>
</head>
<body>

<h1>Welcome, <?= $username ?>!</h1>

<p>You are logged in as a regular user.</p>
<p>Role: user</p>

<a href="home.php?logout=1">Logout</a>

</body>
</html>

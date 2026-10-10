<?php
session_start();
require_once "db.php";
require_once "functions.php";

require_login();

// Administrators have their own dashboard.
if ($_SESSION["role"] === "admin") {
    header("Location: admin.php");
    exit;
}

$username = $_SESSION["username"];
$role     = $_SESSION["role"];

// Look up extra account details (member since).
$stmt = $conn->prepare("SELECT created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$memberSince = $row ? date("M j, Y", strtotime($row["created_at"])) : "—";
$loginTime   = date("g:i A", $_SESSION["login_time"] ?? time());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &middot; NexusApp</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
    <div class="brand">
        <span class="mark"><?= icon("shield", "icon") ?></span>
        <span>NexusApp</span>
    </div>
    <div class="nav-user">
        <span class="chip chip-success"><?= e($role) ?></span>
        <span><?= e($username) ?></span>
        <span class="avatar"><?= e(initial($username)) ?></span>
        <a class="btn btn-ghost" href="logout.php"><?= icon("logout", "icon icon-sm") ?> Logout</a>
    </div>
</header>

<main class="container">

    <section class="hero">
        <div class="glow"></div>
        <h1>Hello, <?= e($username) ?> &#128075;</h1>
        <p>You are signed in as a regular user. Here is your activity overview.</p>
    </section>

    <div class="grid">
        <div class="stat">
            <div class="value"><?= e(ucfirst($role)) ?></div>
            <div class="label">Account role</div>
        </div>
        <div class="stat">
            <div class="value"><?= e($memberSince) ?></div>
            <div class="label">Member since</div>
        </div>
        <div class="stat">
            <div class="value"><?= e($loginTime) ?></div>
            <div class="label">Logged in at</div>
        </div>
    </div>

    <section class="panel">
        <div class="panel-head">
            <span><?= icon("user", "icon icon-sm") ?> Your account</span>
            <span class="chip chip-info">User</span>
        </div>
        <div class="panel-body flush">
            <table>
                <tr><th>Username</th><td><?= e($username) ?></td></tr>
                <tr><th>Role</th><td><span class="chip chip-success"><?= e($role) ?></span></td></tr>
                <tr><th>User ID</th><td class="mono">#<?= e($_SESSION["user_id"]) ?></td></tr>
            </table>
        </div>
    </section>

</main>
</body>
</html>

<?php
session_start();
require_once "db.php";
require_once "functions.php";

require_admin();

$username = $_SESSION["username"];

// Dashboard statistics.
$totals = $conn->query(
    "SELECT
        COUNT(*) AS total,
        SUM(role = 'admin') AS admins,
        SUM(role = 'user')  AS users
     FROM users"
)->fetch_assoc();

// Full user list.
$result = $conn->query(
    "SELECT id, username, role, created_at FROM users ORDER BY id ASC"
);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard &middot; NexusApp</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
    <div class="brand">
        <span class="mark"><?= icon("admin_panel", "icon") ?></span>
        <span>NexusApp</span>
    </div>
    <div class="nav-user">
        <span class="chip chip-admin">admin</span>
        <span><?= e($username) ?></span>
        <span class="avatar"><?= e(initial($username)) ?></span>
        <a class="btn btn-ghost" href="logout.php"><?= icon("logout", "icon icon-sm") ?> Logout</a>
    </div>
</header>

<main class="container">

    <section class="hero">
        <div class="glow"></div>
        <h1>Admin Dashboard &#9989;</h1>
        <p>Welcome back, <?= e($username) ?>. You have full access to the system.</p>
    </section>

    <div class="grid">
        <div class="stat">
            <div class="value"><?= e($totals["total"]) ?></div>
            <div class="label">Total accounts</div>
        </div>
        <div class="stat">
            <div class="value"><?= e($totals["admins"]) ?></div>
            <div class="label">Administrators</div>
        </div>
        <div class="stat">
            <div class="value"><?= e($totals["users"]) ?></div>
            <div class="label">Regular users</div>
        </div>
    </div>

    <section class="panel">
        <div class="panel-head">
            <span><?= icon("group", "icon icon-sm") ?> Registered users</span>
            <span class="chip chip-info"><?= e(count($users)) ?> records</span>
        </div>
        <div class="panel-body flush">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="mono">#<?= e($u["id"]) ?></td>
                        <td><?= e($u["username"]) ?></td>
                        <td>
                            <span class="chip <?= $u["role"] === "admin" ? "chip-admin" : "chip-success" ?>">
                                <?= e($u["role"]) ?>
                            </span>
                        </td>
                        <td class="mono"><?= e(date("M j, Y", strtotime($u["created_at"]))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>
</body>
</html>

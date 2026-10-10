<?php
session_start();
require_once "db.php";
require_once "functions.php";

// Already signed in? Go straight to the right dashboard.
if (is_logged_in()) {
    redirect_by_role($_SESSION["role"]);
}

$error   = "";
$success = "";
$page    = $_GET["page"] ?? "login";

// Re-fill values after a failed attempt.
$oldUsername = $_COOKIE["remember_username"] ?? "";
$oldRemember = $oldUsername !== "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // ---------------------------------------------------------
    //  REGISTER
    // ---------------------------------------------------------
    if ($action === "register") {
        $page        = "register";
        $username    = trim($_POST["username"] ?? "");
        $password    = $_POST["password"] ?? "";
        $confirm     = $_POST["confirm_password"] ?? "";
        $oldUsername = $username;

        if ($username === "" || $password === "" || $confirm === "") {
            $error = "All fields are required.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $error = "Username must be 3-50 characters (letters, numbers, underscores).";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "That username is already taken.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')"
                );
                $stmt->bind_param("ss", $username, $hash);
                $stmt->execute();
                $stmt->close();

                $success     = "Account created. You can sign in now.";
                $page        = "login";
                $oldUsername = "";
            }

            $check->close();
        }

    // ---------------------------------------------------------
    //  LOGIN
    // ---------------------------------------------------------
    } elseif ($action === "login") {
        $page        = "login";
        $username    = trim($_POST["username"] ?? "");
        $password    = $_POST["password"] ?? "";
        $oldUsername = $username;

        if ($username === "" || $password === "") {
            $error = "Please fill in all fields.";
        } else {
            $stmt = $conn->prepare(
                "SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user["password"])) {
                // Optional "remember me" -> keep the username on this device.
                if (!empty($_POST["remember"])) {
                    setcookie("remember_username", $user["username"], time() + 2592000, "/");
                } else {
                    setcookie("remember_username", "", time() - 3600, "/");
                }

                session_regenerate_id(true);
                $_SESSION["user_id"]    = $user["id"];
                $_SESSION["username"]   = $user["username"];
                $_SESSION["role"]       = $user["role"];
                $_SESSION["login_time"] = time();

                redirect_by_role($user["role"]);
            }

            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid request.";
    }
}

// Toast payload for the front-end.
$toastTitle = "";
$toastBody  = "";
$toastType  = "info";
if ($error !== "") {
    $toastTitle = "Authentication failed";
    $toastBody  = $error;
    $toastType  = "error";
} elseif ($success !== "") {
    $toastTitle = "Success";
    $toastBody  = $success;
    $toastType  = "success";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page === "register" ? "Create account" : "Sign in" ?> &middot; NexusApp</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Toast -->
<div class="toast" id="toast">
    <span class="dot"></span>
    <div>
        <div class="toast-title" id="toast-title">Notice</div>
        <div class="toast-body" id="toast-body"></div>
    </div>
</div>

<?php if ($toastBody !== ""): ?>
    <span hidden data-toast-message="<?= e($toastBody) ?>"
          data-toast-title="<?= e($toastTitle) ?>"
          data-toast-type="<?= e($toastType) ?>"></span>
<?php endif; ?>

<main class="auth">
    <div class="ambient ambient-a"></div>
    <div class="ambient ambient-b"></div>
    <div class="ambient ambient-c"></div>

    <div class="card-perspective">
        <div class="tilt-card" id="tilt-card">
            <div class="glare" id="card-glare"></div>

            <div class="flip-inner <?= $page === "register" ? "is-flipped" : "" ?>">

                <!-- ============ FRONT : SIGN IN ============ -->
                <div class="flip-face flip-front">

                    <div class="auth-head">
                        <div class="logo-badge"><?= icon("lock") ?></div>
                        <div class="chip-row">
                            <span class="chip chip-info">v2.4 Engine</span>
                            <span class="chip chip-neutral" style="border:none;text-transform:none;">
                                <span class="dot dot-pulse"></span> DB&nbsp;Synced
                            </span>
                        </div>
                        <h1 class="auth-title">Welcome back to NexusApp</h1>
                        <p class="auth-sub"></p>
                    </div>

                    <?php if ($error !== "" && $page === "login"): ?>
                        <div class="alert alert-error"><?= icon("error", "icon icon-sm") ?><?= e($error) ?></div>
                    <?php elseif ($success !== "" && $page === "login"): ?>
                        <div class="alert alert-success"><?= icon("check_circle", "icon icon-sm") ?><?= e($success) ?></div>
                    <?php endif; ?>

                    <form class="form" method="POST" action="index.php" data-animate-submit>
                        <input type="hidden" name="action" value="login">

                        <div class="field">
                            <label for="login-username">Username</label>
                            <div class="input-wrap">
                                <?= icon("user") ?>
                                <input id="login-username" type="text" name="username"
                                       value="<?= e($oldUsername) ?>" placeholder="admin"
                                       maxlength="50" required>
                            </div>
                            <span class="field-error">Please enter your username.</span>
                        </div>

                        <div class="field">
                            <div class="field-head">
                                <label for="login-password">Password</label>
                                <a href="#" data-forgot style="font-size:.8125rem;">Forgot password?</a>
                            </div>
                            <div class="input-wrap">
                                <?= icon("lock") ?>
                                <input id="login-password" class="has-toggle" type="password"
                                       name="password" placeholder="••••••••••••" required>
                                <button class="icon-toggle" type="button"
                                        data-toggle-password="login-password"
                                        aria-label="Toggle password visibility"><?= icon("eye") ?></button>
                            </div>
                            <span class="field-error">Please enter your password.</span>
                        </div>

                        <div class="row-between">
                            <label class="check">
                                <input type="checkbox" name="remember" value="1"
                                       <?= $oldRemember ? "checked" : "" ?>>
                                Remember me for 30 days
                            </label>
                            <span class="chip chip-neutral" style="border:none;color:var(--muted);">
                                <?= icon("verified_user", "icon icon-sm") ?>
                            </span>
                        </div>

                        <button class="btn btn-primary" type="submit" style="margin-top:.5rem;">
                            <span data-label>Sign In to Workspace</span>
                            <?= icon("arrow_forward", "icon icon-arrow") ?>
                        </button>
                    </form>

                    <div class="divider"><span>Or continue with</span></div>

                    <div class="grid" style="grid-template-columns:1fr 1fr; margin:0;">
                        <button class="btn btn-ghost" type="button" data-oauth="GitHub"
                                style="background:var(--surface-container);">
                            GitHub
                        </button>
                        <button class="btn btn-ghost" type="button" data-oauth="Google"
                                style="background:var(--surface-container);">
                            Google
                        </button>
                    </div>

                    <p class="swap" style="margin-top:1rem;">
                        Don't have an account?
                        <button type="button" data-flip="true">Sign up</button>
                    </p>

                    <div class="footline">
                        <span class="l"><?= icon("lock", "icon icon-sm") ?> End-to-end encrypted</span>
                        <span class="r"><span class="dot dot-pulse"></span> DB Store: active</span>
                    </div>
                </div>

                <!-- ============ BACK : SIGN UP ============ -->
                <div class="flip-face flip-back">

                    <div class="auth-head">
                        <div class="logo-badge" style="color:var(--secondary);"><?= icon("person_add") ?></div>
                        <div class="chip-row">
                            <span class="chip chip-primary">Create Identity</span>
                        </div>
                        <h2 class="auth-title">Create your account</h2>
                        <p class="auth-sub"></p>
                    </div>

                    <?php if ($error !== "" && $page === "register"): ?>
                        <div class="alert alert-error" style="margin-top:1rem;">
                            <?= icon("error", "icon icon-sm") ?><?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form class="form" method="POST" action="index.php?page=register"
                          data-animate-submit style="margin-top:1rem;">
                        <input type="hidden" name="action" value="register">

                        <div class="field">
                            <label for="reg-username">Username</label>
                            <div class="input-wrap">
                                <?= icon("person_add") ?>
                                <input id="reg-username" type="text" name="username"
                                       value="<?= $page === "register" ? e($oldUsername) : "" ?>"
                                       placeholder="new_operator" minlength="3" maxlength="50" required>
                            </div>
                            <span class="field-error">3-50 letters, numbers or underscores.</span>
                        </div>

                        <div class="field">
                            <label for="reg-password">Create password</label>
                            <div class="input-wrap">
                                <?= icon("key") ?>
                                <input id="reg-password" class="has-toggle" type="password"
                                       name="password" placeholder="At least 8 characters"
                                       minlength="8" required>
                                <button class="icon-toggle" type="button"
                                        data-toggle-password="reg-password"
                                        aria-label="Toggle password visibility"><?= icon("eye") ?></button>
                            </div>
                            <span class="field-error">Minimum 8 characters.</span>
                        </div>

                        <div class="field">
                            <label for="reg-confirm">Confirm password</label>
                            <div class="input-wrap">
                                <?= icon("key") ?>
                                <input id="reg-confirm" class="has-toggle" type="password"
                                       name="confirm_password" placeholder="Repeat password"
                                       minlength="8" required>
                                <button class="icon-toggle" type="button"
                                        data-toggle-password="reg-confirm"
                                        aria-label="Toggle password visibility"><?= icon("eye") ?></button>
                            </div>
                            <span class="field-error">Passwords must match.</span>
                        </div>

                        <button class="btn btn-secondary" type="submit" style="margin-top:.5rem;">
                            <span data-label>Create Account</span>
                            <?= icon("sync", "icon") ?>
                        </button>
                    </form>

                    <p class="swap" style="margin-top:1rem;">
                        Already registered?
                        <button type="button" data-flip="false">Back to sign in</button>
                    </p>

                    <div class="footline">
                        <span class="l"><?= icon("lock", "icon icon-sm") ?> Encrypted DB Node</span>
                        <span class="r">3D Flip Active</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<script src="assets/js/app.js"></script>
</body>
</html>


<?php
session_start();
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {

    if ($_SESSION['role'] == "admin") {
        header("Location: home.php");
        exit;
    } elseif ($_SESSION['role'] == "student") {
        header("Location: home2.php");
        exit;
    }
}

// Process login
if (isset($_POST['sub'])) {

    $username1 = "jason";
    $pass1 = "admin";

    $username2 = "Danmar";
    $pass2 = "walangtitibag";

    $uname = $_POST['username'];
    $pw = $_POST['pass'];

    if ($uname == $username1 && $pw == $pass1) {

        $_SESSION['username'] = $uname;
        $_SESSION['role'] = "admin";

        header("Location: home.php");
        exit;

    } elseif ($uname == $username2 && $pw == $pass2) {

        $_SESSION['username'] = $uname;
        $_SESSION['role'] = "student";

        header("Location: home2.php");
        exit;

    } else {
        echo "<script>alert('Wrong username or password!');</script>";
    }
}
?>

<form method="post" action="index.php">
    <label>Username:</label><br>
    <input type="text" name="username" required>
    <br><br>

    <label>Password:</label><br>
    <input type="password" name="pass" required>
    <br><br>

    <input type="submit" name="sub" value="Login">
</form>

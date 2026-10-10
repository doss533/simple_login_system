
<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['role'] != "student") {
    if ($_SESSION['role'] == "admin") {
        header("Location: home.php");
        exit;
    }

    header("Location: index.php");
    exit;
}
?>

<h1>Student Dashboard</h1>

<?php
echo "Hi " . htmlspecialchars($_SESSION['username']) .
     ", you are an " . htmlspecialchars($_SESSION['role']) . ".";
?>
<br>
<a href=" logout.php">log out</a>
<?php
session_start();
$_SESSION['username']="";
$_SESSION['role']="";

session_destroy();
header("Location: index.php");
exit;
?>


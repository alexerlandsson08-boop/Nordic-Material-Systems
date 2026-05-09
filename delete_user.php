<?php
require_once 'functions.php';

$username = $_GET['Cname'];

$db = connectToDb();
$statement = $db->prepare("DELETE FROM company_logins WHERE Cname = ?");
$statement->bind_param("s", $username);
$statement->execute();
$statement->close();
$db->close();
$_SESSION['message'] = "User " . htmlspecialchars($username) . " has succesfully been deleted.";
header("Location: account-management.php");
?>


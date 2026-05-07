<?php
require_once 'functions.php';

// Kontrollera login
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== TRUE) {
    header("Location: login-form.php");
    exit();
}

$selectedPlan = $_POST['plan'];
$username = $_SESSION['username'];


$db = connectToDb();


$stmt = $db->prepare("UPDATE company_logins SET sub_plan = ? WHERE Cname = ?");

$stmt->bind_param("ss", $selectedPlan, $username);

$stmt->execute();

$stmt->close();
$db->close();

header('Location: index.php');
?>
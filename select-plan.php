<?php
require_once 'functions.php';

// Kontrollera login annars skicka till inlogg
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== TRUE) {
    header("Location: login-form.php");
    exit();
}

//Spara vald plan och användare
$selectedPlan = $_POST['plan'];
$username = $_SESSION['username'];

//anslut till databas och uppdatera plan fältet till det nya, gå sedan boknings kalendern
$db = connectToDb();


$stmt = $db->prepare("UPDATE company_logins SET sub_plan = ? WHERE Cname = ?");

$stmt->bind_param("ss", $selectedPlan, $username);

$stmt->execute();

$stmt->close();
$db->close();


header('Location: booking.php');
?>
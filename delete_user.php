<?php
require_once 'functions.php';

//spara användarnamnet som ska raderas
$username = $_GET['Cname'];

//Anslut till databasen och ta bort användare
$db = connectToDb();
$statement = $db->prepare("DELETE FROM company_logins WHERE Cname = ?");
$statement->bind_param("s", $username);
$statement->execute();
$statement->close();
$db->close();

//skicka meddelande att det har lyckats till hanterings sidan
$_SESSION['message'] = "User " . htmlspecialchars($username) . " has succesfully been deleted.";
header("Location: account-management.php");
?>


<?php

require_once('functions.php');

if (!isset($_POST['name']) || !isset($_POST['password'])) {
    echo "Form data missing.";
    exit();
}



$username = $_POST['name'];
$password = $_POST['password'];

$db = connectToDb();

$statement = $db->prepare("SELECT * FROM company_logins WHERE Cname = ?");
$statement->bind_param('s', $username);
$statement->execute();
$result = $statement->get_result();
$user = $result->fetch_assoc();


if ( ! $user) {
    $_SESSION['message'] = "Inlogg hittas ej!";
    header("Location: login-form.php");
    exit();
}

$hashedPassword = $user['password'];


if ( ! password_verify($password, $hashedPassword)) {
    $_SESSION['message'] = "Felaktigt lösenord!";
    header("Location: login-form.php");
    exit();
}


$_SESSION['loggedIn'] = TRUE;
$_SESSION['email'] = $user['email'];
$_SESSION['username'] = $user['Cname'];



header('Location: booking.php');
?>



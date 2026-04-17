<?php
require_once('functions.php');

$db = connectToDb();

/* Hämta datan från formuläret */

$username = $_POST['name'];
$password = $_POST['password'];
$passwordVari = $_POST['password-vari'];
$email = $_POST['email'];


/* Kontrollera att lösenord är sama */

if($password !== $passwordVari){

$_SESSION['message'] = "Lösenorden matchar inte";
header("Location:log-in.php");
exit();

}


/* Kontrollera om användarnamn redan finns */

$check = $db->prepare("SELECT id FROM company_logins WHERE Cname = ?");
$check->bind_param("s",$username);
$check->execute();
$result = $check->get_result();

if($result->num_rows > 0){

$_SESSION['message-same'] = "Användarnamnet är redan taget";
header("Location:log-in.php");
exit();

}


/* Hasha lösenord */

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);


/* Spara användare */

$statement = $db->prepare(
"INSERT INTO company_logins (Cname,password,email)
VALUES (?,?,?)"
);

$statement->bind_param(
"sss",
$username,
$hashedPassword,
$email
);

$statement->execute();


/* Skicka tillbaka meddelande med användaren */

$_SESSION['message'] = "Kontot skapades!";



header("Location:log-in.php");
exit();
?>
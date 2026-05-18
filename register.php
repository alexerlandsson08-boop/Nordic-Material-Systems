<?php
require_once('functions.php');



// Hämta datan från formuläret

$username = $_POST['name'];
$password = $_POST['password'];
$passwordVari = $_POST['password-verify'];
$email = $_POST['email'];


// Kontrollera att lösenord är sama 

if($password !== $passwordVari){

$_SESSION['message'] = "Lösenorden matchar inte";
header("Location:login-form.php");
exit();

}


//Anslut till databasen kolla om användare redan finns
$db = connectToDb();
$check = $db->prepare("SELECT id FROM company_logins WHERE Cname = ?");
$check->bind_param("s",$username);
$check->execute();
$result = $check->get_result();


if($result->num_rows > 0){
$_SESSION['message-same'] = "Användarnamnet är redan taget";
header("Location:login-form.php");
exit();

}


//Hasha lösenordet

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);


//Spara användaren

$statement = $db->prepare(
"INSERT INTO company_logins (Cname,password,email,Sub_plan, User_type)
VALUES (?,?,?,'free','user')"
);

$statement->bind_param(
"sss",
$username,
$hashedPassword,
$email
);

$statement->execute();


//Skicka meddelande om att kontot skapats och gå tillbaka till inloggen

$_SESSION['message'] = "Kontot skapades!";



header("Location:login-form.php");



exit();
?>
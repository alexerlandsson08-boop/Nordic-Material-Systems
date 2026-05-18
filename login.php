<?php
require_once('functions.php');

//Om information saknas skriv ut det
if (!isset($_POST['name']) || !isset($_POST['password'])) {
    echo "Form data missing.";
    exit();
}

//Spara användarnamn och lösenord
$username = $_POST['name'];
$password = $_POST['password'];

//Anslut till databas och kontrollera om användare finns
$db = connectToDb();

$statement = $db->prepare("SELECT * FROM company_logins WHERE Cname = ?");
$statement->bind_param('s', $username);
$statement->execute();
$result = $statement->get_result();
$user = $result->fetch_assoc();

//Om användare inte finns skicka tillbaka
if ( ! $user) {
    $_SESSION['message'] = "Inlogg hittas ej!";
    header("Location: login-form.php");
    exit();
}

//Spara lösenordet i hashad och verifiera
$hashedPassword = $user['password'];


if ( ! password_verify($password, $hashedPassword)) {
    $_SESSION['message'] = "Felaktigt lösenord!";
    header("Location: login-form.php");
    exit();
}

//Spara information i SESSION
$_SESSION['UserId'] = $user['id'];
$_SESSION['loggedIn'] = TRUE;
$_SESSION['email'] = $user['email'];
$_SESSION['username'] = $user['Cname'];
$_SESSION['selectedPlan'] = $user['Sub_plan'];

//Om admin sätt till true, annars false och gå tillbaka till index
if ($user['User_type'] === 'admin') {
    $_SESSION['isAdmin'] = TRUE;
} else {
    $_SESSION['isAdmin'] = FALSE;
}

header('Location: index.php');

?>



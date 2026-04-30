<?php
require_once('functions.php');

$selectedPlan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan'])) {
    $selectedPlan = $_POST['plan'];
}

if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === TRUE) {
    header('Location: booking.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/main.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/Screenshot 2026-04-03 20.27.25 (1).png">
    <title>Login - Nordic Material Systems</title>
    <script src="translations.js"></script>
</head>
<body>

     <img class="logo" src="/images/Screenshot 2026-04-03 20.27.25 (1).png" alt="NMS, logotype">

    <a href="index.php" class="back-link" data-i18n="login.back-home">Tillbaka till hem</a>
    <p id="lang-toggle" onclick="setLanguage(currentLang === 'sv' ? 'en' : 'sv')" style="position: absolute; top: 10px; right: 10px;">EN/SV</p>
    
<main>    
   <h1 data-i18n="login.title">Logga in</h1>

    
    

    <form class ="login-form "action="login.php" method="post" >
        <input type="hidden" name="plan" value="<?php echo htmlspecialchars($selectedPlan); ?>">
        <input type="text" name="name" required data-i18n-placeholder="login.company-name" placeholder="Företagsnamn"> 

        <input type="password" name="password" required data-i18n-placeholder="login.password" placeholder="Lösenord">

        <button type="submit" data-i18n="login.submit">Logga in</button>
    </form>

    <?php 
    if (isset($_SESSION['message'])) {
    echo "<p style='color: red;'>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
    }
    
    if (isset($_SESSION['message-same'])) {
    echo "<p style='color: red;'>" . $_SESSION['message-same'] . "</p>";
    unset($_SESSION['message-same']);
}
?>


<h1 data-i18n="register.title">Skapa konto</h1>



<form class="login-form" action="register.php" method="post">
    <input type="hidden" name="plan" value="<?php echo htmlspecialchars($selectedPlan); ?>">

<input type="text" name="name" required data-i18n-placeholder="login.company-name" placeholder="Företagsnamn">

<input type="password" name="password" required data-i18n-placeholder="login.password" placeholder="Lösenord">

<input type="password" name="password-vari" required data-i18n-placeholder="register.verify-password" placeholder="Verifiera lösenord">

<input type="email" name="email" required data-i18n-placeholder="register.email" placeholder="E-post">

<button type="submit" data-i18n="register.submit">Registrera konto</button>

</form>

</main>

</body>
</html>


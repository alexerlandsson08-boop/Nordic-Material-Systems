<?php
require_once('functions.php');

// Variabel som ska innehålla vald plan från formuläret
$selectedPlan = '';
// Kontrollerar om formuläret skickats med POST-metoden och om fältet "plan" finns med i formuläret
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan'])) {

    // Sparar användarens valda plan i variabeln
    $selectedPlan = $_POST['plan'];
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/main.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/logga.webp">
    <title>Login - Nordic Material Systems</title>
    <script src="translations.js"></script>
</head>
<body>

<header class="site-header">
    <div class="user-info-container">
        <a href="index.php" class="back-link" data-i18n="login.back-home">Tillbaka till hem</a>
    </div>
    
    <div class="site-header-right">
        <p id="lang-toggle" onclick="setLanguage(currentLang === 'sv' ? 'en' : 'sv')">EN/SV</p>
        <img class="logo" src="/images/logga.webp" alt="NMS, logotype">
    </div>
</header>

<main>    
   <h1 data-i18n="login.title">Logga in</h1>

    <form class ="login-form "action="login.php" method="post" >
        
        <input type="hidden" name="plan" value="<?php echo htmlspecialchars($selectedPlan); ?>">

        <!--Alla lable för skärmläsare-->
        <label hidden for="text">Skriv företagsnamn</label>
        <input type="text" name="name" required data-i18n-placeholder="login.company-name" placeholder="Företagsnamn"> 

        <label hidden for="password">Skriv lösenord</label>
        <input type="password" name="password" required data-i18n-placeholder="login.password" placeholder="Lösenord">

        <label hidden for="submit">Logga in</label>
        <button name="submit" type="submit" data-i18n="login.submit">Logga in</button>
    </form>

    <?php 
    //skriv ut meddelande om det finns
    if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
    }
    
    if (isset($_SESSION['message-same'])) {
    echo "<p>" . $_SESSION['message-same'] . "</p>";
    unset($_SESSION['message-same']);
}
?>


<h1 data-i18n="register.title">Skapa konto</h1>



<form class="login-form" action="register.php" method="post">
    <input type="hidden" name="plan" value="<?php echo htmlspecialchars($selectedPlan); ?>">

    <label hidden for="name">Skriv företagsnamn</label>
    <input type="text" name="name" required data-i18n-placeholder="login.company-name" placeholder="Företagsnamn">

    <label hidden for="password">Skriv lösenord</label>
    <input type="password" name="password" required data-i18n-placeholder="login.password" placeholder="Lösenord">

    <label hidden for="password-verify">Verifiera lösenord</label>
    <input type="password" name="password-verify" required data-i18n-placeholder="register.verify-password" placeholder="Verifiera lösenord">

    <label hidden for="email">E-post</label>
    <input type="email" name="email" required data-i18n-placeholder="register.email" placeholder="E-post">

    <label hidden for="submit">Registrera konto</label>
    <button type="submit" data-i18n="register.submit">Registrera konto</button>

</form>
</main>
</body>
</html>


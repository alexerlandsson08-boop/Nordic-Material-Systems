<?php
require_once 'functions.php';

//kontrollera om användaren är inloggad anars skicka till inlogg

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== TRUE) {
    $_SESSION['message'] = "login.ms";
    header('Location: login-form.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/main.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/logga.webp">
    <title>Kontakta-Nordic Material Systems</title>
    <script src="translations.js"></script>
</head>
<body>

<header class="site-header">
    <div class="user-info-container">
        <p class="user-info"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        <a href="index.php" class="back-link" data-i18n="login.back-home">Tillbaka till hem</a>
    </div>

    <div class="site-header-right">
        <p id="lang-toggle" onclick="setLanguage(currentLang === 'sv' ? 'en' : 'sv')">EN/SV</p>
        <img class="logo" src="/images/logga.webp" alt="NMS, logotype">
    </div>
</header>

<main class="contact">
    <h1 data-i18n="contact.title">Kontakta</h1>
    <form action="index.php">
        <label hidden for="text" data-i18n="contact.label.title">Rubrik</label>
        <input type="text" name="title" data-i18n-placeholder="contact.placeholder.title" placeholder="Rubrik" required>

        <label hidden for="textarea" data-i18n="contact.label.content">Innehåll</label>
        <textarea name="content" data-i18n-placeholder="contact.placeholder.content" placeholder="Skriv..." required></textarea>

        <label hidden for="submit" data-i18n="contact.label.submit">Skicka</label>
        <input name="submit" type="submit" data-i18n-value="contact.submit" value="Skicka">
    </form>
</main>

</body>
</html>
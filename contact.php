<?php
require_once 'functions.php';

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== TRUE) {
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
    <link rel="icon" type="image/x-icon" href="/images/Screenshot 2026-04-03 20.27.25 (1).png">
    <title>Kontakta-Nordic Material Systems</title>
    <script src="translations.js"></script>
</head>
<body>

<div class="contact">
    <div class="user-info-container">
<p class='user-info'><?php echo htmlspecialchars($_SESSION['username']); ?></p>
<a href="index.php" class="back-link" data-i18n="login.back-home" >Back to home</a>
</div>

    <h1>Kontakta</h1>
    <form>
        <input type="text" name="title" placeholder="Rubrik" required>

        <textarea name="content" placeholder="Skriv..." required></textarea>

        <input type="submit" value="Publicera">
    </form>

</div>
   
</body>
</html>
<?php
require_once 'functions.php';

    //kontrollera om användaren är inloggad och visa i såfall kontot samt utlogg knapp
  if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === TRUE) {
        echo "<header class='site-header'>";
        echo "<div class='user-info-container'>";
        echo "<p class='user-info'>" . htmlspecialchars($_SESSION['username']) . "</p>";
        echo "<a data-i18n='nav.logout' class='logout-button' href='logout.php'>Logout</a>";
        echo "</div>";
        echo "</header>";
    }

    //Kontrollera om användaren är admin och visa i så fall Manage accounts
    if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === TRUE) {
        echo "<header class='site-header'>";
        echo "<nav class='site-nav'>";
        echo "<a href='account-management.php' data-i18n='nav.manage-accounts'>Manage Accounts</a>";
        echo "</nav>";
        echo "</header>";
    }
    
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/main.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/logga.webp">
    <title>Nordic Material Systems</title>
    <script src="translations.js"></script>
</head>
<body>
    
<header class="site-header">
    <nav class="site-nav">
        <a href="products.php" data-i18n="nav.book-appointment">Boka tid</a>
        <a href="choises.php" data-i18n="nav.team">Mina bokningar</a>
        <a href="products.php" data-i18n="nav.products">Produkter</a>
        <a href="contact.php" data-i18n="nav.contact">Kontakt</a>
    </nav>
    <div class="site-header-right">
        <p id="lang-toggle" onclick="setLanguage(currentLang === 'sv' ? 'en' : 'sv')">EN/SV</p>
        <img class="logo" src="/images/logga.webp" alt="NMS, logotype">
    </div>
</header>
<main>
    <div class="image-cover">
        <img id='Big-picture' src="/images/NMScrew.webp" alt="Nordic Material Systems founders">
        <div class="cover-overlay"></div>
        <h1 class='title' data-i18n="title.main">Nordic Material Systems</h1>
        <p class="title" data-i18n="hero.description">Nordic Material Systems är inte en vision om en perfekt framtid – det är en realistisk, genomförbar lösning på ett konkret problem som finns här och nu.</p>
    </div>

    <div class="about">
        <h1 data-i18n="about.title">Vad händer egentligen med alla kläder när vi inte längre använder dem?</h1> 
        <p id="box-2" data-i18n="about.description">Nordic Material Systems erbjuder cirkulära och hållbara textillösningar för företag genom att återvinna och förädla textilier. Företaget bygger på att köpa in och förädla överblivna textilier från företag. Dessa textilier återställs, bearbetas och omvandlas till nytt garn och nya, fräscha textilprodukter av hög kvalitet.Företaget arbetar med abonnemangsbaserade samarbeten där företag betalar en löpande avgift för att få sina textilier omhändertagna på ett hållbart sätt. Genom abonnemanget säkerställs en kontinuerlig insamling, återvinning och återanvändning av materialet, vilket minskar avfall och klimatpåverkan samtidigt som företagens resurser tas tillvara.De återvunna materialen säljs vidare som garn eller färdiga textilier till företag inom mode, inredning och andra textilrelaterade branscher. NMS skapar därmed värde både ekonomiskt och miljömässigt genom att förlänga textiliers livscykel och bidra till en mer cirkulär textilindustri i Sverige.</p>
        <p data-i18n="about.description2">Vår innovation, Nordic Material Systems, är ett cirkulärt system som fokuserar på det som faktiskt går att återvinna effektivt: sorterade monomaterial som 100 procent bomull, 100 procent ull och andra rena cellulosafibrer. Istället för att försöka lösa hela textilproblemet, fokuserar vi på den del som är genomförbar och lönsam. Genom abonnemangsbaserad insamling från företag samlar vi in deras överblivna eller kasserade monomaterial, kvalitetssäkrar dem och bearbetar dem mekaniskt till ny fiber och nytt garn. Det återvunna materialet skickas sedan tillbaka till företagen som en cirkulär råvara av hög kvalitet.</p>
    </div>
</main>
</body>
</html>
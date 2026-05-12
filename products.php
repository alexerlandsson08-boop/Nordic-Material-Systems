<?php
require_once 'functions.php';

if (isset($_SESSION['selectedPlan']) && $_SESSION['loggedIn'] === TRUE) {
    echo "Du har redan ett aktiv prenumerationsplan. Om du vill ändra din prenumeration, vänligen välj ny plan nedan. <br> Vald plan: <strong>" . htmlspecialchars($_SESSION['selectedPlan'])  . "</strong>";
}

?>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMS-Produkter</title>
    <link href="css/main.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/Screenshot 2026-04-03 20.27.25 (1).png">
    <script src="translations.js"></script>
</head>
<body>
    <img class="logo" src="/images/Screenshot 2026-04-03 20.27.25 (1).png" alt="NMS, logotype">
    <p id="lang-toggle" onclick="setLanguage(currentLang === 'sv' ? 'en' : 'sv')"> EN/SV</p>

    <main class="products-page">
        <h1 data-i18n="products.title">Våra prenumerationsalternativ</h1>
        <a href="index.php" class="back-link" data-i18n="products.back-home">Tillbaka till hem</a>

        <div class="pricing-container">
            <table class="pricing-table" aria-label="Subscription options">
                <thead>
                    <tr>
                        <th></th>
                        <th data-i18n="products.basic.title">Basic</th>
                        <th data-i18n="products.standars.title">Standard</th>
                        <th data-i18n="products.premium.title">Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th data-i18n="products.table.pickup-frequency">Pickup frequency</th>
                        <td data-i18n="products.basic.feature1">1 upphämtning per månad</td>
                        <td data-i18n="products.standars.feature1">2 upphämtningar per månad</td>
                        <td data-i18n="products.premium.feature1">4 upphämtningar per månad (veckovis)</td>
                    </tr>
                    <tr>
                        <th data-i18n="products.table.handling">Handling & sorting</th>
                        <td data-i18n="products.basic.feature3">Grundläggande sortering</td>
                        <td data-i18n="products.standars.feature2">Prioriterad sortering</td>
                        <td data-i18n="products.premium.feature2">Prioriterad och snabb hantering</td>
                    </tr>
                    <tr>
                        <th data-i18n="products.table.price">Price</th>
                        <td data-i18n="products.basic.price">Pris: 5000 kr/månad</td>
                        <td data-i18n="products.standars.price">Pris: 10000 kr/månad</td>
                        <td data-i18n="products.premium.price">Pris: 15000 kr/månad</td>
                    </tr>
                    <tr>
                        <th data-i18n="products.table.recycling">Recycling</th>
                        <td data-i18n="products.basic.feature4">Återvinning till fiber</td>
                        <td data-i18n="products.standars.feature3">Återvinning till fiber och garn</td>
                        <td data-i18n="products.premium.feature3">Återvinning till högkvalitativt garn</td>
                    </tr>
                    <tr>
                        <th data-i18n="products.table.report">Sustainability report</th>
                        <td data-i18n="products.basic.feature5">Enkel hållbarhetsrapport (volym + klimatpåverkan)</td>
                        <td data-i18n="products.standars.feature4">Utökad hållbarhetsrapport (CO₂-besparing, materialdata)</td>
                        <td data-i18n="products.premium.feature4">Skräddarsydd hållbarhetsrapport</td>
                    </tr>
                    <tr>
                        <th data-i18n="products.table.consulting">Consulting</th>
                        <td>—</td>
                        <td data-i18n="products.standars.feature5">Grundläggande rådgivning kring hållbara materialval</td>
                        <td data-i18n="products.premium.feature5">Rådgivning kring cirkulär design</td>
                    </tr>
                    <tr>
                        <th data-i18n="products.table.extra-benefit">Extra benefit</th>
                        <td>—</td>
                        <td>—</td>
                        <td data-i18n="products.premium.feature6">Möjlighet att få tillbaka eget återvunnet material</td>
                    </tr>
                    <tr class="plan-actions">
                        <th></th>
                        <td>
                            <form action="select-plan.php" method="post" class="plan-form">
                                <input type="hidden" name="plan" value="basic">
                                <button type="submit" data-i18n="products.basic.button">Boka möte</button>
                            </form>
                        </td>
                        <td>
                            <form action="select-plan.php" method="post" class="plan-form">
                                <input type="hidden" name="plan" value="standard">
                                <button type="submit" data-i18n="products.standars.button">Boka möte</button>
                            </form>
                        </td>
                        <td>
                            <form action="select-plan.php" method="post" class="plan-form">
                                <input type="hidden" name="plan" value="premium">
                                <button type="submit" data-i18n="products.premium.button">Boka möte</button>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
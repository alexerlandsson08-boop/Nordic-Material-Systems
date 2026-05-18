<?php
require_once 'functions.php';

//kontrollera om användaren är inloggad anars skicka till inlogg
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== TRUE) {
    header('Location: login-form.php');
    $_SESSION['message'] = "<p data-i18n='login.ms'> Vänligen logga in och testa igen. </p>";
}

//spara användarnamnet och anslut till databasen
$username = $_SESSION['username'];
$db = connectToDb();

//Kontrollera om admin och i såfall hämta ALLA bokningar annars endast användarens
if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === TRUE) {
    $stmt = $db->prepare("SELECT id, `date-time`, name FROM bookings ORDER BY `date-time` ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $countRow['antal'] = $result->num_rows;
}else{
    $countStmt = $db->prepare("SELECT COUNT(*) AS antal FROM bookings WHERE name = ?");
    $countStmt->bind_param("s", $username);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult->fetch_assoc();
    $countStmt->close();

    $stmt = $db->prepare("SELECT id, `date-time`, name FROM bookings WHERE name = ? ORDER BY `date-time` ASC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mina bokningar</title>
    <link href="css/main.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/logga.webp">
    <script src="translations.js"></script>
</head>

<body>

<header class="site-header">
    <div class="user-info-container">
        <p class='user-info'><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        <a href="index.php" class="back-link" data-i18n="login.back-home">Tillbaka till hem</a>
    </div>
    
    <div class="site-header-right">
        <p id="lang-toggle" onclick="setLanguage(currentLang === 'sv' ? 'en' : 'sv')">EN/SV</p>
        <img class="logo" src="/images/logga.webp" alt="NMS, logotype">
    </div>
</header>

<main>
    <?php if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === TRUE): ?>
        <h1>Alla bokningar</h1>
        <p>Här kan du se alla bokningar som finns</p>
    <?php else: ?>
        <h1 data-i18n="mybookings.title">Mina bokningar</h1>
        <p data-i18n="mybookings.welcome">Välkommen till din bokningsöversikt!</p>
    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
        <strong><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></strong>
    <?php endif; ?>

    <p data-i18n="mybookings.count" data-count="<?php echo $countRow['antal']; ?>">
        Du har just nu <strong><?php echo $countRow['antal']; ?></strong> bokningar.
    </p>

    <?php if ($countRow['antal'] > 0): ?>
    <table>
        <tr>
            <th data-i18n="mybookings.table.datetime">Datum & Tid</th>
            <?php if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === TRUE): ?>
                <th>Användare</th>
            <?php endif; ?>
            <th data-i18n="mybookings.table.manage">Hantera</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['date-time']); ?></td>
            <?php if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === TRUE): ?>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
            <?php endif; ?>
            <td>
                <form method="POST" action="delete-bookings.php">
                    <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="delete-btn" data-i18n="mybookings.cancel">Avboka</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php else: ?>
        <p data-i18n="mybookings.none">Du har inga bokningar just nu.</p>
    <?php endif; ?>
</main>
</body>
</html>

<?php
//avsluta statement och stäng ner databas-kopplingen
$stmt->close();
$db->close();
?>
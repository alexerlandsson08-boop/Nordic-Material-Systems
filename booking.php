<?php
require_once 'functions.php';

//Alla bokbara tider
$allTimes = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];

//hämta månad/år från url, annars använd nuvarande
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : date('Y');

//Skapa variabler för meddelenden och vald datum/tid
$messageKey  = "";
$messageType = "";
$date        = "";
$time        = "";


//kontrollera att information skickats med POST och att book_date finns
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_date'])) {

    //hämtar valt datum och tid
    $date = $_POST['book_date'];
    $time = $_POST['book_time'];

    //Om ingen tid finns, skicka fel meddelande annars sätt ihop datum och tid till datetime
    if ($time == "") {
        $messageKey  = "booking.select-time";
        $messageType = "error";
    } else {
        $dateTime = $date . " " . $time;

        //om det finns datum/tid, användarnamn och email-->skicka lyckas. om tid tagen, skicka fel
        if (bookDate($dateTime, $_SESSION['username'], $_SESSION['email'])) {
            $messageKey  = "booking.confirmation";
            $messageType = "success";
        } else {
            $messageKey  = "booking.taken";
            $messageType = "error";
        }
    }
}

//Bygg kalendern

function build_calendar($month, $year) {

    $daysOfWeek = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    //skapa timestamp för första dagen. Hämta antal dagar, vilken veckodag som är först och dagens datum
    $firstDay   = mktime(0,0,0,$month,1,$year);
    $numberDays = date('t',$firstDay);
    $startDay   = date('w',$firstDay);
    $today      = date('Y-m-d');

    //Börja bygg kalendern
    $calendar  = "<table class='calendar'>";
    $calendar .= "<caption>" . date('F Y', $firstDay) . "</caption>";
    $calendar .= "<tr>";

   //Skriv ut dagarna
    foreach ($daysOfWeek as $day) {
        $calendar .= "<th>$day</th>";
    }

    $calendar .= "</tr><tr>";

    //Lägger till toma rutor innan första dagen
    for ($i = 0; $i < $startDay; $i++) {
        $calendar .= "<td class='empty'></td>";
    }

    //Loopa genom alla dagar i månaden
    for ($day = 1; $day <= $numberDays; $day++) {

        //Ställ in formatet YYYY-MM-DD
        $date    = sprintf('%04d-%02d-%02d', $year, $month, $day);
        //Array för CSS klasser
        $classes = [];

        //Om det är idag
        if ($date == $today) {
            $classes[] = "today";
        }

        //Om det har varit
        if (strtotime($date) < strtotime($today)) {
            $classes[] = "past";
        }

        //Gör om array till vanlig string
        $class = implode(" ", $classes);

        //Om datumet inte har varit
        if (!in_array("past", $classes)) {
            //Gör det klickbart
            $calendar .= "<td class='$class'>
                <a class='book-link' href='?month=$month&year=$year&book=$date'>$day</a>
            </td>";
        } else {
            //Gör att gamla datum ej är klickbara
            $calendar .= "<td class='$class'>$day</td>";
        }

        //Bygg ny rad efter varje vecka
        if (($day + $startDay) % 7 == 0) {
            $calendar .= "</tr><tr>";
        }
    }

    //Avsluta tabell och returnera färdig kalender
    $calendar .= "</tr></table>";
    return $calendar;
}

//Månaden innan, efter och standard år
$prevMonth = $month - 1;
$nextMonth = $month + 1;
$prevYear  = $year;
$nextYear  = $year;

//Om januari föregående december och tvärtom
if ($month == 1) {
    $prevMonth = 12;
    $prevYear--;
}

if ($month == 12) {
    $nextMonth = 1;
    $nextYear++;
}

//Boka tiden i databasen
function bookDate($date, $name, $email, $userId = null) {
    $db = connectToDb();

    $checkStmt = $db->prepare('SELECT COUNT(*) as count FROM bookings WHERE `date-time` = ?');
    $checkStmt->bind_param('s', $date);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row    = $result->fetch_assoc();
    $checkStmt->close();

    //Om count > 0 så är tiden bokad och returnerar false
    if ($row['count'] > 0) {
        $db->close();
        return false;
    }

    //Gör om till rätt format
    $dateTime   = date('Y-m-d H:i:s', strtotime($date));
    //Kontrollerar om Userid finns och bokar utan om det behövs. Eftersom att det krävs inlogg för att nå denna sida behövs det egentligen inte
    $useUserId  = false;

    if ($userId !== null) {
        $columnCheck = $db->query("SHOW COLUMNS FROM bookings LIKE 'user_id'");
        if ($columnCheck && $columnCheck->num_rows > 0) {
            $useUserId = true;
        }
    }

    if ($useUserId) {
        $stmt = $db->prepare('INSERT INTO bookings (`date-time`, name, email, user_id) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $dateTime, $name, $email, $userId);
    } else {
        $stmt = $db->prepare('INSERT INTO bookings (`date-time`, name, email) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $dateTime, $name, $email);
    }

    $ok = $stmt->execute();
    $stmt->close();
    $db->close();
    return $ok;
}

//Hämta alla bokade tider för ett visst datum
function getBookedTimes($date) {
    $db         = connectToDb();
    $dateOnly   = date('Y-m-d', strtotime($date));
    $startOfDay = $dateOnly . ' 00:00:00';
    $endOfDay   = $dateOnly . ' 23:59:59';

    $stmt = $db->prepare('SELECT `date-time` FROM bookings WHERE `date-time` BETWEEN ? AND ?');
    $stmt->bind_param('ss', $startOfDay, $endOfDay);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookedTimes = [];
    while ($row = $result->fetch_assoc()) {
        $bookedTimes[] = date('H:i', strtotime($row['date-time']));
    }

    $stmt->close();
    $db->close();
    return $bookedTimes;
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Boka möte</title>
    <link rel="stylesheet" href="css/main.css">
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

<h1 data-i18n="booking.title" style="text-align:center;">Boka ett möte</h1>

<div class="message">
    <?php if (!empty($messageKey)): ?>
        <div class="<?php echo $messageType; ?>"
             data-i18n="<?php echo $messageKey; ?>"
             <?php if ($messageKey === 'booking.confirmation'): ?>
                 data-date="<?php echo $date; ?>"
                 data-time="<?php echo $time; ?>"
             <?php endif; ?>>
            <?php echo $messageKey; ?>
        </div>
    <?php endif; ?>
</div>

<div class="navigation">
    <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" data-i18n="booking.nav.prev">Tidigare</a>
    <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" data-i18n="booking.nav.today">Idag</a>
    <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" data-i18n="booking.nav.next">Nästa</a>
</div>

<?php if (isset($_GET['book'])): ?>
<?php
    $selectedDate = $_GET['book'];
    $bookedTimes  = getBookedTimes($selectedDate);
?>
<div class="booking-form">
    <h3 data-i18n="booking.form.title" data-date="<?php echo $selectedDate; ?>">Boka för <?php echo $selectedDate; ?></h3>

    <?php if (count($bookedTimes) < count($allTimes)): ?>
    <form method="post">
        <input type="hidden" name="book_date" value="<?php echo $selectedDate; ?>">

        <select name="book_time">
            <option value="" data-i18n="booking.form.select">Välj tid</option>
            <?php foreach ($allTimes as $time): ?>
                <?php if (!in_array($time, $bookedTimes)): ?>
                    <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <button type="submit" data-i18n="booking.form.button">Boka nu</button>
    </form>

    <?php else: ?>
        <p class="error" data-i18n="booking.no-times">Alla tider är bokade.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php echo build_calendar($month, $year); ?>

</body>
</html>
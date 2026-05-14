<?php
require_once 'functions.php';

// Tider som går att boka från start
$allTimes = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];

// Hämta månad och år
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : date('Y');

$message = "";

// Hantera bokning
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_date'])) {

    $date = $_POST['book_date'];
    $time = $_POST['book_time'];

    if ($time == "") {
        $message = "<div class='error'>Please select a time.</div>";
    } else {
        $dateTime = $date . " " . $time;

        if (bookDate($dateTime, $_SESSION['username'], $_SESSION['email'])) {
            $message = "<div class='success'>Booking confirmed!</div>";
        } else {
            $message = "<div class='error'>Time already booked. Try another.</div>";
        }
    }
}

// Bygg kalender
function build_calendar($month, $year) {

    $daysOfWeek = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    $firstDay = mktime(0,0,0,$month,1,$year);
    $numberDays = date('t',$firstDay);
    $startDay = date('w',$firstDay);

    $today = date('Y-m-d');

    $calendar = "<table class='calendar'>";
    $calendar .= "<caption>" . date('F Y', $firstDay) . "</caption>";
    $calendar .= "<tr>";

    foreach ($daysOfWeek as $day) {
        $calendar .= "<th>$day</th>";
    }

    $calendar .= "</tr><tr>";

    // tomma rutor
    for ($i = 0; $i < $startDay; $i++) {
        $calendar .= "<td class='empty'></td>";
    }

    // dagar
    for ($day = 1; $day <= $numberDays; $day++) {

        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $classes = [];

        if ($date == $today) {
            $classes[] = "today";
        }

        if (strtotime($date) < strtotime($today)) {
            $classes[] = "past";
        }

        $class = implode(" ", $classes);

        // klickbara datum
        if (!in_array("past", $classes)) {
            $calendar .= "<td class='$class'>
                <a class='book-link' href='?month=$month&year=$year&book=$date'>$day</a>
            </td>";
        } else {
            $calendar .= "<td class='$class'>$day</td>";
        }

        if (($day + $startDay) % 7 == 0) {
            $calendar .= "</tr><tr>";
        }
    }

    $calendar .= "</tr></table>";

    return $calendar;
}

// navigering mellan månader
$prevMonth = $month - 1;
$nextMonth = $month + 1;
$prevYear = $year;
$nextYear = $year;

if ($month == 1) {
    $prevMonth = 12;
    $prevYear--;
}

if ($month == 12) {
    $nextMonth = 1;
    $nextYear++;
}


//Boka in daumn och tid
function bookDate($date, $name, $email, $userId = null) {
    $db = connectToDb();
    
    // Kolla om tid redan är bokad
    $checkStmt = $db->prepare('SELECT COUNT(*) as count FROM bookings WHERE `date-time` = ?');
    $checkStmt->bind_param('s', $date);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    $checkStmt->close();
    
    // Returnera false om tiden redan är bokad
    if ($row['count'] > 0) {
        $db->close();
        return false;
    }
    
    $dateTime = date('Y-m-d H:i:s', strtotime($date));
    $useUserId = false;
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

//visa endast lediga tider
function getBookedTimes($date) {
    $db = connectToDb();
    // Ta ut exakt datum
    $dateOnly = date('Y-m-d', strtotime($date));
    $startOfDay = $dateOnly . ' 00:00:00';
    $endOfDay = $dateOnly . ' 23:59:59';
    
    // Kolla alla bokningar för det datumet
    $stmt = $db->prepare('SELECT `date-time` FROM bookings WHERE `date-time` BETWEEN ? AND ?');
    $stmt->bind_param('ss', $startOfDay, $endOfDay);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Hämta alla bokade tider"
    $bookedTimes = [];
    while ($row = $result->fetch_assoc()) {
        $time = date('H:i', strtotime($row['date-time']));
        $bookedTimes[] = $time;
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
    <p class='user-info'><?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <a href="index.php" class="back-link" data-i18n="login.back-home">Tillbaka till hem</a>
    </div>
    
    <div class="site-header-right">
        <p id="lang-toggle" onclick="setLanguage(currentLang === 'sv' ? 'en' : 'sv')">EN/SV</p>
        <img class="logo" src="/images/logga.webp" alt="NMS, logotype">
    </div>
</header>


<h1 data-i18n="booking.title" style="text-align:center;">Boka ett möte</h1>


<div class="message">
    <?php echo $message; ?>
</div>

<!-- NAVIGATION -->
<div class="navigation">
    <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">Tidigare</a>
    <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>">Idag</a>
    <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">Nästa</a>
</div>

<!-- BOOKING FORM -->
<?php
if (isset($_GET['book'])) {

    $selectedDate = $_GET['book'];
    $bookedTimes = getBookedTimes($selectedDate);
?>

<div class="booking-form">
    <h3>Book for <?php echo $selectedDate; ?></h3>

    <?php if (count($bookedTimes) < count($allTimes)): ?>

    <form method="post">
        <input type="hidden" name="book_date" value="<?php echo $selectedDate; ?>">

        <select name="book_time">
            <option value="">Välj tid</option>

            <?php
            foreach ($allTimes as $time) {
                if (!in_array($time, $bookedTimes)) {
                    echo "<option value='$time'>$time</option>";
                }
            }
            ?>
        </select>

        <button type="submit">Boka nu</button>
    </form>

    <?php else: ?>
        <p class="error">All times are booked.</p>
    <?php endif; ?>

</div>

<?php } ?>

<!-- KALENDER -->
<?php echo build_calendar($month, $year); ?>

</body>
</html>
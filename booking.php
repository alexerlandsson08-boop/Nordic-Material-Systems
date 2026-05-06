<?php
require_once 'functions.php';

// Tider som går att boka
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

// ===== KALENDER =====
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

// navigering
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
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="css/main.css">
    <script src="translations.js"></script>
</head>

<body>

<p id="lang-toggle" onclick="setLanguage(currentLang === 'sv' ? 'en' : 'sv')"> EN/SV</p>

<img class="logo" src="/images/Screenshot 2026-04-03 20.27.25 (1).png" alt="logo">

<a href="index.php" class="back-link" data-i18n="login.back-home" >Back to home</a>

<div class="user-info">
    Logged in as <strong><?php echo $_SESSION['username']; ?></strong>
</div>

<h1 style="text-align:center;">Book an Appointment</h1>

<!-- MEDDELANDE -->
<div class="message">
    <?php echo $message; ?>
</div>

<!-- NAVIGATION -->
<div class="navigation">
    <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">Previous</a>
    <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>">Today</a>
    <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">Next</a>
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
            <option value="">Choose time</option>

            <?php
            foreach ($allTimes as $time) {
                if (!in_array($time, $bookedTimes)) {
                    echo "<option value='$time'>$time</option>";
                }
            }
            ?>
        </select>

        <button type="submit">Book Now</button>
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
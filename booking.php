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

function getBookedDates($month, $year) {
    $db = connectToDb();
    $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
    $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

    // Get all bookings for the month
    $stmt = $db->prepare('SELECT `date-time` FROM bookings WHERE `date-time` BETWEEN ? AND ?');
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Group bookings by date
    $bookingsByDate = [];
    while ($row = $result->fetch_assoc()) {
        $date = date('Y-m-d', strtotime($row['date-time']));
        $time = date('H:i', strtotime($row['date-time']));
        if (!isset($bookingsByDate[$date])) {
            $bookingsByDate[$date] = [];
        }
        $bookingsByDate[$date][] = $time;
    }

    $stmt->close();
    $db->close();

    // Available times for comparison
    $allTimes = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];

    // Find dates that are fully booked (all time slots taken)
    $fullyBookedDates = [];
    foreach ($bookingsByDate as $date => $bookedTimes) {
        if (count(array_diff($allTimes, $bookedTimes)) === 0) {
            // All time slots are booked for this date
            $fullyBookedDates[] = (int)date('j', strtotime($date));
        }
    }

    return $fullyBookedDates;
}


function getPartiallyBookedDates($month, $year) {
    $db = connectToDb();
    $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
    $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

    // Get all bookings for the month
    $stmt = $db->prepare('SELECT `date-time` FROM bookings WHERE `date-time` BETWEEN ? AND ?');
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Group bookings by date
    $bookingsByDate = [];
    while ($row = $result->fetch_assoc()) {
        $date = date('Y-m-d', strtotime($row['date-time']));
        $time = date('H:i', strtotime($row['date-time']));
        if (!isset($bookingsByDate[$date])) {
            $bookingsByDate[$date] = [];
        }
        $bookingsByDate[$date][] = $time;
    }

    $stmt->close();
    $db->close();

    // Available times for comparison
    $allTimes = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];

    // Find dates that are partially booked (some but not all time slots taken)
    $partiallyBookedDates = [];
    foreach ($bookingsByDate as $date => $bookedTimes) {
        $availableSlots = count(array_diff($allTimes, $bookedTimes));
        if ($availableSlots > 0 && $availableSlots < count($allTimes)) {
            // Some time slots are booked but not all
            $partiallyBookedDates[] = (int)date('j', strtotime($date));
        }
    }

    return $partiallyBookedDates;
}


function bookDate($date, $name, $email, $userId = null) {
    $db = connectToDb();
    
    // Check if this specific time slot is already booked (prevent double-booking)
    $checkStmt = $db->prepare('SELECT COUNT(*) as count FROM bookings WHERE `date-time` = ?');
    $checkStmt->bind_param('s', $date);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    $checkStmt->close();
    
    // Return false if time slot is already taken
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


function getBookedTimes($date) {
    $db = connectToDb();
    // Extract just the date portion and create range for entire day
    $dateOnly = date('Y-m-d', strtotime($date));
    $startOfDay = $dateOnly . ' 00:00:00';
    $endOfDay = $dateOnly . ' 23:59:59';
    
    // Query all bookings for this specific date
    $stmt = $db->prepare('SELECT `date-time` FROM bookings WHERE `date-time` BETWEEN ? AND ?');
    $stmt->bind_param('ss', $startOfDay, $endOfDay);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Extract time portion from each booking
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


<div class="user-info-container">
<p class='user-info'><?php echo htmlspecialchars($_SESSION['username']); ?></p>
<a href="index.php" class="back-link" data-i18n="login.back-home" >Back to home</a>
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
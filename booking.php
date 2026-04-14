<?php
require_once 'vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$allTimes = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];

$month = max(1, min(12, (int)($_GET['month'] ?? date('n'))));
$year = max(1970, (int)($_GET['year'] ?? date('Y')));

$message = '';
$availableTimes = [];

// If a date is selected, get booked times for that date
if (isset($_GET['book'])) {
    $bookedTimes = getBookedTimes($_GET['book']);
    $availableTimes = array_diff($allTimes, $bookedTimes);
    $availableTimes = array_values($availableTimes); // Re-index array
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['book_date'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $selectedDate = $_POST['book_date'];
    $selectedTime = $_POST['book_time'] ?? '';
    
    if (!in_array($selectedTime, $allTimes, true)) {
        $message = "<p class='error'>Invalid time selected.</p>";
    } elseif (empty($availableTimes) || !in_array($selectedTime, $availableTimes, true)) {
        $message = "<p class='error'>Selected time is no longer available. Please choose another time.</p>";
    } elseif ($name === '') {
        $message = "<p class='error'>Please provide a valid company name.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p class='error'>Please provide a valid email address.</p>";
    } else {
        $dateTime = substr($selectedDate, 0, 10) . ' ' . $selectedTime . ':00';
        if (bookDate($dateTime, $name, $email)) {
            $message = "<p class='success'>Booking successful for " . date('F j, Y \a\t H:i', strtotime($dateTime)) . "!</p>";
            // Clear the booking form after success
            header('Location: ?month=' . $month . '&year=' . $year);
            exit;
        } else {
            $message = "<p class='error'>Booking failed. This time may have been taken. Please try another.</p>";
        }
    }


/**
 * Generates an HTML calendar table for a given month and year.
 * Shows which dates are today, booked, or in the past.
 * Available dates are clickable links to select a booking date.
 * @param int $month The month number (1-12)
 * @param int $year The year (4-digit)
 * @return string HTML table representing the calendar
 */
function build_calendar($month, $year) {
    // Days of week labels
    $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    // Get all booked dates for this month
    $booked = getBookedDates($month, $year);
    // Get calendar information for this month
    $firstDay = date_create("$year-$month-01");
    $numberDays = (int)$firstDay->format('t');  // Total days in month
    $monthName = $firstDay->format('F');        // Month name (e.g., "April")
    $startDay = (int)$firstDay->format('w');    // Day of week the month starts on (0=Sunday)

    $calendar = "<table class='calendar'><caption>$monthName $year</caption><tr>";
    foreach ($daysOfWeek as $day) {
        $calendar .= "<th class='header'>$day</th>";
    }
    $calendar .= '</tr><tr>' . str_repeat("<td class='empty'></td>", $startDay);

    for ($day = 1, $weekDay = $startDay; $day <= $numberDays; $day++, $weekDay++) {
        if ($weekDay === 7) {
            $weekDay = 0;
            $calendar .= '</tr><tr>';
        }

        $dateString = sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day);
        $classes = [];
        if ($dateString === date('Y-m-d 00:00:00')) $classes[] = 'today';
        if (in_array($day, $booked, true)) $classes[] = 'booked';
        if (strtotime($dateString) < strtotime(date('Y-m-d 00:00:00'))) $classes[] = 'past';
        $class = $classes ? ' class="' . implode(' ', $classes) . '"' : '';

        $content = empty($classes)
            ? "<a href='?month=$month&year=$year&book=" . rawurlencode($dateString) . "' class='book-link'>$day</a>"
            : $day;

        $calendar .= "<td{$class}>$content</td>";
    }

    $calendar .= str_repeat("<td class='empty'></td>", (7 - $weekDay) % 7);
    $calendar .= '</tr>';
    return $calendar;
}

$prevMonth = $month === 1 ? 12 : $month - 1;
$prevYear = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear = $month === 12 ? $year + 1 : $year;

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/main.css" rel="stylesheet">
    <title>Book Appointment - Nordic Material Systems</title>
   
</head>

<body>
    <a href="index.php" class="back-link">Back to Home</a>
    
    <h1 style="text-align: center; color: darkgreen;">Book an Appointment</h1>
    
    <div class="navigation">
        <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">&larr; Previous</a>
        <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>">Today</a>
        <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">Next &rarr;</a>
    </div>
    
    <?php echo $message; ?>
    
    <?php if (isset($_GET['book'])): ?>
    
        <div class="booking-form">
            <h3>Book Appointment for <?php echo date('F j, Y', strtotime($_GET['book'])); ?></h3>
            
            <?php if (!empty($availableTimes)): ?>
                <form method="post">
                    <input type="hidden" name="book_date" value="<?php echo $_GET['book']; ?>">
                    
                    <div>
                        <label for="book_time">Choose a time:</label>
                        <select id="book_time" name="book_time" required>
                            <option value="">-- Select a time --</option>
                            <?php foreach ($allTimes as $time): ?>
                                <?php $isBooked = in_array($time, getBookedTimes($_GET['book']), true); ?>
                                <option value="<?php echo $time; ?>" <?php echo $isBooked ? 'disabled' : ''; ?>>
                                    <?php echo $time; ?> <?php echo $isBooked ? '(Booked)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <input type="text" name="name" placeholder="Company name" required>
                    </div>
                    
                    <div>
                        <input type="email" name="email" placeholder="Company email" required>
                    </div>
                    
                    <button type="submit">Book Appointment</button>
                </form>
            <?php else: ?>
                <p class="error">No available times for this date. Please select another date.</p>
                <a href="?month=<?php echo $month; ?>&year=<?php echo $year; ?>">Back to calendar</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php echo build_calendar($month, $year); ?>
    
    <div style="text-align: center; margin: 20px; color: #666;">
        <p>Green background = Today | Red background = Booked | Gray = Past dates</p>
        <p>Click on available dates to book an appointment.</p>
    </div>
</body>
</html>
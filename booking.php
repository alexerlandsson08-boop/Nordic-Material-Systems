<?php

require_once('functions.php');
// Load installed packages (Composer autoloader)
require_once 'vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Handle month navigation from URL parameters or default to current month
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Handle booking form submission
$message = '';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_date'])) {
    $date = $_POST['book_date'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Validate form data
    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Attempt to book the date
        if (bookDate($date, $name, $email)) {
            $message = "<p class='success'>Booking successful for " . date('F j, Y', strtotime($date)) . "!</p>";
        } else {
            $message = "<p class='error'>Booking failed. Please try again.</p>";
        }
    } else {
        $message = "<p class='error'>Please provide a valid name and email address.</p>";
    }
}

/**
 * Build HTML calendar for the given month and year
 *
 * @param int $month The month (1-12)
 * @param int $year The year (4-digit)
 * @return string HTML table representing the calendar
 */
function build_calendar($month, $year) {
    // Days of the week headers
    $daysOfWeek = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

    // Get all booked dates for this month from database
    $bookedDates = getBookedDates($month, $year);

    // Calculate first day of month and number of days
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDayOfMonth);
    $dateComponents = getdate($firstDayOfMonth);
    $monthName = $dateComponents['month'];
    $dayOfWeek = $dateComponents['wday'];

    // Start building the HTML table
    $calendar = "<table class='calendar'>";
    $calendar .= "<caption>$monthName $year</caption>";
    $calendar .= "<tr>";

    // Add day headers
    foreach ($daysOfWeek as $day) {
        $calendar .= "<th class='header'>$day</th>";
    }

    // Calculate empty cells for days before the first day of month
    $currentDay = 1;
    if ($dayOfWeek > 0) {
        for ($k=0; $k < $dayOfWeek; $k++) {
            $calendar .= "<td class='empty'></td>";
        }
    }

    // Build calendar rows and cells
    while ($currentDay <= $numberDays) {
        // Start new row at the beginning of each week
        if ($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar .= "</tr><tr>";
        }

        // Create date string for this day
        $dateString = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);
        $isPast = strtotime($dateString) < strtotime(date('Y-m-d'));
        $isBooked = in_array($currentDay, $bookedDates);
        $isToday = ($currentDay == date('j') && $month == date('n') && $year == date('Y'));

        // Determine CSS classes for this cell
        $class = '';
        if ($isToday) $class .= ' today';
        if ($isBooked) $class .= ' booked';
        if ($isPast) $class .= ' past';

        // Create the cell content
        if ($isPast || $isBooked) {
            // Past or booked dates are not clickable
            $calendar .= "<td class='$class'>$currentDay</td>";
        } else {
            // Available dates are clickable links
            $calendar .= "<td class='$class'><a href='?month=$month&year=$year&book=" . $dateString . "' class='book-link'>$currentDay</a></td>";
        }

        $currentDay++;
        $dayOfWeek++;
    }

    // Fill remaining cells in the last row
    if ($dayOfWeek != 7) {
        for ($k=0; $k < (7 - $dayOfWeek); $k++) {
            $calendar .= "<td class='empty'></td>";
        }
    }

    $calendar .= "</tr>";
    return $calendar;
}

// Calculate navigation links for previous/next months
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

?>

<!-- ===========================================
     HTML PAGE STRUCTURE
     =========================================== -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/main.css" rel="stylesheet">
    <title>Book Appointment - Nordic Material Systems</title>
    <style>
        
    </style>
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
            <form method="post">
                <input type="hidden" name="book_date" value="<?php echo $_GET['book']; ?>">
                <input type="text" name="name" placeholder="Company name" required>
                <input type="email" name="email" placeholder="Company email" required>
                <button type="submit">Book Appointment</button>
            </form>
        </div>
    <?php endif; ?>
    
    <?php echo build_calendar($month, $year); ?>
    
    <div style="text-align: center; margin: 20px; color: #666;">
        <p>Green background = Today | Red background = Booked | Gray = Past dates</p>
        <p>Click on available dates to book an appointment.</p>
    </div>
</body>
</html>
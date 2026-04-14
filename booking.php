<?php
require_once 'vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Available booking times
$allTimes = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];

// Get current month/year from URL or use today
$month = max(1, min(12, (int)($_GET['month'] ?? date('n'))));
$year = max(1970, (int)($_GET['year'] ?? date('Y')));

$message = '';
$availableTimes = [];

// If user clicked a date to book, get available times for that date
if (isset($_GET['book'])) {
    $bookedTimes = getBookedTimes($_GET['book']);
    $availableTimes = array_diff($allTimes, $bookedTimes);
    $availableTimes = array_values($availableTimes);
}

// Handle booking form submission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['book_date'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $selectedDate = $_POST['book_date'];
    $selectedTime = $_POST['book_time'] ?? '';
    
    // Validate inputs
    if (empty($name)) {
        $message = "<p class='error'>Please enter a company name.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p class='error'>Please enter a valid email.</p>";
    } elseif (empty($selectedTime)) {
        $message = "<p class='error'>Please select a time.</p>";
    } else {
        // Combine date and time
        $dateTime = date('Y-m-d H:i:s', strtotime($selectedDate . ' ' . $selectedTime));
        
        // Try to book
        if (bookDate($dateTime, $name, $email)) {
            $message = "<p class='success'>✓ Booking confirmed for " . date('F j, Y \a\t H:i', strtotime($dateTime)) . "!</p>";
        } else {
            $message = "<p class='error'>Failed to book - time may have been taken. Try another.</p>";
        }
    }
}


// Generate calendar table
function build_calendar($month, $year) {
    $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $fullyBooked = getBookedDates($month, $year);
    
    // Get partially booked dates (dates with some but not all time slots booked)
    $partiallyBooked = getPartiallyBookedDates($month, $year);
    
    $firstDay = date_create("$year-$month-01");
    $numberDays = (int)$firstDay->format('t');
    $monthName = $firstDay->format('F');
    $startDay = (int)$firstDay->format('w');

    $calendar = "<table class='calendar'><caption>$monthName $year</caption><tr>";
    foreach ($daysOfWeek as $day) {
        $calendar .= "<th>$day</th>";
    }
    $calendar .= '</tr><tr>' . str_repeat("<td class='empty'></td>", $startDay);

    for ($day = 1, $weekDay = $startDay; $day <= $numberDays; $day++, $weekDay++) {
        if ($weekDay === 7) {
            $weekDay = 0;
            $calendar .= '</tr><tr>';
        }

        $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $classes = [];
        
        // Mark today
        if ($dateString === date('Y-m-d')) {
            $classes[] = 'today';
        }
        // Mark fully booked dates
        if (in_array($day, $fullyBooked, true)) {
            $classes[] = 'booked';
        }
        // Mark partially booked dates
        if (in_array($day, $partiallyBooked, true)) {
            $classes[] = 'partial';
        }
        // Mark past dates
        if (strtotime($dateString) < strtotime(date('Y-m-d'))) {
            $classes[] = 'past';
        }
        
        $class = $classes ? ' class="' . implode(' ', $classes) . '"' : '';
        
        if (empty($classes) && strtotime($dateString) >= strtotime(date('Y-m-d'))) {
            $calendar .= "<td><a href='?month=$month&year=$year&book=$dateString' class='book-link'>$day</a></td>";
        } elseif (in_array('partial', $classes) && !in_array('past', $classes)) {
            $calendar .= "<td><a href='?month=$month&year=$year&book=$dateString' class='book-link partial'>$day</a></td>";
        } else {
            $calendar .= "<td$class>$day</td>";
        }
    }

    $calendar .= str_repeat("<td class='empty'></td>", (7 - $weekDay) % 7);
    $calendar .= '</tr></table>';
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
    <link rel="icon" type="image/x-icon" href="/images/Screenshot 2026-04-03 20.27.25 (1).png">
    <title>Nordic Material systems</title>
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
    
    
    <?php if (isset($_GET['book'])): ?>
        <div class="booking-form">
            <h3>Book for <?php echo date('F j, Y', strtotime($_GET['book'])); ?></h3>
            
            <?php $bookedTimes = getBookedTimes($_GET['book']); ?>
            <?php if (count($bookedTimes) < count($allTimes)): ?>
                <form method="post">
                    <input type="hidden" name="book_date" value="<?php echo $_GET['book']; ?>">
                    
                    <label>Select Time:</label>
                    <select name="book_time" required>
                        <option value="">-- Choose time --</option>
                        <?php foreach ($allTimes as $time): ?>
                            <?php if (!in_array($time, $bookedTimes)): ?>
                                <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="text" name="name" placeholder="Company name" required>
                    <input type="email" name="email" placeholder="Company email" required>
                    <button type="submit">Book Now</button>
                </form>
            <?php else: ?>
                <p style="color: red;">All times booked for this date.</p>
                <a href="?month=<?php echo $month; ?>&year=<?php echo $year; ?>">← Back</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php echo build_calendar($month, $year); ?>
    
    <div style="text-align: center; margin: 20px; color: #666;">
        <p>Green background = Today | Yellow background = Some times available | Red background = Fully booked | Gray = Past dates</p>
        <p>Click on available dates to book an appointment.</p>
    </div>
</body>
</html>
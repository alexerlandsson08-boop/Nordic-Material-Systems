<?php
/**
 * Establishes a connection to the MySQL database using credentials from environment variables.
 * @return mysqli The database connection object
 */
function connectToDb() {
    return new mysqli('ostrawebb.se', $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_USER']);
}

/**
 * Retrieves all days in a given month that have at least one booking.
 * Used to visually mark booked dates on the calendar.
 * @param int $month The month number (1-12)
 * @param int $year The year (4-digit)
 * @return array Array of day numbers (1-31) that have bookings
 */
function getBookedDates($month, $year) {
    $db = connectToDb();
    // Set date range to first day of month (00:00:00) to last day of month (23:59:59)
    $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
    $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

    // Query database for all bookings in the date range
    $stmt = $db->prepare('SELECT `date-time` AS booking_datetime FROM bookings WHERE `date-time` BETWEEN ? AND ?');
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Extract day numbers from results
    $dates = [];
    while ($row = $result->fetch_assoc()) {
        // Convert to day number (1-31) and add to array
        $dates[] = (int)date('j', strtotime($row['booking_datetime']));
    }

    $stmt->close();
    $db->close();
    return $dates;
}

/**
 * Creates a new booking in the database with date/time, company name, and email.
 * Prevents double-booking by checking if the time slot is already reserved.
 * @param string $date The booking date and time (format: "Y-m-d H:i:s")
 * @param string $name The company name
 * @param string $email The company email address
 * @return bool True if booking was successful, false if time slot was already booked or error occurred
 */
function bookDate($date, $name, $email) {
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
    
    // Convert date string to proper format and insert booking
    $dateTime = date('Y-m-d H:i:s', strtotime($date));
    $stmt = $db->prepare('INSERT INTO bookings (`date-time`, name, email) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $dateTime, $name, $email);
    $ok = $stmt->execute();
    $stmt->close();
    $db->close();
    return $ok;
}

/**
 * Retrieves all booked time slots for a specific date.
 * Used to show which times are unavailable when user selects a date.
 * @param string $date The date to check (any format acceptable by strtotime)
 * @return array Array of booked times in "H:i" format (e.g., ['09:00', '10:00'])
 */
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



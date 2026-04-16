<?php
/**
 * Establishes a connection to the MySQL database using credentials from environment variables.
 * @return mysqli The database connection object
 */
function connectToDb() {
    $dbName = $_ENV['DB_NAME'] ?? $_ENV['DB_USER'];
    return new mysqli('ostrawebb.se', $_ENV['DB_USER'], $_ENV['DB_PASS'], $dbName);
}

/**
 * Retrieves all days in a given month that are fully booked (all time slots taken).
 * Used to visually mark completely booked dates on the calendar.
 * @param int $month The month number (1-12)
 * @param int $year The year (4-digit)
 * @return array Array of day numbers (1-31) that are fully booked
 */
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

/**
 * Retrieves all days in a given month that are partially booked (some time slots taken).
 * Used to visually mark dates with available time slots on the calendar.
 * @param int $month The month number (1-12)
 * @param int $year The year (4-digit)
 * @return array Array of day numbers (1-31) that are partially booked
 */
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

/**
 * Creates a new booking in the database with date/time, company name, and email.
 * Prevents double-booking by checking if the time slot is already reserved.
 * @param string $date The booking date and time (format: "Y-m-d H:i:s")
 * @param string $name The company name
 * @param string $email The company email address
 * @param int|null $userId Optional user ID for authenticated bookings
 * @return bool True if booking was successful, false if time slot was already booked or error occurred
 */
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



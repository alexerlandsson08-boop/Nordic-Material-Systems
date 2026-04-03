<?php
/**
 * Common functions for the Nordic Material Systems website
 *
 * This file contains database functions, session management, and booking operations.
 */

session_start();

/**
 * Connect to the MySQL database
 *
 * Uses environment variables for database credentials.
 * Database is hosted on ostrawebb.se
 *
 * @return mysqli Database connection object
 */
function connectToDb() {
    $db = new mysqli('ostrawebb.se', $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_USER']);
    return $db;
}

/**
 * Check if user is logged in
 *
 * @return bool True if user has an active session
 */
function isLoggedIn() {
    return isset($_SESSION['loggedIn']);
}

/**
 * Create the bookings table if it doesn't exist
 *
 * Table structure:
 * - id: Auto-incrementing primary key
 * - date: The booking date (YYYY-MM-DD format)
 * - name: Customer's full name
 * - email: Customer's email address
 * - created_at: Timestamp when booking was made
 */
function createBookingsTable() {
    $db = connectToDb();
    $sql = "CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->query($sql);
    $db->close();
}

/**
 * Get all booked dates for a specific month and year
 *
 * @param int $month The month (1-12)
 * @param int $year The year (4-digit)
 * @return array Array of day numbers (1-31) that are booked
 */
function getBookedDates($month, $year) {
    $db = connectToDb();

    // Calculate first and last day of the month
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));

    // Prepare and execute query to get booked dates
    $stmt = $db->prepare("SELECT date FROM bookings WHERE date BETWEEN ? AND ?");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Extract day numbers from the dates
    $bookedDates = [];
    while ($row = $result->fetch_assoc()) {
        $bookedDates[] = date('j', strtotime($row['date']));
    }

    $stmt->close();
    $db->close();
    return $bookedDates;
}

/**
 * Book a date for a customer
 *
 * @param string $date The date to book (YYYY-MM-DD format)
 * @param string $name Customer's name
 * @param string $email Customer's email
 * @return bool True if booking was successful, false otherwise
 */
function bookDate($date, $name, $email) {
    $db = connectToDb();

    // Prepare and execute insert statement
    $stmt = $db->prepare("INSERT INTO bookings (date, name, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $date, $name, $email);
    $result = $stmt->execute();

    $stmt->close();
    $db->close();
    return $result;
}

?>
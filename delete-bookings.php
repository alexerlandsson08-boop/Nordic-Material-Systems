<?php
require_once 'functions.php';

if (isset($_POST['booking_id'])) {
    $bookingId = $_POST['booking_id'];
    $db = connectToDb();
    $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $stmt->close();
    $db->close();
}

$_SESSION['message'] = "<p data-i18n='time.delete'>Tiden har blivit avbokad</p>";

header("Location: choises.php");
exit();

?>
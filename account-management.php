<?php
require_once 'functions.php';

$db = connectToDb();

$statement = $db->prepare("SELECT Cname FROM company_logins WHERE User_type != 'admin'");
$statement->execute();


$result = $statement->get_result();


echo "<table class='account-table'>";
echo "<tr><th>Company users</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Cname']) . "</td>";
    echo "<td><a href='delete_user.php?Cname=" . urlencode($row['Cname']) . "'>Delete</a></td>";
    echo "</tr>";
}

echo "</table>";


$statement->close();
$db->close();

if (isset($_SESSION['message'])) {
    echo "<p class='message'>" . "<strong>" . htmlspecialchars($_SESSION['message']) . "</strong>" . "</p>";
    unset($_SESSION['message']);
}

?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/main.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/Screenshot 2026-04-03 20.27.25 (1).png">
    <title>Account Management - Nordic Material Systems</title>
    <script src="translations.js"></script>
</head>
<body>
    <a href="index.php" data-i18n="login.back-home">Back to home</a>
</body>
</html>


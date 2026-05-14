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

<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/main.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/logga.webp">
    <title>Konto hantering - Nordic Material Systems</title>
    <script src="translations.js"></script>
</head>
<body>
    <a href="index.php" data-i18n="login.back-home">Tillbaka till hem</a>
</body>
</html>


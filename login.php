<?php
require_once 'vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!empty($_SESSION['id'])) {
    header('Location: booking.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $password === '') {
        $message = '<p class="error">Please enter both name and password.</p>';
    } else {
        $db = connectToDb();
        $stmt = $db->prepare('SELECT id, password, email FROM company_logins WHERE name = ?');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['id'] = $row['id'];
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $row['email'];
                $db->close();
                header('Location: booking.php');
                exit;
            }
        }
        $message = '<p class="error">Invalid name or password.</p>';
        $db->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/main.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/Screenshot 2026-04-03 20.27.25 (1).png">
    <title>Login - Nordic Material Systems</title>
</head>
<body>
    <a href="index.php" class="back-link">Back to Home</a>
    <h1>Login</h1>
    <?php echo $message; ?>
    <form method="post" action="login.php">
        <label for="name">name</label>
        <input type="text" id="name" name="name" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Log in</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register</a></p>
</body>
</html>
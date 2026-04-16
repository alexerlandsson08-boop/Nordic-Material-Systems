<?php
require_once 'vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!empty($_SESSION['name'])) {
    header('Location: booking.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($name === '') {
        $message = '<p class="error">Please enter companyname.</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<p class="error">Please enter a valid email.</p>';
    } elseif (strlen($password) < 6) {
        $message = '<p class="error">Password must be at least 6 characters.</p>';
    } elseif ($password !== $passwordConfirm) {
        $message = '<p class="error">Passwords do not match.</p>';
    } else {
        $db = connectToDb();
        $stmt = $db->prepare('SELECT id FROM company_logins WHERE name = ? OR email = ?');
        $stmt->bind_param('ss', $name, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $message = '<p class="error">name or email already exists.</p>';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO company_logins (name, password, email) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $passwordHash, $email);
            if ($stmt->execute()) {
                $_SESSION['id'] = $stmt->insert_id;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $stmt->close();
                $db->close();
                header('Location: booking.php');
                exit;
            }
            $message = '<p class="error">Registration failed. Please try again.</p>';
            $stmt->close();
        }
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
    <title>Register - Nordic Material Systems</title>
</head>
<body>
    <a href="index.php" class="back-link">Back to Home</a>
    <h1>Register</h1>
    <?php echo $message; ?>
    <form method="post" action="register.php">
        <label for="name">name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="password_confirm">Confirm Password</label>
        <input type="password" id="password_confirm" name="password_confirm" required>

        <button type="submit">Create account</button>
    </form>
    <p>Already have an account? <a href="login.php">Log in</a></p>
</body>
</html>
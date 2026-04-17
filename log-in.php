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
    <?php 
     if (isset($_SESSION['message'])) {
    echo "<p style='color: red;'>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
    }
    ?>
    
    
    <form action="login.php" method="post" >
 
        <input type="text" name="name" required placeholder="Company name"> 

        <input type="password" name="password" required placeholder="Password">

        <button type="submit">Log in</button>
    </form>
    
<h2>Skapa konto</h2>

<?php
if (isset($_SESSION['message-same'])) {
    echo "<p style='color: red;'>" . $_SESSION['message-same'] . "</p>";
    unset($_SESSION['message-same']);
}
?>

<form action="register.php" method="post">

<input type="text" name="name" required placeholder="Company name">

<input type="password" name="password" required placeholder="Password">

<input type="password" name="password-vari" required placeholder="Verify password">

<input type="email" name="email" required placeholder="Email">

<button type="submit">Register account</button>

</form>

</body>
</html>


<?php
require_once 'functions.php';

//Döda sessionen (logga ut)
session_destroy();
header('Location: index.php');
exit();
?>
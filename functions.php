<?php
session_start();

//Behövs för .env att fungera
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Anslutningen till databasen
function connectToDb() {
    $db = new mysqli('ostrawebb.se',$_ENV['DB_USER'] , $_ENV['DB_PASS'], $_ENV['DB_USER']);   
    return $db;
}

?>
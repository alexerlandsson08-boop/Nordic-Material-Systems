<?php
session_start();


/**
 * Establishes a connection to the MySQL database using credentials from environment variables.
 * @return mysqli The database connection object
 */
function connectToDb() {
    $db = new mysqli('ostrawebb.se', 'wsp2526_aleerl', 'sabebimu77', 'wsp2526_aleerl');   
    return $db;
}

?>
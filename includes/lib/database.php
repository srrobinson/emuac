<?php

require_once('constants.php');

function getDBConnection()
{
    $dbConnection = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

    if ($dbConnection->connect_errno) {
        die("Failed to connect do DB: " . DB_NAME . " Error was: " . $dbConnection->connect_error);
    }

    return $dbConnection;
}





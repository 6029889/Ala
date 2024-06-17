<?php

function connect_to_database() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hobo2022";


    $conn = new mysqli($servername, $username, $password, $dbname);


    if ($conn->connect_error) {
        die("Kan geen verbinding maken met de database: " . $conn->connect_error);
    }

    return $conn;
}
?>

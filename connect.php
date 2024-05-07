<?php
// Verbinding maken met de database
function connect_to_database() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hobo2022";

    // Verbinding maken met de database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Controleren op fouten
    if ($conn->connect_error) {
        die("Kan geen verbinding maken met de database: " . $conn->connect_error);
    }

    return $conn;
}
?>

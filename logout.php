<?php
// Start de sessie
session_start();

// Verwijder alle sessievariabelen
$_SESSION = array();

// Sessie volledig vernietigen
session_destroy();

// Redirect naar de inlogpagina (index.php in dit geval)
header("Location: index.php");
exit;
?>

<?php
include 'connect.php';

session_start();

// Functie om gebruikersgegevens op te halen
function getUserData($klantNr) {
    $conn = connect_to_database();
    
    
    $stmt = $conn->prepare("SELECT klantnr, aboid, voornaam, tussenvoegsel, achternaam, email, genre FROM klant WHERE klantnr = ?");
    $stmt->bind_param("i", $klantNr);
    $stmt->execute();
    $result = $stmt->get_result();

    $userData = null;
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }

    $stmt->close();
    $conn->close();
    
    return $userData;
}

// Controleer of de gebruiker is ingelogd
if (isset($_SESSION['KlantNr'])) {
    $klantNr = $_SESSION['KlantNr'];

    $userData = getUserData($klantNr);
} else {
    // Redirect naar inlogpagina als de gebruiker niet is ingelogd
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profiel</title>
    <link rel="stylesheet" href="style/home.css" >
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .profile-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .profile-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .profile-container p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <header>
<div class="header-left">
        <img src="images/HOBO_logo.png" alt="">
        <a href="index.php" class="home">Home</a>
    </div>
    </header>
    <div class="profile-container">
        <?php if ($userData): ?>
            <h1>Profiel</h1>
            <p><strong>Klantnummer:</strong> <?php echo htmlspecialchars($userData['klantnr']); ?></p>
            <p><strong>Abonnementnummer:</strong> <?php echo htmlspecialchars($userData['aboid']); ?></p>
            <p><strong>Voornaam:</strong> <?php echo htmlspecialchars($userData['voornaam']); ?></p>
            <p><strong>Tussenvoegsel:</strong> <?php echo htmlspecialchars($userData['tussenvoegsel']); ?></p>
            <p><strong>Achternaam:</strong> <?php echo htmlspecialchars($userData['achternaam']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
            <p><strong>Genre:</strong> <?php echo htmlspecialchars($userData['genre']); ?></p>
        <?php else: ?>
            <p>Geen gegevens gevonden voor deze gebruiker.</p>
    
        <?php endif; ?>
    </div>
</body>
</html>

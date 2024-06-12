<?php
include 'connect.php';

session_start();

// Functie om gebruikersgegevens op te halen
function getUserData($klantNr) {
    $conn = connect_to_database();
    
    $stmt = $conn->prepare("SELECT klantnr, voornaam, tussenvoegsel, achternaam, email, genre FROM klant WHERE klantnr = ?");
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

// Functie om kijkgeschiedenis op te halen
function getWatchHistory($klantNr) {
    $conn = connect_to_database();
    
    $stmt = $conn->prepare("
        SELECT s.d_start, s.d_eind, a.AflTitel, serie.SerieTitel 
        FROM stream s
        INNER JOIN aflevering a ON s.AflID = a.AfleveringID
        INNER JOIN seizoen se ON a.SeizID = se.SeizoenID
        INNER JOIN serie ON se.SerieID = serie.SerieID
        WHERE s.klantID = ?
        ORDER BY s.d_start DESC
    ");
    $stmt->bind_param("i", $klantNr);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    $stmt->close();
    $conn->close();
    
    return $history;
}

// Controleer of de gebruiker is ingelogd
if (isset($_SESSION['KlantNr'])) {
    $klantNr = $_SESSION['KlantNr'];

    $userData = getUserData($klantNr);
    $watchHistory = getWatchHistory($klantNr);
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
    .profile-container {
        background-color: #16213e;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        width: 300px;
        color: white;
    }
    .profile-container h1 {
        font-size: 24px;
        margin-bottom: 20px;
        text-align: center;
    }
    .profile-container p {
        margin: 10px 0;
    }
    .watch-history {
        margin-top: 20px;
    }
    .watch-history h2 {
        font-size: 20px;
        margin-bottom: 10px;
        text-align: center;
    }
    .watch-history ul {
        list-style-type: none;
        padding: 0;
    }
    .watch-history li {
        margin-bottom: 10px;
    }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <img src="images/HOBO_logo.png" alt="Logo">
            <a href="index.php" class="home">Home</a>
        </div>
    </header>
    <div class="profile-container">
        <?php if ($userData): ?>
            <h1>Profiel</h1>
            <p><strong>Voornaam:</strong> <?php echo htmlspecialchars($userData['voornaam']); ?></p>
            <p><strong>Tussenvoegsel:</strong> <?php echo htmlspecialchars($userData['tussenvoegsel']); ?></p>
            <p><strong>Achternaam:</strong> <?php echo htmlspecialchars($userData['achternaam']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
            <p><strong>Genre:</strong> <?php echo htmlspecialchars($userData['genre']); ?></p>

            <div class="watch-history">
                <h2>Kijkgeschiedenis</h2>
                <ul>
                    <?php if (!empty($watchHistory)): ?>
                        <?php foreach ($watchHistory as $entry): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($entry['SerieTitel']); ?></strong>: 
                                <?php echo htmlspecialchars($entry['AflTitel']); ?> 
                                (Gestart op: <?php echo htmlspecialchars($entry['d_start']); ?>)
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Geen kijkgeschiedenis gevonden.</li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php else: ?>
            <p>Geen gegevens gevonden voor deze gebruiker.</p>
        <?php endif; ?>
    </div>
</body>
</html>

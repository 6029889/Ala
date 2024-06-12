<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['KlantNr'])) {
    die("Je moet ingelogd zijn om deze pagina te bekijken.");
}

if (!isset($_GET['serie_id']) || !isset($_GET['episode_id']) || !isset($_SESSION['KlantNr'])) {
    die("SerieID, AfleveringID of klantnr is niet ingesteld.");
}

$userID = $_SESSION['KlantNr']; // Haal klantnummer op uit de URL
$serieID = $_GET['serie_id'];
$episodeID = $_GET['episode_id'];

// Verbinding maken met de database
$conn = connect_to_database();

if ($conn->connect_error) {
    die("Kan geen verbinding maken met de database: " . $conn->connect_error);
}

// Nieuwe stream invoegen in de database
$insert_query = "INSERT INTO stream (klantID,  AflID, d_start) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("ii", $userID, $episodeID);
$stmt->execute();
$stmt->close();
$conn->close();

// Hardcode de URL van de video hier
$video_url = "path_to_your_video.mp4"; // Vervang dit met het pad naar je video
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/play.css">
    <title>Aflevering Afspelen</title>
</head>
<body>
<header>
    <div class="header-left">
        <img src="images/HOBO_logo.png" alt="Logo">
        <a href="index.php" class="home">Home</a>
    </div>
    <div class="header-right">
        <a href="logout.php" class="logout-link">Uitloggen</a>
    </div>
</header>

<div class="video-container">
    <h2>Aflevering <?php echo $episodeID; ?></h2>
    <video width="800" controls>
        <source src="<?php echo $video_url; ?>" type="video/mp4">
        Uw browser ondersteunt de video tag niet.
    </video>
</div>

</body>
</html>

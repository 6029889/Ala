<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['KlantNr']) && !isset($_SESSION['id'])) {
    die("Je moet ingelogd zijn om deze pagina te bekijken.");
}

if (!isset($_GET['serie_id']) || !isset($_GET['episode_id'])) {
    die("SerieID of AfleveringID is niet ingesteld.");
}
if (isset($_SESSION['KlantNr'])) {
    $klantID = $_SESSION['KlantNr'];
}

$serieID = $_GET['serie_id'];
$episodeID = $_GET['episode_id'];

$conn = connect_to_database();

if ($conn->connect_error) {
    die("Kan geen verbinding maken met de database: " . $conn->connect_error);
}

$episode_query = "
    SELECT a.AflTitel
    FROM aflevering a
    WHERE a.AfleveringID = ?
";
$stmt = $conn->prepare($episode_query);
$stmt->bind_param("i", $episodeID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $episode = $result->fetch_assoc();
    $episodeTitle = $episode['AflTitel'];
} else {
    die("Aflevering niet gevonden.");
}

$stmt->close();


function insertintoStream($klantID, $AflID, $d_start, $d_eind)
{
    global $conn;
    $insert_query = "INSERT INTO stream (klantID, AflID, d_start, d_eind) VALUES (?, ?, NOW(), NOW() + INTERVAL 1 HOUR)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ii", $klantID, $AflID);
    $stmt->execute();
    $stmt->close();
}


if (!isset($_SESSION['userType']) || ($_SESSION['userType'] !== 'admin' && $_SESSION['userType'] !== 'content')) {
    insertintoStream($klantID, $episodeID, date("Y-m-d H:i:s"), date("Y-m-d H:i:s", strtotime("+1 hour")));
}

$video_url = "path_to_your_video.mp4";


$series = [
    'SerieBeschrijving' => 'A chemistry teacher diagnosed with inoperable lung cancer turns to manufacturing and selling methamphetamine with a former student in order to secure his family\'s future.',
];

$actors = [
    'Bryan Cranston',
    'Aaron Paul',
    'Anna Gunn',
    'Betsy Brandt',
    'RJ Mitte',
    'Dean Norris',
    'Bob Odenkirk',
    'Jonathan Banks',
    'Giancarlo Esposito',
];
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
    <video width="800" controls>
        <source src="<?php echo $video_url; ?>" type="video/mp4">
        Uw browser ondersteunt de video tag niet.
    </video>
</div>

<div class="description-container">
    <h2><?php echo htmlspecialchars($episodeTitle); ?></h2>
    <h2>Series Beschrijving</h2>
    <p><?php echo htmlspecialchars($series['SerieBeschrijving']); ?></p>
    <div class="actors">
        <h3>Acteurs:</h3>
        <ul>
            <?php foreach ($actors as $actor): ?>
                <li><?php echo htmlspecialchars($actor); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

</body>
</html>

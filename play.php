<?php
include 'connect.php';

session_start();

if (!isset($_SESSION['KlantNr']) && !isset($_SESSION['id'])) {
    die("Je moet ingelogd zijn om deze pagina te bekijken.");
}



if (!isset($_GET['serie_id'])) {
    die("SerieID is niet ingesteld.");
}

$serieID = $_GET['serie_id'];

$conn = connect_to_database();

if ($conn->connect_error) {
    die("Kan geen verbinding maken met de database: " . $conn->connect_error);
}


$seasons_query = "
    SELECT s.SeizoenID, s.Rang, s.Jaar, s.IMDBRating, a.AfleveringID, a.AflTitel, a.Duur 
    FROM seizoen s 
    LEFT JOIN aflevering a ON s.SeizoenID = a.SeizID 
    WHERE s.SerieID = ?
";
$stmt = $conn->prepare($seasons_query);
$stmt->bind_param("i", $serieID);
$stmt->execute();
$seasons_result = $stmt->get_result();

$seasons = [];
while ($row = $seasons_result->fetch_assoc()) {
    $seasons[$row['SeizoenID']]['details'] = [
        'Rang' => $row['Rang'],
        'Jaar' => $row['Jaar'],
        'IMDBRating' => $row['IMDBRating']
    ];
    if (!empty($row['AfleveringID'])) {
        $seasons[$row['SeizoenID']]['episodes'][] = [
            'AfleveringID' => $row['AfleveringID'],
            'AflTitel' => $row['AflTitel'],
            'Duur' => $row['Duur']
        ];
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/play.css">
    <title>Seizoenen en Afleveringen</title>
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

<div class="seasons-container">
    <?php 
    $seasonNumber = 1;
    foreach ($seasons as $seizoenID => $season): ?>
        <div class="season">
            <h2>Seizoen <?php echo $seasonNumber++; ?></h2>
            <ul>
                <?php if (!empty($season['episodes'])): ?>
                  
                    <?php foreach ($season['episodes'] as $episode): ?>
                        <li>
                            <a href="play_episode.php?serie_id=<?php echo $serieID; ?>&episode_id=<?php echo $episode['AfleveringID']?>">
                                <?php echo $episode['AflTitel']; ?> - <?php echo "Duur " . $episode['Duur'] . " Minuten"; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
               
                    <li>Geen afleveringen gevonden voor dit seizoen.</li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>

<?php
include 'connect.php';

session_start();

// Check if SerieID is set
if (!isset($_GET['serie_id'])) {
    die("SerieID is niet ingesteld.");
}

$serieID = $_GET['serie_id'];

// Connect to the database
$conn = connect_to_database();

if ($conn->connect_error) {
    die("Kan geen verbinding maken met de database: " . $conn->connect_error);
}

// Fetch seasons for the given SerieID
$seasons_query = "SELECT * FROM seizoen WHERE SerieID = ?";
$stmt = $conn->prepare($seasons_query);
$stmt->bind_param("i", $serieID);
$stmt->execute();
$seasons_result = $stmt->get_result();

$seasons = [];
while ($row = $seasons_result->fetch_assoc()) {
    $seasons[] = $row;
}

$stmt->close();

$episodes = [];

foreach ($seasons as $season) {
    // Fetch episodes for each season
    $episodes_query = "SELECT * FROM aflevering WHERE seizID = ?";
    $stmt = $conn->prepare($episodes_query);
    $stmt->bind_param("i", $season['SeizID']);
    if (!$stmt->execute()) {
        die("Error: " . $conn->error); // Error handling for SQL execution
    }
    $episodes_result = $stmt->get_result();

    while ($row = $episodes_result->fetch_assoc()) {
        $episodes[$season['SeizID']][] = $row;
        
    }

    $stmt->close();
}

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
    foreach ($seasons as $season): ?>
        <div class="season">
        <h2>Seizoen <?php echo $seasonNumber++; ?></h2>
            <ul>
                <?php if (isset($episodes[$season['SeizID']])): ?>
                    <?php foreach ($episodes[$season['SeizID']] as $episode): ?>
                        <li><?php echo $episode['AfleveringTitel']; ?> - <?php echo $episode['Duur']; ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php var_dump($seasons); ?>
                    <li>Geen afleveringen gevonden voor dit seizoen.</li>
                <?php endif; ?>
            </ul>
        </div>

    <?php endforeach; ?>

</div>

</body>
</html>

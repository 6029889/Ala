<?php
if (!isset($_GET['episode_id'])) {
    die("Aflevering ID is niet ingesteld.");
}

$episodeID = $_GET['episode_id'];

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

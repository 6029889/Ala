<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/play.css">
    <title>video</title>
</head>
<body>
<header>
    <div class="header-left">
        <img src="images/HOBO_logo.png" alt="">
        <a href="index.php" class="home">Home</a>
    </div>
    <div class="header-right">
        <a href="logout.php" class="logout-link">Uitloggen</a>
    </div>
</header>
<video controls>
  <source src="images/video-ala.mp4" type="video/mp4">
</video>

<?php
$serieinfo = array(
    'SerieTitel' => 'Breaking Bad',
    'SerieBeschrijving' => 'A chemistry teacher diagnosed with inoperable lung cancer turns to manufacturing and selling methamphetamine with a former student in order to secure his family\'s future.',
);
?>

<div class="serie-info">
  <h1 class="serie-title"><?php echo htmlspecialchars($serieinfo['SerieTitel']); ?></h1>
  <p class="serie-description"><?php echo htmlspecialchars($serieinfo['SerieBeschrijving']); ?></p>
</div>

</body>
</html>
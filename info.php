<?php

$series = [
    'SerieTitel' => 'Breaking Bad',
    'SerieBeschrijving' => 'A chemistry teacher diagnosed with inoperable lung cancer turns to manufacturing and selling methamphetamine with a former student in order to secure his family\'s future.',
    'TrailerURL' => 'https://www.youtube.com/embed/HhesaQXLuRY',
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/info.css">
    <title><?php echo htmlspecialchars($series['SerieTitel']); ?></title>
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
    <div class="container">
        <h1><?php echo htmlspecialchars($series['SerieTitel']); ?></h1>
        <p><?php echo htmlspecialchars($series['SerieBeschrijving']); ?></p>
        <?php if (!empty($series['TrailerURL'])): ?>
            <div class="trailer">
                <h2>Trailer</h2>
                <iframe width="560" height="315" src="<?php echo htmlspecialchars($series['TrailerURL']); ?>" frameborder="0" allowfullscreen></iframe>
            </div>
        <?php endif; ?>
        <div class="actors">
            <h2>Actors</h2>
            <ul>
                <?php foreach ($actors as $actor): ?>
                    <li><?php echo htmlspecialchars($actor); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>

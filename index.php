<?php
include 'connect.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $gebruikersnaam = $_POST['gebruikersnaam'];
    $wachtwoord = $_POST['wachtwoord'];

    $conn = connect_to_database();

  
    $stmt = $conn->prepare("SELECT KlantNr FROM klant WHERE Email = ? AND password = ?");
    $stmt->bind_param("ss", $gebruikersnaam, $wachtwoord);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($klantNr);
        $stmt->fetch();
        $_SESSION['KlantNr'] = $klantNr;
        $_SESSION['userType'] = 'klant';
    } else {
       
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $gebruikersnaam, $wachtwoord);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($userID);
            $stmt->fetch();
            $_SESSION['id'] = $userID;
            $_SESSION['userType'] = 'admin';
        } else {
            $loginError = "Ongeldige gebruikersnaam of wachtwoord.";
        }
    }

    $stmt->close();
    $conn->close();
}


?>

<?php if (isset($_SESSION['KlantNr']) || isset($_SESSION['id'])): ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/home.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Welkom op de startpagina</title>
</head>
<body>
<header>
    <div class="header-left">
        <img src="images/HOBO_logo.png" alt="">
        <a href="index.php" class="home">Home</a>
    </div>
    <div class="header-right">
        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="search-container">
                <input type="text" id="searchTerm" name="searchTerm" required>
                <a href="search.php"><img src="images/search.png" alt="" class="search" id="searchIcon"></a>
            </div>
        </form>
        <a href="logout.php" class="logout-link">Uitloggen</a>
        <a href="profiel.php" class="profile-link">Profiel</a>
    </div>
</header>
<div>
<?php
function getLastWatchedSeries($klantNr) {
    $conn = connect_to_database();
    $sql = "
        SELECT serie.SerieID, serie.SerieTitel, MAX(s.d_start) as LastWatched
        FROM stream s
        INNER JOIN aflevering a ON s.AflID = a.AfleveringID
        INNER JOIN seizoen se ON a.SeizID = se.SeizoenID
        INNER JOIN serie ON se.SerieID = serie.SerieID
        WHERE s.klantID = ?
        GROUP BY serie.SerieID, serie.SerieTitel
        ORDER BY LastWatched DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $klantNr);
    $stmt->execute();
    $result = $stmt->get_result();

    $series = [];
    while ($row = $result->fetch_assoc()) {
        $series[] = $row;
    }

    $stmt->close();
    $conn->close();
    
    return $series;
}
function displaySeries($searchTerm = "") {
    $conn = connect_to_database();

    if ($conn->connect_error) {
        die("Kan geen verbinding maken met de database: " . $conn->connect_error);
    }

 
    $genres_query = "SELECT DISTINCT Genre.GenreID, Genre.GenreNaam FROM Genre INNER JOIN serie_genre ON Genre.GenreID = serie_genre.GenreID INNER JOIN serie ON serie.SerieID = serie_genre.SerieID WHERE serie.Actief = 1";
    $genres_result = $conn->query($genres_query);

    if ($genres_result->num_rows > 0) {
        while ($genre_row = $genres_result->fetch_assoc()) {
            $genreID = $genre_row['GenreID'];
            $genreNaam = $genre_row['GenreNaam'];

            $sql = "SELECT serie.SerieID, serie.SerieTitel, serie.IMDBLink FROM serie INNER JOIN serie_genre ON serie.SerieID = serie_genre.SerieID WHERE serie_genre.GenreID = ? AND serie.Actief = 1";
            if (!empty($searchTerm)) {
                $sql .= " AND serie.SerieTitel LIKE ?";
            }
            $sql .= " LIMIT 20";

            $stmt = $conn->prepare($sql);

            if (!empty($searchTerm)) {
                $searchParam = '%' . $searchTerm . '%';
                $stmt->bind_param("is", $genreID, $searchParam);
            } else {
                $stmt->bind_param("i", $genreID);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<div class='series-container-wrapper'>";
                $numSeries = $result->num_rows;
              
                echo "<h2>$genreNaam</h2>";

                echo "<div class='series-container' id='series-container-$genreID'>";
                if ($numSeries >= 9) {

                    echo "<button class='scrollLeftButton' data-container-id='series-container-$genreID'><i class='fas fa-angle-left'></i></button>";
                }
                while ($row = $result->fetch_assoc()) {
                    $serieIDWithoutZeroes = sprintf('%05d', $row['SerieID']);
                    $imagePath = "images/images/fotos/" . $serieIDWithoutZeroes . ".jpg";
                    echo "<div class='series-card' data-serie-id='" . $row['SerieID'] . "' style='text-decoration: none; color: inherit; cursor: pointer;'>";
                    if (file_exists($imagePath)) {
                        echo "<img src='" . $imagePath . "' alt='" . $row['SerieTitel'] . "' style='max-width: 100px; margin-bottom: 10px;'>";
                    }
                    echo "<h3>" . $row['SerieTitel'] . "</h3>";
                    echo "</div>";
                    $serieID = $row['SerieID'];
                }    
                if ($numSeries >= 9) {

                    echo "<button class='scrollRightButton' data-container-id='series-container-$genreID'><i class='fas fa-angle-right'></i></button>";
                }
                echo "</div>";
               
                echo "</div>";
            }
            
            $stmt->close();
        }
    } else {
        echo "<div class='series-container'>";
        echo "Geen series gevonden.";
        echo "</div>";
    }

    $conn->close();
}



  
$series = [
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
<?php
if (isset($_SESSION['KlantNr'])) {
    $klantNr = $_SESSION['KlantNr'];
    $lastWatchedSeries = getLastWatchedSeries($klantNr);

    if (!empty($lastWatchedSeries)) {
        echo "<div class='series-container-wrapper'>";
        echo "<h2>Laatst Bekeken Series</h2>";
        echo "<div class='series-container' id='last-watched-series-container'>";
        
        $numSeries = count($lastWatchedSeries);
        
        if ($numSeries >= 9) {
            echo "<button class='scrollLeftButton' data-container-id='last-watched-series-container'><i class='fas fa-angle-left'></i></button>";
        }
        
        foreach ($lastWatchedSeries as $series) {
            $serieIDWithoutZeroes = sprintf('%05d', $series['SerieID']);
            $imagePath = "images/images/fotos/" . $serieIDWithoutZeroes . ".jpg";
            echo "<div class='series-card' data-serie-id='" . $series['SerieID'] . "' style='text-decoration: none; color: inherit; cursor: pointer;'>";
            if (file_exists($imagePath)) {
                echo "<img src='" . $imagePath . "' alt='" . $series['SerieTitel'] . "' style='max-width: 100px; margin-bottom: 10px;'>";
            }
            echo "<h3>" . $series['SerieTitel'] . "</h3>";
            echo "</div>";
        }
        
        if ($numSeries >= 9) {
            echo "<button class='scrollRightButton' data-container-id='last-watched-series-container'><i class='fas fa-angle-right'></i></button>";
        }
        
        echo "</div>";
        echo "</div>";
    }
}
displaySeries();
?>

</div>

<div id="info-container">
    <h1><?php echo htmlspecialchars($series['SerieTitel'] ?? ''); ?></h1>
    <p><?php echo htmlspecialchars($series['SerieBeschrijving'] ?? ''); ?></p>
    <?php if (!empty($series['TrailerURL'])): ?>
        <div class="trailer">
            <h2>Trailer</h2>
            <iframe width="200" height="100" src="<?php echo htmlspecialchars($series['TrailerURL']); ?>" frameborder="0" allowfullscreen></iframe>
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
   <button id="watch-video-button">Bekijk serie</button>
    <button id="less-button">Minder weergeven</button>
</div>




<script>

    document.querySelectorAll('.scrollRightButton').forEach(function(button) {
        button.addEventListener('click', function() {
            const wrapper = this.closest('.series-container-wrapper');
            console.log(wrapper);
            wrapper.scrollBy({
                left: 500,
                behavior: 'smooth'
            });
        });
    });

    document.querySelectorAll('.scrollLeftButton').forEach(function(button) {
        button.addEventListener('click', function() {
            
            const wrapper = this.closest('.series-container-wrapper');
            wrapper.scrollBy({
                left: -500,
                behavior: 'smooth'
            });
        });
    });
    document.querySelectorAll('.series-card').forEach(function(card) {
        card.addEventListener('click', function() {
            var infoContainer = document.getElementById('info-container');
            var wrapperContainer = card.closest('.series-container-wrapper');

            wrapperContainer.appendChild(infoContainer);

            document.querySelector('#info-container h1').textContent = card.querySelector('h3').textContent;

            infoContainer.style.display = 'block';
        });
    });
  
    document.addEventListener('DOMContentLoaded', (event) => {
    const watchButton = document.getElementById('watch-video-button');


    document.querySelectorAll('.series-card').forEach(card => {
        card.addEventListener('click', function() {
            const serieID = this.getAttribute('data-serie-id');
            if (serieID) {
                
                watchButton.setAttribute('data-serie-id', serieID);
               
            }
        });
    });


    watchButton.addEventListener('click', function() {
        const serieID = watchButton.getAttribute('data-serie-id');
        if (serieID) {

            window.location.href = 'play.php?serie_id=' + serieID;
        }
    });
});

    document.getElementById('less-button').addEventListener('click', function() {
        document.getElementById('info-container').style.display = 'none';
    });

</script>
</body>
</html>
<?php else: ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body class="bodyinlog">
<div class="container">
    <header>
        <div class="header-left">
            <img src="images/HOBO_logo.png" alt="">
        </div>
        <div class="header-right">
            <button onclick="window.location.href='register.php'">Sign up</button>
        </div>
    </header>
    <div>
        <h1>Log In</h1>
        <form method="post" action="">
            <label for="gebruikersnaam">Email:</label>
            <input type="text" name="gebruikersnaam" id="gebruikersnaam" required><br>

            <label for="wachtwoord">Password:</label>
            <input type="password" name="wachtwoord" id="wachtwoord" required><br>

            <input type="submit" name="login" value="Log In">
        </form>
    </div>
</div>
</body>
</html>
<?php endif; ?>
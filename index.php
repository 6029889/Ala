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
    } else {
        $loginError = "Ongeldige gebruikersnaam of wachtwoord.";
    }

    $stmt->close();
    $conn->close();
}

?>

<?php if (isset($_SESSION['KlantNr'])): ?>
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
                <img src="images/search.png" alt="" class="search" id="searchIcon">
            </div>
        </form>
        <a href="logout.php" class="logout-link">Uitloggen</a>
        <a href="profiel.php" class="profile-link">Profiel</a>
    </div>
</header>
<div>
<?php
function displaySeries($searchTerm = "") {
    $conn = connect_to_database();

    if ($conn->connect_error) {
        die("Kan geen verbinding maken met de database: " . $conn->connect_error);
    }

    // Haal alle unieke genres op
    $genres_query = "SELECT DISTINCT Genre.GenreID, Genre.GenreNaam FROM Genre INNER JOIN serie_genre ON Genre.GenreID = serie_genre.GenreID INNER JOIN serie ON serie.SerieID = serie_genre.SerieID WHERE serie.Actief = 1";
    $genres_result = $conn->query($genres_query);

    if ($genres_result->num_rows > 0) {
        while ($genre_row = $genres_result->fetch_assoc()) {
            $genreID = $genre_row['GenreID'];
            $genreNaam = $genre_row['GenreNaam'];

            // Query om series per genre op te halen
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
              
                echo "<h2>$genreNaam</h2>"; // Toon het genre bovenaan

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

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['searchTerm'])) {
    $searchTerm = $_GET['searchTerm'];
    displaySeries($searchTerm);
} else {
    displaySeries();
}

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
</div>

<div id="info-container" style="display: none;">
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
    <button id="watch-video-button" onclick="window.location.href = 'play.php';">Video kijken</button>
    <button id="less-button">Minder weergeven</button>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchIcon = document.getElementById("searchIcon");
    const searchTermInput = document.getElementById("searchTerm");
    let searchVisible = false;

    searchIcon.addEventListener("click", function() {
        searchTermInput.style.display = searchVisible ? "none" : "inline-block";
        if (!searchVisible) {
            searchTermInput.focus();
        }
        searchVisible = !searchVisible;
    });

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
                document.getElementById('info-container').style.display = 'block';
            });
        });

        document.getElementById('less-button').addEventListener('click', function() {
            document.getElementById('info-container').style.display = 'none';
       });
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
            <button>Sign up</button>
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
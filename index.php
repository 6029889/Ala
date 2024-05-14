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
        $_SESSION['KlantNr'] = $gebruikersnaam;
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
    <title>Welkom op de startpagina</title>
</head>
<body>
<header>
      <div class="header-left">
          <img src="images/HOBO_logo.png" alt="">
          <a href="index.php" class="home">Home</a>
      </div>
      <div class="header-right">
        <img src="images/search.png" alt="" class="search">
        <a href="logout.php" class="logout-link">Uitloggen</a>
      </div>

</header>
<div>
<?php
function displaySeries() {
    // Maak verbinding met de database
    $conn = connect_to_database();

    // Controleer of de verbinding succesvol is voordat we doorgaan
    if ($conn->connect_error) {
        die("Kan geen verbinding maken met de database: " . $conn->connect_error);
    }

    // Query om actieve series op te halen
    $sql = "SELECT SerieID, SerieTitel, IMDBLink FROM serie WHERE Actief = 1 LIMIT 4";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Output data van elke rij
        echo "<div class='series-container'>";
        while ($row = $result->fetch_assoc()) {
            echo "<div class='series-card'>";
            $imagePath = "images/images/fotos/" . $row['SerieID'] . ".jpg";
            if (file_exists($imagePath)) {
                echo "<img src='" . $imagePath . "' alt='" . $row['SerieTitel'] . "' style='max-width: 100px; margin-bottom: 10px;'>";
            }
            echo "<h3>" . $row['SerieTitel'] . "</h3>";
            echo "<p><a href='" . $row['IMDBLink'] . "' target='_blank'>IMDB-pagina</a></p>";
            echo "</div>";
        }
        echo "</div>"; // Close series-container
    } else {
        echo "Geen series gevonden.";
    }

    // Sluit de verbinding met de database
    $conn->close();
}
?>

<?php displaySeries(); ?>
</div>
</body>
</html>


 <!-- Dit is de hoofdpagina niet ingelogd. -->
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
</body>
</html>
<?php endif; ?>

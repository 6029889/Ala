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
    </div>
</header>
<div>
<?php
function displaySeries($searchTerm = "") {
    $conn = connect_to_database();

    if ($conn->connect_error) {
        die("Kan geen verbinding maken met de database: " . $conn->connect_error);
    }

    $sql = "SELECT SerieID, SerieTitel, IMDBLink FROM serie WHERE Actief = 1";
    if (!empty($searchTerm)) {
        $sql .= " AND SerieTitel LIKE ?";
    }
    $sql .= " LIMIT 14";

    $stmt = $conn->prepare($sql);
    if (!empty($searchTerm)) {
        $searchParam = '%' . $searchTerm . '%';
        $stmt->bind_param("s", $searchParam);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='series-container-wrapper'>";
        echo "<div class='series-container'>";
        while ($row = $result->fetch_assoc()) {
            $serieIDWithoutZeroes = sprintf('%05d', $row['SerieID']);    
            echo "<div class='series-card'>";
            $imagePath = "images/images/fotos/" . $serieIDWithoutZeroes . ".jpg";
            if (file_exists($imagePath)) {
                echo "<a href='" . $row['IMDBLink'] . "' target='_blank'> <img src='" . $imagePath . "' alt='" . $row['SerieTitel'] . "' style='max-width: 100px; margin-bottom: 10px;'></a>";
            }
            echo "<h3>" . $row['SerieTitel'] . "</h3>";
            echo "</div>";
        }
        echo "</div>"; 
        echo "</div>";

        echo "<button id='scrollLeftButton'>
        <i class='fas fa-angle-left'></i>
      </button>";

        echo "<button id='scrollRightButton'>
        <i class='fas fa-angle-right'></i>
      </button>";

    } else {
        echo "Geen series gevonden.";
    }    

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['searchTerm'])) {
    $searchTerm = $_GET['searchTerm'];
    displaySeries($searchTerm);
} else {
    displaySeries();
}
?>



</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchIcon = document.getElementById("searchIcon");
        const searchTermInput = document.getElementById("searchTerm");
        let searchVisible = false;

        searchIcon.addEventListener("click", function() {
            if (!searchVisible) {
                searchTermInput.style.display = "inline-block";
                searchTermInput.focus();
            } else {
                searchTermInput.style.display = "none";
            }
            searchVisible = !searchVisible;
        });
    });

    window.addEventListener('DOMContentLoaded', (event) => {
        const seriesContainers = document.querySelectorAll('.series-container');
        seriesContainers.forEach(container => {
            container.classList.add('scroll-container');
        });
    });
    document.addEventListener('DOMContentLoaded', function () {
        const scrollRightButton = document.getElementById('scrollRightButton');
        const scrollLeftButton = document.getElementById('scrollLeftButton');
        const wrapper = document.querySelector('.series-container-wrapper');

        scrollRightButton.addEventListener('click', function () {
            wrapper.scrollBy({
                left: 200,
                behavior: 'smooth'
            });
        });

        scrollLeftButton.addEventListener('click', function () {
            wrapper.scrollBy({
                left: -150,
                behavior: 'smooth'
            });
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
</body>
</html>
<?php endif; ?>

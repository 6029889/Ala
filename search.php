<?php
include 'connect.php';

function displaySeries($searchTerm = "") {
    $conn = connect_to_database();

    if ($conn->connect_error) {
        die("Kan geen verbinding maken met de database: " . $conn->connect_error);
    }

    // Query om alle actieve series op te halen
    $sql = "SELECT SerieID, SerieTitel, IMDBLink FROM serie WHERE Actief = 1";
    if (!empty($searchTerm)) {
        $sql .= " AND SerieTitel LIKE ?";
    }
    $sql .= " LIMIT 50"; // Limiteer het aantal resultaten om performance te verbeteren

    $stmt = $conn->prepare($sql);

    if (!empty($searchTerm)) {
        $searchParam = '%' . $searchTerm . '%';
        $stmt->bind_param("s", $searchParam);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    echo "<div class='series-container'>";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $serieIDWithoutZeroes = sprintf('%05d', $row['SerieID']);
            $imagePath = "images/images/fotos/" . $serieIDWithoutZeroes . ".jpg";
            echo "<a href='play.php?serie_id=" . $row['SerieID'] . "' class='series-link'>";
            echo "<div class='series-card' data-serie-id='" . $row['SerieID'] . "'>";
            if (file_exists($imagePath)) {
                echo "<img src='" . $imagePath . "' alt='" . $row['SerieTitel'] . "'>";
            }
            echo "<h3>" . $row['SerieTitel'] . "</h3>";
            echo "</div>";
            echo "</a>";
        }
    } else {
        echo "Geen series gevonden.";
    }
    echo "</div>";

    $stmt->close();
    $conn->close();
}


?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/search.css">
    <title>Series Zoeken</title>
</head>
<body>
<header>
    <div class="header-left">
        <img src="images/HOBO_logo.png" alt="Logo">
        <a href="index.php" class="home">Home</a>
    </div>
    <div class="header-right">
        <form method="GET" action="search.php">
            <input type="text" name="searchTerm" placeholder="Zoek series..." value="<?php echo isset($_GET['searchTerm']) ? $_GET['searchTerm'] : ''; ?>">
            <button type="submit">Zoeken</button>
        </form>
        <a href="logout.php" class="logout-link">Uitloggen</a>
    </div>
</header>

<main>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['searchTerm'])) {
        $searchTerm = $_GET['searchTerm'];
        displaySeries($searchTerm);
    } else {
        displaySeries();
    }
    ?>
</main>
</body>
</html>

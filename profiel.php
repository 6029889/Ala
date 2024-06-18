<?php
include 'connect.php';

session_start();

function getAlluserdata($page, $itemsPerPage){
    $conn = connect_to_database();
    $stmt = $conn->prepare("SELECT klantnr, voornaam, tussenvoegsel, achternaam, email, genre FROM klant");
    $stmt->execute();
    $result = $stmt->get_result();

    $userData = [];
    while ($row = $result->fetch_assoc()) {
        $userData[] = $row;
    }

    $stmt->close();
    $conn->close();
    
    return $userData;

}

function getAllSeries($page, $itemsPerPage) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("SELECT SerieID, SerieTitel, Actief FROM serie");
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
function countAllSeries() {
    $conn = connect_to_database();
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM serie");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'];

    $stmt->close();
    $conn->close();
    
    return $total;
}


function countAllUsers(){
    $conn = connect_to_database();
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM klant");
    $stmt->execute();
    $result = $stmt->get_result();

    $total = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total = $row['total'];
    }

    $stmt->close();
    $conn->close();
    
    return $total;

}

function getUserData($klantNr) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("SELECT klantnr, voornaam, tussenvoegsel, achternaam, email, genre FROM klant WHERE klantnr = ?");
    $stmt->bind_param("i", $klantNr);
    $stmt->execute();
    $result = $stmt->get_result();

    $userData = null;
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }

    $stmt->close();
    $conn->close();
    
    return $userData;
}

function getAdminData($userID) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    $adminData = null;
    if ($result->num_rows > 0) {
        $adminData = $result->fetch_assoc();
    }

    $stmt->close();
    $conn->close();
    
    return $adminData;
}

function updateUserData($klantNr, $voornaam, $tussenvoegsel, $achternaam, $email, $genre) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("UPDATE klant SET voornaam = ?, tussenvoegsel = ?, achternaam = ?, email = ?, genre = ? WHERE klantnr = ?");
    $stmt->bind_param("sssssi", $voornaam, $tussenvoegsel, $achternaam, $email, $genre, $klantNr);
    $stmt->execute();

    $success = $stmt->affected_rows > 0;

    $stmt->close();
    $conn->close();
    
    return $success;
}

function getWatchHistory($klantNr) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("
        SELECT s.d_start, s.d_eind, a.AflTitel, serie.SerieTitel 
        FROM stream s
        INNER JOIN aflevering a ON s.AflID = a.AfleveringID
        INNER JOIN seizoen se ON a.SeizID = se.SeizoenID
        INNER JOIN serie ON se.SerieID = serie.SerieID
        WHERE s.klantID = ?
        ORDER BY s.d_start DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $klantNr);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    $stmt->close();
    $conn->close();
    
    return $history;
}

$klantNr = isset($_SESSION['KlantNr']) ? $_SESSION['KlantNr'] : null;
$isContentmanager = isset($_SESSION['userType']) && $_SESSION['userType'] == 'content';
$isAdmin = isset($_SESSION['userType']) && $_SESSION['userType'] == 'admin';



$userData = null;
$allSeries = [];

if ($klantNr !== null) {
    $userData = getUserData($klantNr);
    $watchHistory = getWatchHistory($klantNr);
    $todayWatchTime = getTodayWatchTime($klantNr);
}

$itemsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalSeries = countAllSeries();
$totalUsers = countAllUsers();
$totalPagesSeries = ceil($totalSeries / $itemsPerPage);
$totalPagesUsers = ceil($totalUsers / $itemsPerPage);

if ($isContentmanager) {
    $allSeries = getAllSeries($page, $itemsPerPage);
}
if ($isAdmin) {
    $userData = getAllUserData($page, $itemsPerPage);
}


if (isset($_POST['submit'])) {
    $voornaam = $_POST['voornaam'];
    $tussenvoegsel = $_POST['tussenvoegsel'];
    $achternaam = $_POST['achternaam']; 
    $email = $_POST['email'];
    $genre = $_POST['genre'];

    $success = updateUserData($klantNr, $voornaam, $tussenvoegsel, $achternaam, $email, $genre);

    if ($success) {
        $userData = getUserData($klantNr);
    }
}

function getTodayWatchTime($klantNr) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("
        SELECT SUM(TIMESTAMPDIFF(SECOND, s.d_start, s.d_eind)) as watchTime
        FROM stream s
        WHERE s.klantID = ? AND DATE(s.d_start) = CURDATE()
    ");
    $stmt->bind_param("i", $klantNr);
    $stmt->execute();
    $result = $stmt->get_result();

    $watchTime = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $watchTime = $row['watchTime'];
    }

    $stmt->close();
    $conn->close();
    
    return $watchTime;
}
function deleteWatchHistory($klantNr) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("DELETE FROM stream WHERE klantID = ?");
    $stmt->bind_param("i", $klantNr);
    $stmt->execute();

    $success = $stmt->affected_rows > 0;

    $stmt->close();
    $conn->close();
    
    return $success;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profiel</title>
    <link rel="stylesheet" href="style/home.css">
    <style>
        .profile-container {
            background-color: #16213e;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 300px;
            color: white;
            margin: auto;
            margin-top: 50px;
        }
        .profile-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .profile-container p {
            margin: 10px 0;
        }
        .watch-history {
            margin-top: 20px;
        }
        .watch-history h2 {
            font-size: 20px;
            margin-bottom: 10px;
            text-align: center;
        }
        .watch-history ul {
            list-style-type: none;
            padding: 0;
        }
        .watch-history li {
            margin-bottom: 10px;
        }
        .series-management {
            margin-top: 20px;
        }
        .series-management h2 {
            font-size: 20px;
            margin-bottom: 10px;
            text-align: center;
        }
        .series-management ul {
            list-style-type: none;
            padding: 0;
        }
        .series-management li {
            margin-bottom: 10px;
        }
        .wissen{
            display: inline-block;
            margin-right: 10px;
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease;
            background-color: #92d051;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <img src="images/HOBO_logo.png" alt="Logo">
            <a href="index.php" class="home">Home</a>
        </div>  <?php var_dump($_SESSION)?> 
    </header>
    <div class="profile-container">
        <?php if ($klantNr): ?>
            <h1>Profiel</h1>
            <form method="post" action="">
                <p>
                    <label for="voornaam">Voornaam:</label><br>
                    <input type="text" name="voornaam" id="voornaam" value="<?php echo htmlspecialchars($userData['voornaam']); ?>" required>
                </p>
                <p>
                    <label for="tussenvoegsel">Tussenvoegsel:</label><br>
                    <input type="text" name="tussenvoegsel" id="tussenvoegsel" value="<?php echo htmlspecialchars($userData['tussenvoegsel']); ?>">
                </p>
                <p>
                    <label for="achternaam">Achternaam:</label><br>
                    <input type="text" name="achternaam" id="achternaam" value="<?php echo htmlspecialchars($userData['achternaam']); ?>" required>
                </p>
                <p>
                    <label for="email">Email:</label><br>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                </p>
                <p>
                    <label for="genre">Favoriete genre:</label><br>
                    <input type="text" name="genre" id="genre" value="<?php echo htmlspecialchars($userData['genre']); ?>" required>
                </p>
                <p>
                    <input type="submit" name="submit" value="Opslaan">
                </p>
            </form>
          
            <div class="watch-history">
                <h2>Kijkgeschiedenis</h2>
                
                <ul>
                    <?php if (!empty($watchHistory)): ?>
                        <form method="post" action="">
                                    <p>
                                        <input type="submit" name="clear-history" value="Wis kijkgeschiedenis" class="wissen">
                                    </p>
                                </form>
                                <?php
                                if (isset($_POST['clear-history'])) {
                                    $success = deleteWatchHistory($klantNr);
                                    if ($success) {
                                        $watchHistory = [];
                                    }
                                }
                                ?>
                        <?php foreach ($watchHistory as $entry): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($entry['SerieTitel']); ?></strong>: 
                                <?php echo htmlspecialchars($entry['AflTitel']); ?> 
                                <p>Gestart op:</p> <?php echo htmlspecialchars($entry['d_start']); ?>
                                <p>Beëindigd op:</p> <?php echo htmlspecialchars($entry['d_eind']); ?>
                          
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Geen kijkgeschiedenis gevonden.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="watch-time-statistics">
                <h2>Kijktijd Vandaag</h2>
                <p>
                    <?php 
                        if ($todayWatchTime) {
                            $hours = floor($todayWatchTime / 3600);
                            $minutes = floor(($todayWatchTime % 3600) / 60);
                            echo "Je hebt vandaag " . $hours . " uur en " . $minutes . " minuten gekeken.";
                        } else {
                            echo "Je hebt vandaag nog niets gekeken.";
                        }
                    ?>
                </p>
            </div>
        <?php endif; ?>
        <div class="pagination">
    <?php if ($isContentmanager): ?>
        <h2>Series Beheer</h2>
        <form method="post" action="">
            <ul>
                <?php foreach ($allSeries as $serie): ?>
                    <li>
                        <label>
                            <?php echo htmlspecialchars($serie['SerieTitel']); ?>
                            <a href="serieedit.php?serie_id=<?php echo $serie['SerieID']; ?>">Bewerk</a>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPagesSeries; $i++): ?>
                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <h2>Gebruikers</h2>
        <ul>
            <?php foreach ($userData as $user): ?>
                <li>
                    <strong><?php echo htmlspecialchars($user['voornaam']); ?></strong>
                    <p><?php echo htmlspecialchars($user['tussenvoegsel']); ?></p>
                    <p><?php echo htmlspecialchars($user['achternaam']); ?></p>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <p><?php echo htmlspecialchars($user['genre']); ?></p>
                    <a href="useredit.php?user_id=<?php echo $user['klantnr']; ?>">Bewerk</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPagesUsers; $i++): ?>
                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>


       
    </div>
</body>
</html>

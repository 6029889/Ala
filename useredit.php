<?php
include 'connect.php';

session_start();

if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'admin') {
    header("Location: index.php");
    exit();
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

function deleteUser($klantNr) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("DELETE FROM klant WHERE klantnr = ?");
    $stmt->bind_param("i", $klantNr);
    $stmt->execute();

    $success = $stmt->affected_rows > 0;

    $stmt->close();
    $conn->close();
    
    return $success;
}

$klantNr = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$userData = null;

if ($klantNr) {
    $userData = getUserData($klantNr);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        $voornaam = $_POST['voornaam'];
        $tussenvoegsel = $_POST['tussenvoegsel'];
        $achternaam = $_POST['achternaam'];
        $email = $_POST['email'];
        $genre = $_POST['genre'];

        $success = updateUserData($klantNr, $voornaam, $tussenvoegsel, $achternaam, $email, $genre);

        if ($success) {
            $userData = getUserData($klantNr);
            $message = "Gebruiker bijgewerkt.";
        } else {
            $message = "Bijwerken mislukt.";
        }
    } elseif (isset($_POST['delete'])) {
        $success = deleteUser($klantNr);

        if ($success) {
            header("Location: profiel.php");
            exit();
        } else {
            $message = "Verwijderen mislukt.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruiker Bewerken</title>
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
        .profile-container form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .profile-container input[type="text"],
        .profile-container input[type="email"],
        .profile-container input[type="submit"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .profile-container input[type="submit"] {
            background-color: #92d051;
            color: white;
            cursor: pointer;
        }
        .profile-container .delete-btn {
            background-color: #d9534f;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <img src="images/HOBO_logo.png" alt="Logo">
            <a href="index.php" class="home">Home</a>
        </div>
    </header>
    <div class="profile-container">
        <?php if ($userData): ?>
            <h1>Gebruiker Bewerken</h1>
            <?php if (isset($message)): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <form method="post" action="profiel.php">
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
                    <input type="submit" name="update" value="Opslaan">
                </p>
                <p>
                    <input type="submit" name="delete" value="Verwijderen" class="delete-btn">
                </p>
            </form>
        <?php else: ?>
            <p>Geen gebruiker gevonden.
            <?php  echo $klantNr ?>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>

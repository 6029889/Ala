<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'content') {

    header("Location: index.php");
    exit();
}

$serieID = isset($_GET['serie_id']) ? (int)$_GET['serie_id'] : null;
$serie = null;

function getSerieDetails($serieID) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("SELECT SerieID, SerieTitel, Actief FROM serie WHERE SerieID = ?");
    $stmt->bind_param("i", $serieID);
    $stmt->execute();
    $result = $stmt->get_result();

    $serie = null;
    if ($result->num_rows > 0) {
        $serie = $result->fetch_assoc();
    }

    $stmt->close();
    $conn->close();
    
    return $serie;
}

function updateSerie($serieID, $serieTitel, $actief) {
    $conn = connect_to_database();
    $stmt = $conn->prepare("UPDATE serie SET SerieTitel = ?, Actief = ? WHERE SerieID = ?");
    $stmt->bind_param("sii", $serieTitel, $actief, $serieID);
    $stmt->execute();

    $success = $stmt->affected_rows > 0;

    $stmt->close();
    $conn->close();
    
    return $success;
}
function deleteSeries($serieID) {
    $conn = connect_to_database();
    
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete episodes
        $stmt = $conn->prepare("
            DELETE aflevering 
            FROM aflevering 
            INNER JOIN seizoen ON aflevering.SeizID = seizoen.SeizoenID 
            WHERE seizoen.SerieID = ?
        ");
        $stmt->bind_param("i", $serieID);
        $stmt->execute();
        $stmt->close();

        // Delete seasons
        $stmt = $conn->prepare("DELETE FROM seizoen WHERE SerieID = ?");
        $stmt->bind_param("i", $serieID);
        $stmt->execute();
        $stmt->close();

        // Delete from serie_genre
        $stmt = $conn->prepare("DELETE FROM serie_genre WHERE SerieID = ?");
        $stmt->bind_param("i", $serieID);
        $stmt->execute();
        $stmt->close();

        // Delete series
        $stmt = $conn->prepare("DELETE FROM serie WHERE SerieID = ?");
        $stmt->bind_param("i", $serieID);
        $stmt->execute();

        $success = $stmt->affected_rows > 0;

        $stmt->close();

        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $success = false;
    }

    $conn->close();
    
    return $success;
}


if ($serieID) {
    $serie = getSerieDetails($serieID);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serieTitel = $_POST['serieTitel'];
    $actief = isset($_POST['actief']) ? 1 : 0;
    $success = updateSerie($serieID, $serieTitel, $actief);

    if ($success) {
        header("Location: profiel.php");
        exit();
    } else {
        $error = "Er is een fout opgetreden bij het bijwerken van de serie.";
    }
}
if (isset($_POST['delete'])) {
    $success = deleteSeries($serieID);

    if ($success) {
        header("Location: profiel.php");
        exit();
    } else {
        $error = "Er is een fout opgetreden bij het verwijderen van de serie.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serie Bewerken</title>
    <link rel="stylesheet" href="style/home.css">
    <style>
        .edit-container {
            background-color: #16213e;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 300px;
            color: white;
            margin: auto;
            margin-top: 50px;
        }
        .edit-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .edit-container form p {
            margin: 10px 0;
        }
        .edit-container form input[type="text"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .edit-container form input[type="checkbox"] {
            margin-right: 10px;
        }
        .edit-container form input[type="submit"] {
            background-color: #92d051;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .edit-container form input[type="submit"]:hover {
            background-color: #76b041;
        }
        .edit-container .error {
            color: red;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h1>Serie Bewerken</h1>
        <?php if ($serie): ?>
            <form method="post" action="">
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <p>
                    <label for="serieTitel">Serietitel:</label><br>
                    <input type="text" name="serieTitel" id="serieTitel" value="<?php echo htmlspecialchars($serie['SerieTitel']); ?>" required>
                </p>
                <p>
                    <label for="actief">Actief:</label>
                    <input type="checkbox" name="actief" id="actief" <?php if ($serie['Actief']) echo 'checked'; ?>>
                </p>
                <p>
                    <input type="submit" value="Opslaan">
                    <input type="submit" name="delete" value="Delete" >
                </p>
            </form>
        <?php else: ?>
            <p>Serie niet gevonden.</p>
        <?php endif; ?>
    </div>
</body>
</html>

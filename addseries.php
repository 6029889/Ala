<?php
function addnewseries(){
    include 'connect.php';
    $seriesname = $_POST['seriesname'];
    $seriesstatus = 1;
    $conn = connect_to_database();
    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO serie (SerieTitel, Actief) VALUES (?, ?)");
    $stmt->bind_param("si", $seriesname, $seriesstatus);

    if ($stmt->execute() === TRUE) {
        echo "New record created successfully";
        header('Location: index.php');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    addnewseries();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        form {
            display: flex;
            flex-direction: column;
            width: 300px;
            margin: 0 auto;
        }
        label {
            margin-bottom: 10px;
        }
        input {
            margin-bottom: 20px;
            padding: 5px;
        }
    </style>
    <title>Serie toevoegen</title>
</head>
<body>
    <form action="" method="post">
        <label for="seriesname">Serie naam</label>
        <input type="text" name="seriesname" id="seriesname" required>
        <input type="submit" value="Toevoegen">
    </form>
</body>
</html>

<?php
include 'connect.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $email = $_POST['email'];
    $gebruikersnaam = $_POST['gebruikersnaam'];
    $tussenvoegsel = $_POST['tussenvoegsel'];
    $achternaam = $_POST['achternaam'];
    $wachtwoord = $_POST['wachtwoord'];
    $wachtwoordBevestiging = $_POST['wachtwoordBevestiging'];

    if ($wachtwoord !== $wachtwoordBevestiging) {
        $registerError = "Wachtwoorden komen niet overeen.";
    } else {
        $conn = connect_to_database();


        $stmt = $conn->prepare("SELECT KlantNr FROM klant WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $registerError = "E-mailadres is al in gebruik.";
        } else {
      
            $hashedPassword = password_hash($wachtwoord, PASSWORD_DEFAULT);

        
            $genrefavoriet = "Science Ficton";
            $stmt = $conn->prepare("INSERT INTO klant (Voornaam, Tussenvoegsel, Achternaam, Email, Password, AboID, Genre) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $gebruikersnaam, $tussenvoegsel, $achternaam, $email, $hashedPassword, $aboID, $genrefavoriet);

            if ($stmt->execute()) {
                $_SESSION['KlantNr'] = $stmt->insert_id;
                header("Location: index.php");
                exit();
            } else {
                $registerError = "Er is een fout opgetreden bij de registratie.";
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body class="bodyinlog">
<div class="container">
    <header>
        <div class="header-left">
            <img src="images/HOBO_logo.png" alt="">
        </div>
        <div class="header-right">
            <a href="index.php">Log In</a>
        </div>
    </header>
    <div>
        <h1>Register</h1>
        <?php if (isset($registerError)): ?>
            <p style="color:red;"><?php echo $registerError; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <label for="gebruikersnaam">Naam:</label>
            <input type="name" name="gebruikersnaam" id="gebruikersnaam" required><br>
            
            <label for="tussenvoegsel">Tussenvoegsel:</label>
            <input type="name" name="tussenvoegsel" id="tussenvoegsel"><br>
            
            <label for="achternaam">Achternaam:</label>
            <input type="name" name="achternaam" id="achternaam" required><br>
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required><br>

            <label for="wachtwoord">Password:</label>
            <input type="password" name="wachtwoord" id="wachtwoord" required><br>

            <label for="wachtwoordBevestiging">Confirm Password:</label>
            <input type="password" name="wachtwoordBevestiging" id="wachtwoordBevestiging" required><br>

            <input type="submit" name="register" value="Register">
        </form>
    </div>
</div>
</body>
</html>

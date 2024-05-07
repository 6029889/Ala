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
    <title>Welkom op de startpagina</title>
</head>
<body>
    <h1>Welkom op de startpagina</h1>
    <p>Hier is de inhoud van de startpagina voor ingelogde gebruikers.</p>
    <a href="logout.php">Uitloggen</a>
</body>
</html>
<?php else: ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen</title>
</head>
<body>
    <?php if (isset($loginError)) { ?>
        <p><?php echo $loginError; ?></p>
    <?php } ?>

    <h1>Inloggen</h1>
    <form method="post" action="">
        <label for="gebruikersnaam">Gebruikersnaam (E-mail):</label>
        <input type="text" name="gebruikersnaam" id="gebruikersnaam" required><br>

        <label for="wachtwoord">Wachtwoord:</label>
        <input type="password" name="wachtwoord" id="wachtwoord" required><br>

        <input type="submit" name="login" value="Inloggen">
    </form>
</body>
</html>
<?php endif; ?>

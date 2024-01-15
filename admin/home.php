<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "seo_konferencia");

if ($conn->connect_error) {
    die("Sikertelen kapcsolódás: " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];
$sql = "SELECT username FROM admin WHERE id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = $row["username"];
} else {
    echo "Hiba a felhasználó adatainak lekérdezésekor";
}
$tabla = mysqli_query($conn, "SELECT * FROM jelentkezok");
while ($sor = mysqli_fetch_array($tabla))
{
	echo 
	$sor['id'],
	$sor['nev'],
	$sor['szuletesi_ev'],
	$sor['email'],
	$sor['telefonszam'],
	$sor['munkahely_neve'],
	$sor['munkahely_cime'],
	$sor['munkakor'],
	$sor['arckep_nev'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>
    <h2>Üdvözöllek, <?php echo $username; ?>!</h2>
    <a href="logout.php">Kijelentkezés</a>
	<p>Regisztrált felhasználók</p>
</body>
</html>

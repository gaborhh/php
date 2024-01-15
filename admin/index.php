<?php
session_start();

$host = "localhost"; 
$username = "root";  
$password = ""; 
$database = "seo_konferencia";  


$conn = new mysqli($host, $username, $password, $database);


if ($conn->connect_error) {
    die("Sikertelen kapcsolat az adatbázissal: " . $conn->connect_error);
}


if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $query = "SELECT id, username, password FROM admin WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($user_id, $db_username, $db_password_hash);

        if ($stmt->fetch() && md5($password) === $db_password_hash) {
            $_SESSION['user_id'] = $user_id;
            header("Location: home.php");
            exit();
        } else {
            $error_message = "Hibás felhasználónév vagy jelszó!";
        }

        $stmt->close();
    } else {
        $error_message = "Kérjük, adja meg mindkét mezőt!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
</head>
<body>
    <h2>Bejelentkezés</h2>

    <?php if (isset($error_message)) { ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php } ?>

    <form method="post" >
        <label for="username">Felhasználónév:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Jelszó:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <input type="submit" value="Bejelentkezés">
    </form>
</body>
</html>

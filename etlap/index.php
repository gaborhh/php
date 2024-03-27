<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étlap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 80%;
            margin: 0 auto;
        }
        .kategoria {
            margin-top: 20px;
            font-size: 20px;
            font-weight: bold;
        }
        .etel {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="GET">
            <input type="text" name="kereses" placeholder="Keresés...">
            <button type="submit">Keresés</button>
        </form>
        <form method="GET">
            <select name="kategoriak">
                <option value="">Összes</option>
                <option value="Főétel">Főétel</option>
                <option value="Desszertek">Desszertek</option>
                <option value="Levesek">Levesek</option>
            </select>
            <button type="submit">Szűrés</button>
        </form>
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "etlap";
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Hiba: " . $conn->connect_error);
        }
        $kereses = isset($_GET['kereses']) ? $_GET['kereses'] : '';
        $kategoria = isset($_GET['kategoriak']) ? $_GET['kategoriak'] : '';
        $sql = "SELECT nev, ar , katNev FROM etelek INNER JOIN kategoriak ON etelek.katAz = kategoriak.katAz";
        if (!empty($kereses)) {
            $sql .= " WHERE nev LIKE '%$kereses%'";
        }
        if (!empty($kategoria)) {
            if (!empty($kereses)) {
                $sql .= " AND ";
            } else {
                $sql .= " WHERE ";
            }
            $sql .= "kategoriak.katNev = '$kategoria'";
        }
        $sql .= " ORDER BY katNev";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='kategoria'>" . $row["katNev"] . "</div>";
                echo "<div class='etel'>" . $row["nev"] . " - " . $row["ar"] . " Ft</div>";
            }
        } else {
            echo "Nincs adat";
        }
        $conn->close();
        ?>

    </div>
</body>
</html>

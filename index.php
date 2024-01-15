<!DOCTYPE html>
<html>
<head>
    <title>Jelentkezési űrlap</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script>
       
        function validateCaptcha() {
            var userInput = document.getElementById('captchaInput').value;
            var randomNumber1 = parseInt(document.getElementById('num1').innerHTML);
            var randomNumber2 = parseInt(document.getElementById('num2').innerHTML);
            var correctAnswer = randomNumber1 + randomNumber2;

            if (userInput == correctAnswer) {
                return true; 
            } else {
                alert("Hibás eredmény! Próbálja újra.");
                generateCaptcha();
                return false; 
            }
        }

       
        function generateCaptcha() {
            var number1 = Math.floor(Math.random() * 10); 
            var number2 = Math.floor(Math.random() * 10);
            
            document.getElementById('num1').innerHTML = number1;
            document.getElementById('num2').innerHTML = number2;
        }
    </script>
</head>

<body onload="generateCaptcha()">
    <h2>Jelentkezési űrlap</h2>
    <form action="feldolgozas.php" method="post" enctype="multipart/form-data" onsubmit="return validateCaptcha()">
		<label for="nev">Név:</label>
        <input type="text" id="nev" name="nev" required><br><br>
        
        <label for="szuletesi_ev">Születési év:</label>
        <input type="number" id="szuletesi_ev" name="szuletesi_ev" required><br><br>
        
        <label for="email">E-mail cím:</label>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="telefonszam">Telefonszám:</label>
        <input type="tel" id="telefonszam" name="telefonszam" required><br><br>
        
        <label for="munkahely_neve">Munkahely neve:</label>
        <input type="text" id="munkahely_neve" name="munkahely_neve" required><br><br>
        
        <label for="munkahely_cime">Munkahely címe:</label>
        <input type="text" id="munkahely_cime" name="munkahely_cime" required><br><br>
        
        <label for="munkakor">Munkakör:</label>
        <input type="text" id="munkakor" name="munkakor" required><br><br>
        
        <label for="arckep">Arckép feltöltése:</label>
        <input type="file" id="arckep" name="arckep" accept="image/*" required><br><br>

        <label for="captchaInput">Mennyi az eredménye:</label>
        <span id="num1"></span> + <span id="num2"></span> =
        <input type="number" id="captchaInput" name="captchaInput" required><br><br>

        <input type="submit" value="Jelentkezés">
    </form>
</body>
</html>

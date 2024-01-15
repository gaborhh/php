<?php
require('tFPDF/tfpdf.php'); 
class PDF extends tFPDF {
    function AddFont($family, $style='', $file='', $uni=false) {
        if($file=='') $file=str_replace(' ','',$family).strtolower($style).'.php';
        parent::AddFont($family,$style,$file,$uni);
    }
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seo_konferencia";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Sikertelen kapcsolódás az adatbázishoz: " . $conn->connect_error);
}

$max_registrations = 5; 


$check_registration_query = "SELECT COUNT(*) as count FROM jelentkezok";
$count_result = $conn->query($check_registration_query);

if ($count_result) {
    $row = $count_result->fetch_assoc();
    $current_registrations = $row['count'];

    if ($current_registrations >= $max_registrations) {
       
        echo "A regisztrációk száma már elérte a maximális limitet. Jelenleg nem lehetséges új jelentkezést leadni.";
        exit(); 
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (!isset($_FILES['arckep']['error']) || is_array($_FILES['arckep']['error'])) {
      die('Hibás fájlfeltöltés.');
  }

  switch ($_FILES['arckep']['error']) {
      case UPLOAD_ERR_OK:
          break;
      case UPLOAD_ERR_NO_FILE:
          die('Nincs fájl feltöltve.');
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
          die('A fájl mérete meghaladja a megengedett maximális értéket.');
      default:
          die('Ismeretlen hiba történt a fájlfeltöltés során.');
  }
    $nev = $_POST['nev'];
    $szuletesi_ev = $_POST['szuletesi_ev'];
    $email = $_POST['email'];
    $telefonszam = $_POST['telefonszam'];
    $munkahely_neve = $_POST['munkahely_neve'];
    $munkahely_cime = $_POST['munkahely_cime'];
    $munkakor = $_POST['munkakor'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Hibás e-mail cím formátum!";
    } else {
        $check_query = "SELECT * FROM jelentkezok WHERE email='$email'";
        $result = $conn->query($check_query);

        if ($result->num_rows > 0) {
            echo "Az email cím már létezik az adatbázisban!";
        } else {
            $arckep_tmp_nev = $_FILES['arckep']['tmp_name'];
            $arckep_nev = $_FILES['arckep']['name'];
            $arckep_meret = getimagesize($arckep_tmp_nev);
            $kep_formatum = pathinfo($arckep_nev, PATHINFO_EXTENSION);
            $unique_image_id = uniqid(); 

            if ($kep_formatum != 'jpg') {
                echo "Csak .jpg formátumú képeket fogadunk el!";
            } else if ($arckep_meret[0] < 480 || $arckep_meret[1] < 480) {
                echo "A feltöltött kép mérete nem megfelelő!";
            } else {
              $unique_image_id = uniqid();
              $email_parts = explode('@', str_replace('.', '', $email));
              $user_folder_name = "users/" . str_replace(['.', '@'], '', $email_parts[0]) . $email_parts[1];
              $new_image_name = $unique_image_id . ".jpg";
              $new_image_path = $user_folder_name . "/" . $email . ".jpg";
              

                if (!is_dir($user_folder_name)) {
                    mkdir($user_folder_name, 0777, true);
                }

                if ($arckep_meret[0] > 640 || $arckep_meret[1] > 640) {
                    $img = imagecreatefromjpeg($arckep_tmp_nev);
                    $new_img = imagescale($img, 640, 640);
                    imagejpeg($new_img, $new_image_path);
                } else {
                    $img = imagecreatefromjpeg($arckep_tmp_nev);
                    $size = min(imagesx($img), imagesy($img));
                    $new_img = imagecrop($img, ['x' => 0, 'y' => 0, 'width' => $size, 'height' => $size]);
                    imagejpeg($new_img, $new_image_path);
                }

                
                if (empty($nev) || empty($szuletesi_ev) || empty($telefonszam) || empty($munkahely_neve) || empty($munkahely_cime) || empty($munkakor)) {
                    echo "Minden mezőt ki kell tölteni!";
                } else {
                   
                    $file_name = $email . ".txt";
                    $file_content = "Név: $nev\nSzületési év: $szuletesi_ev\nE-mail: $email\nTelefonszám: $telefonszam\nMunkahely neve: $munkahely_neve\nMunkahely címe: $munkahely_cime\nMunkakör: $munkakor\nJelentkezés dátuma: " . date("Y-m-d H:i:s") . "\nIP cím: " . $_SERVER['REMOTE_ADDR'];
                    if (!file_exists('almappa')) {
                        mkdir('almappa', 0777, true);
                    }

                    
                    file_put_contents("almappa/" . $file_name, $file_content);

                    $pdf = new tFPDF();
                    $pdf->AddPage();

                    $pdf->AddFont('DejaVuSansCondensed-Bold','','DejaVuSansCondensed-Bold.ttf',true);
                    $pdf->SetFont('DejaVuSansCondensed-Bold','',14);

                    $event_date = date("Y-m-d"); 
                    $pdf->SetFont('DejaVuSansCondensed-Bold', '', 12);
                    $pdf->Cell(0, 10, ('SEO Konferencia'), 0, 1, 'C');
                    $pdf->SetFont('DejaVuSansCondensed-Bold', '', 10);
                    $pdf->Cell(0, 5, ("Dátum: $event_date"), 0, 1, 'C');

                    $pdf->SetFont('DejaVuSansCondensed-Bold', '', 8);
                    $pdf->Cell(0, 5, ('Jelentkező adatai:'), 0, 1, 'L');
                    $pdf->SetFont('DejaVuSansCondensed-Bold', '', 8);
                    $pdf->Cell(0, 5, ("Név: $nev"), 0, 1, 'L');
                    $pdf->Cell(0, 5, ("Munkahely: $munkahely_neve"), 0, 1, 'L');
                    

                    $image_path = "users/" . str_replace(['.', '@'], '', $email) . "/" . $email . ".jpg";
                    if (file_exists($image_path)) {
                        $pdf->Image($image_path, 5, 35, 20);
                    } else {
                        $pdf->Cell(0, 5, ('Kép nem található'), 0, 1, 'L');
                    }

                    $pdf_file_name = $email . ".pdf";
                    $pdf->Output("almappa/$pdf_file_name", 'F');
                    print '<!DOCTYPE html>
                    <html lang="hu">
                    <head>
                      <meta charset="UTF-8">
                      <title>Bankkártya</title>
                      <style>
                        .card-container {
                          display: flex;
                          justify-content: center;
                          align-items: center;
                          height: 100vh;
                        }
                    
                        .card {
                          width: 500px;
                          height: 300px;
                          border: 1px solid #ccc;
                          border-radius: 5px;
                          background-color: #f0f0f0;
                          padding: 5px;
                        }
                    
                        .card-table {
                          width: 100%;
                          height: 100%;
                        }
                    
                        .card-number {
                            height: 15px;
                          font-weight: bold;
                          color: white;
                          background-color: #333;
                          padding: 3px;
                          border-radius: 3px;
                          text-align: center;
                        }
                    
                        .card-logo {
                          border: 1px solid #ccc;
                          width: 20px;
                          height: 12px;
                        }
                    
                        .card-info {
                          font-size: 18px;
                        }
                    
                        .divider {
                          border-right: 1px solid #ccc;
                        }
                        .flex-container {
                          display: flex;
                          flex-direction: column;
                          align-items: stretch;
                          text-align: center;
                      }
                      .element {
                        margin: 10px;
                        padding: 10px;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                    }
                      </style>
                    </head>
                    <body>
                    
                    <div class="card-container">
                      <div class="card">
                        <table class="card-table">
                          <tr>
                            <td colspan="2" class="card-number" style="font-size: 18px;">SEO Konferencia</td>
                          </tr>
                            
                            <td class="card-logo"><img style="height:170px; width:auto;" src="' . $image_path . '" alt="felhasznalo_kep"></td>
                            <td class="card-info">
                            <div class="flex-container">
                            <div class="element">Név: '. $nev .'</div>
                            <div class="element">Munkahely: '. $munkahely_neve .'</div>
                            <div class="element">Rendezvény dátuma: 2024.08.01</div>
                            </div>
                          </td>
                          <tr>
                            
                            <td class="divider"></td>

                          </tr>
                        </table>
                      </div>
                    </div>
                    
                    </body>
                    </html>
                    
                    
                    ';

                    /* Elkészült belépőkártya linkje
                    $pdf_link = "http://example.com/almappa/" . $email . ".pdf";

                    // E-mail küldése a jelentkezőnek
                    $to = $email;
                    $subject = "Regisztráció elfogadva - SEO Konferencia";
                    $message = "Kedves $nev!\n\nKöszönjük a regisztrációt a SEO Konferenciára!\nAz alábbi linken megtalálod belépőkártyád: $pdf_link\n\nÜdvözlettel,\nSEO Konferencia Csapata";
                    $headers = "From: info@seo-konferencia.hu";

                    if (mail($to, $subject, $message, $headers)) {
                        echo "Sikeresen elküldtük az értesítő e-mailt a jelentkezőnek!";
                    } else {
                        echo "Hiba történt az e-mail küldése során!";
                    }
                    */
                   
                    $insert_query = "INSERT INTO jelentkezok (nev, szuletesi_ev, email, telefonszam, munkahely_neve, munkahely_cime, munkakor, arckep_nev) 
                                    VALUES ('$nev', '$szuletesi_ev', '$email', '$telefonszam', '$munkahely_neve', '$munkahely_cime', '$munkakor', '$unique_image_id')";

                    if ($current_registrations < $max_registrations && $conn->query($insert_query) === TRUE) {

                        $current_registrations++;
                        if ($current_registrations > $max_registrations) {
                            
                            echo "A regisztrációk száma már elérte a maximális limitet. Jelenleg nem lehetséges új jelentkezést leadni.";
                            exit(); 
                        }
                    } else {
                        echo "Hiba az adatok mentésekor vagy túllépte a maximális jelentkezők számát!";
                    }
                }
            }
        }
    }
}
$conn->close();
?>
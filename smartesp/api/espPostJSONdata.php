<?php
require_once "connect.php";

$data = file_get_contents('php://input');
$parsed = json_decode($data);

if ($parsed!=NULL){
   $conn = @new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
      die("Błąd połączenia: " . $conn->connect_error);
   }
   $sql = sprintf("INSERT INTO esp_sensors VALUES (NULL,'%s','%s','%s','%s')",$parsed->T,$parsed->C,$parsed->H,$parsed->D);

   if ($conn->query($sql) === TRUE) {
      echo "Dostalem[OK]";
   } else {
      echo "Error: " . $sql . " => " . $conn->error;
   }
   $conn->close();
}
else 
{
	print "Brak danych (json)!";
}

?>
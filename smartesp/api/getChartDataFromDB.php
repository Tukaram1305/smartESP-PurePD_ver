<?php
   // Glowny skypt php z zapytaniami do bazy SQL i zwracajacy informacje do tabel
	require_once "connect.php";
   // Create connection
   $conn = @new mysqli($servername, $username, $password, $dbname);
   // Check connection
   if ($conn->connect_error) {
      die("Błąd połączenia: " . $conn->connect_error);
   }
   else {
  
   //Pobierz dane dt. np. temperatury
	if (isset($_POST['sensorType']))
	{
      $whatData = $_POST['sensorType'];

   if (isset($_POST['Range']))
   {
      $range = $_POST['Range'];
      $startdate = date('Y-m-d H:i:s',strtotime($_POST['startDate'])); // formatuj date i czas

      if ($sql = mysqli_query($conn,sprintf("SELECT %s AS T, Timestamp AS TS FROM esp_sensors WHERE Timestamp >= DATE_SUB('%s',INTERVAL %s)",$whatData,$startdate,$range)))
      {
         $jsonStr = json_encode(mysqli_fetch_all($sql));
         header('Content-Type: application/json; charset=utf-8');
         echo $jsonStr;
         mysqli_free_result($sql);
         
      }
      else 
      {
         echo "Błąd zapytania SQL: ".$conn -> error;
      }
   }
   else
   {
      $startdate = date('Y-m-d',strtotime($_POST['startDate'])); // formatuj date i czas
      $enddate = date('Y-m-d',strtotime($_POST['endDate'])); // formatuj date i czas

      if ($sql = mysqli_query($conn,sprintf("SELECT %s AS T, Timestamp AS TS FROM esp_sensors WHERE Timestamp BETWEEN '%s' AND '%s' ",$whatData,$startdate,$enddate)))
      {
         $jsonStr = json_encode(mysqli_fetch_all($sql));
         header('Content-Type: application/json; charset=utf-8');
         echo $jsonStr;
         mysqli_free_result($sql);
         
      }
      else 
      {
         echo "Błąd zapytania SQL: ".$conn -> error;
      }
   }
 
   }
}
$conn->close();
?>
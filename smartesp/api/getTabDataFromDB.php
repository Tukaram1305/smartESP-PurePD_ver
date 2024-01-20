<?php
   // Glowny kod PHP z zapytaniami do bazy SQL i zwracajacy informacje dla tabel
	require_once "connect.php";
   $conn = @new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
      die("Błąd połączenia: " . $conn->connect_error);
   }
   else {
      $arr = Array (
         "minHour"=>0.0,
         "maxHour"=>0.0,
         "avgHour"=>0.0,
         "HourNums"=>0,
         "minDay"=>0.0,
         "maxDay"=>0.0,
         "avgDay"=>0.0,
         "DayNums"=>0,
         "minWeek"=>0.0,
         "maxWeek"=>0.0,
         "avgWeek"=>0.0,
         "WeekNums"=>0,
         "minMonth"=>0.0,
         "maxMonth"=>0.0,
         "avgMonth"=>0.0,
         "MonthNums"=>0
      );
      // przedzialy czasowe dla tabeli
      $firstDTrange = "48 HOUR";
      $secondDTrange = "2 WEEK";
      $thirdDTrange = "1 MONTH";
      $fourthDTrange = "4 MONTH";
	if (isset($_POST['sensorType']))
	{
      $whatData = $_POST['sensorType'];

   //pierwszy zakres
   $sql = mysqli_query($conn,sprintf("SELECT COUNT(%s) AS 'ILE' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s)",$whatData,$firstDTrange));
   $arr["HourNums"]=mysqli_fetch_assoc($sql);
   if ($arr['HourNums']['ILE']>0)
   {
      $sql = mysqli_query($conn,sprintf("SELECT TRUNCATE(AVG(%s),2) AS 'AVG' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s)",$whatData, $firstDTrange));
      $arr["avgHour"]=mysqli_fetch_assoc($sql);
      $sql = mysqli_query($conn,sprintf("SELECT %s AS 'MIN',Timestamp AS 'TS' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s) ORDER BY %s ASC LIMIT 1",$whatData,$firstDTrange,$whatData));
      $arr["minHour"]=mysqli_fetch_assoc($sql);
      $sql = mysqli_query($conn,sprintf("SELECT %s AS 'MAX',Timestamp AS 'TS' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s) ORDER BY %s DESC LIMIT 1",$whatData,$firstDTrange,$whatData));
      $arr["maxHour"]=mysqli_fetch_assoc($sql);
    
      if ($arr["minHour"]!=NULL) $arr["minHour"]["TS"] = date("H:i d/m",strtotime($arr["minHour"]["TS"]));
      if ($arr["maxHour"]!=NULL) $arr["maxHour"]["TS"] = date("H:i d/m",strtotime($arr["maxHour"]["TS"]));
   }  
   else 
   {
      $arr['avgHour'] = array("AVG"=>0);
      $arr['minHour'] = array("MIN"=>0,"TS"=>"BRAK DANYCH");
      $arr['maxHour'] = array("MAX"=>0,"TS"=>"BRAK DANYCH");
   }

   //drugi zakres
   $sql = mysqli_query($conn,sprintf("SELECT COUNT(%s) AS 'ILE' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s)",$whatData,$secondDTrange));
   $arr["DayNums"]=mysqli_fetch_assoc($sql);
   if ($arr['DayNums']['ILE']>0)
   {
      $sql = mysqli_query($conn,sprintf("SELECT TRUNCATE(AVG(%s),2) AS 'AVG' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s)",$whatData,$secondDTrange));
      $arr["avgDay"]=mysqli_fetch_assoc($sql);
      $sql = mysqli_query($conn,sprintf("SELECT %s AS 'MIN',Timestamp AS 'TS' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s) ORDER BY %s ASC LIMIT 1",$whatData,$secondDTrange,$whatData));
      $arr["minDay"]=mysqli_fetch_assoc($sql);
      $sql = mysqli_query($conn,sprintf("SELECT %s AS 'MAX',Timestamp AS 'TS' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s) ORDER BY %s DESC LIMIT 1",$whatData,$secondDTrange,$whatData));
      $arr["maxDay"]=mysqli_fetch_assoc($sql);

      $arr['minDay']['TS']=date("H:i d/m",strtotime($arr['minDay']['TS']));
      $arr['maxDay']['TS']=date("H:i d/m",strtotime($arr['maxDay']['TS']));
   }
   else 
   {
      $arr['avgDay'] = array("AVG"=>0);
      $arr['minDay'] = array("MIN"=>0,"TS"=>"BRAK DANYCH");
      $arr['maxDay'] = array("MAX"=>0,"TS"=>"BRAK DANYCH");
   }
   
   //trzeci zakres
   $sql = mysqli_query($conn,sprintf("SELECT COUNT(%s) AS 'ILE' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s)",$whatData,$thirdDTrange));
   $arr["WeekNums"]=mysqli_fetch_assoc($sql);
   if ($arr['WeekNums']['ILE']>0)
   {
      $sql = mysqli_query($conn,sprintf("SELECT TRUNCATE(AVG(%s),2) AS 'AVG' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s)",$whatData,$thirdDTrange));
      $arr["avgWeek"]=mysqli_fetch_assoc($sql);
      $sql = mysqli_query($conn,sprintf("SELECT %s AS 'MIN',Timestamp AS 'TS' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s) ORDER BY %s ASC LIMIT 1",$whatData,$thirdDTrange,$whatData));
      $arr["minWeek"]=mysqli_fetch_assoc($sql);
      $sql = mysqli_query($conn,sprintf("SELECT %s AS 'MAX',Timestamp AS 'TS' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s) ORDER BY %s DESC LIMIT 1",$whatData,$thirdDTrange,$whatData));
      $arr["maxWeek"]=mysqli_fetch_assoc($sql);

      $arr['minWeek']['TS']=date("H:i d/m",strtotime($arr['minWeek']['TS']));
      $arr['maxWeek']['TS']=date("H:i d/m",strtotime($arr['maxWeek']['TS']));
   }
   else 
   {
      $arr['avgWeek'] = array("AVG"=>0);
      $arr['minWeek'] = array("MIN"=>0,"TS"=>"BRAK DANYCH");
      $arr['maxWeek'] = array("MAX"=>0,"TS"=>"BRAK DANYCH");
   }

   //czwarty zakres
   $sql = mysqli_query($conn,sprintf("SELECT COUNT(%s) AS 'ILE' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s)",$whatData,$fourthDTrange));
   $arr["MonthNums"]=mysqli_fetch_assoc($sql);
   if ($arr['MonthNums']['ILE']>0)
   {
      $sql = mysqli_query($conn,sprintf("SELECT TRUNCATE(AVG(%s),2) AS 'AVG' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s)",$whatData,$fourthDTrange));
      $arr["avgMonth"]=mysqli_fetch_assoc($sql);
      $sql = mysqli_query($conn,sprintf("SELECT %s AS 'MIN',Timestamp AS 'TS' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s) ORDER BY %s ASC LIMIT 1",$whatData,$fourthDTrange,$whatData));
      $arr["minMonth"]=mysqli_fetch_assoc($sql);
      $sql = mysqli_query($conn,sprintf("SELECT %s AS 'MAX',Timestamp AS 'TS' FROM esp_sensors WHERE Timestamp >= DATE_SUB(NOW(),INTERVAL %s) ORDER BY %s DESC LIMIT 1",$whatData,$fourthDTrange,$whatData));
      $arr["maxMonth"]=mysqli_fetch_assoc($sql);

      $arr['minMonth']['TS']=date("H:i d/m/y",strtotime($arr['minMonth']['TS']));
      $arr['maxMonth']['TS']=date("H:i d/m/y",strtotime($arr['maxMonth']['TS']));
   }
   else 
   {
      $arr['avgMonth'] = array("AVG"=>0);
      $arr['minMonth'] = array("MIN"=>0,"TS"=>"BRAK DANYCH");
      $arr['maxMonth'] = array("MAX"=>0,"TS"=>"BRAK DANYCH");
   }

   mysqli_free_result($sql);
	$iJSON = json_encode($arr);
   header('Content-Type: application/json; charset=utf-8');
	echo $iJSON;
   }
   $conn->close();
   }
?>
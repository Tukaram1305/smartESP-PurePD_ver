<?php
   // Glowny kod PHP dla zbierania przedzialow danych dla histogramow
	require_once "connect.php";
   $conn = @new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
      die("Błąd połączenia: " . $conn->connect_error);
   }
   else {
	   	if (isset($_POST['sensorType'])) $whatData = $_POST['sensorType'];
	   	if (isset($_POST['Increment'])) $incData = $_POST['Increment'];
	   	if (isset($_POST['range'])) $range = $_POST['range'];
	  
	   $sql = mysqli_query($conn,sprintf("SELECT MIN(%s) AS mini FROM esp_sensors",$whatData));
	   $MIN = mysqli_fetch_assoc($sql);
	   $MIN = floor($MIN['mini']);
	   
	   $sql = mysqli_query($conn,sprintf("SELECT MAX(%s) AS maxi FROM esp_sensors",$whatData));
	   $MAX = mysqli_fetch_assoc($sql);
	   $MAX = ceil($MAX['maxi']);
      
		$SPAN = ($MAX-$MIN)/$incData;
		$arr = array(
		array("Min:",$MIN),
		array("Max:",$MAX),
		array("Span:",$SPAN)
		);
		
		$histArr = [];
		$iter = 0;
		for ($i=0; $i<($MAX-$MIN); $i+=$incData)
		{
			$sql = mysqli_query($conn,sprintf("SELECT COUNT(%s) AS DN FROM esp_sensors WHERE %s BETWEEN %f AND %f AND `Timestamp` >= DATE_SUB(NOW(),INTERVAL %s MONTH)",$whatData,$whatData,$MIN+$i,$MIN+$i+$incData-0.01,$range));
			$result = mysqli_fetch_assoc($sql);
			// 2D array				[0]			 ,					[1]
			$histArr[$iter] = array($result['DN'], sprintf("%.2f - %.2f",$MIN+$i,$MIN+$i+$incData));
			$iter++;
		}

    mysqli_free_result($sql);
	$iJSON = json_encode($histArr);
    header('Content-Type: application/json; charset=utf-8');
	echo $iJSON;
	$conn->close();
   } // conn OK if
?>
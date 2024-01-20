<!DOCTYPE HTML5>
<html>
<head>
<title>Temperatura</title>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" type="text/css" href="css/styl.css?ts=<?=time()?>" />
<link rel="stylesheet" type="text/css" href="css/controls.css?ts=<?=time()?>" />

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Russo+One&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Gelasio:wght@600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Days+One&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sarpanch:wght@800&display=swap" rel="stylesheet">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

</head>
<?php
require_once "api/connect.php";
$conn = @new mysqli($servername, $username, $password, $dbname);
// połaczenie nie powiodło się
if ($conn->connect_errno!=0)
{
	echo '<p style="color:red;font-size:24px;">Błąd nr: '.$conn->connect_errno. '<br/> Opis: '.$conn->connect_error.'</p>';
	exit();
}
// połączenie powiodło się
else
{
 $result = mysqli_query($conn,sprintf("SELECT MIN(Timestamp) AS date_time FROM esp_sensors"));
 $minDate = mysqli_fetch_assoc($result);
 $result = mysqli_query($conn,sprintf("SELECT MAX(Timestamp) AS date_time FROM esp_sensors"));
 $maxDate = mysqli_fetch_assoc($result);
 // konwersja z date_time(string) na obiekt date(int)
 $MIND = strtotime($minDate['date_time']);
 $MAXD = strtotime($maxDate['date_time']);
 // konwersja na format Y-m-d
 $minKnownFormDate = date('Y-m-d',$MIND);
 $maxKnownFormDate = date('Y-m-d',$MAXD);
 $conn->close();
}
?>

<body>
<header>
<div class="parent">
	<div id="h_bg"><img src="img/BG4.png"/></div>
	<div id="h_title">Platforma Smart Home</div>
</div>
</div>

</header>

<div id="INFOBAR">
	<span id="konsola">. . .</span>
</div>
	
<!--NAWIGACJA-->
<div class="parent">
<div id="sidenav" >
<span class="vtext" onclick="zwin()"></span>
<a href="index.php"><div class="menu_elem">Strona główna</div></a>
<a href="temperature.php"><div class="menu_elem"> > Temperatura</div></a>
<a href="humidity.php"><div class="menu_elem"> > Wilgotność powietrza</div></a>
<a href="pressure.php"><div class="menu_elem"> > Ciśnienie atmosferyczne</div></a>
<div id="fx1" >MENU</div>
</div>
</div>
<!--NAWIGACJA_END-->

<div id="content">

<div class="bigElem">
<div class="elem_head">Temperatura - zestawienie tabelaryczne</div>
	<table class="myTable" align="center" border="1" bordercolor="#d5d5d5"  cellpadding="4" cellspacing="0">
		<tr>
			<th></th>
			<th>Minimalna</th>
			<th>Maksymalna</th>
			<th>Średnia</th>
		</tr>
		<tr>
			<th id="th0">Ostatnia Godzina</th>
			<td id="t0_n">...</td>
			<td id="t0_x">...</td>
			<td id="t0_g">...</td>
		</tr>
		<tr>
			<th id="th1">Ostatnie 24h</th>
			<td id="t1_n">...</td>
			<td id="t1_x">...</td>
			<td id="t1_g">...</td>
		</tr>
		<tr>
			<th id="th2">Ostatni Tydzień</th>
			<td id="t2_n">...</td>
			<td id="t2_x">...</td>
			<td id="t2_g">...</td>
		</tr>
		<tr>
			<th id="th3">Ostatni Miesiąc</th>
			<td id="t3_n">...</td>
			<td id="t3_x">...</td>
			<td id="t3_g">...</td>
		</tr>
	</table>
</div>

<div class="bigElem">
<div class="elem_head">Temperatura - wykres</div>
	<div class="roomContainer">
		<div class="roomSubContainer">
		<span>
			<button type="button" class="btn1" value="dd" onclick="getDataToChart('Temperature','dd',chartObject);">Pobierz dane</button>
			Data - od: 
			<input type="date" name="dZakresMin" id="dZakresMin" 
			min="<?php print $minKnownFormDate ?>" 
			max="<?php print $maxKnownFormDate ?>" 
			value="<?php print $minKnownFormDate ?>"> - do: 
			<input type="date" name="dZakresMax" id="dZakresMax" 
			min="<?php print $minKnownFormDate ?>" 
			max="<?php print $maxKnownFormDate ?>"
			value="<?php print $maxKnownFormDate ?>">
			<span id="numInfo" style="color:white;"></span>
		</span>
		</div>

		<div class="roomSubContainer">
		<button type="button" class="btn1" value="dd" id="bt1" onclick="getDataToChart('Temperature','dz',chartObject);">Pobierz dane</button>
		<span>Przedział - od: <input type="datetime-local" name="fDate" id="fDate"> , ostatnie:
		<input type="radio" name="nZakres" id="nZakres1" value="1 HOUR">				<label for="nZakres1">1h </label>
		<input type="radio" name="nZakres" id="nZakres2" value="12 HOUR" >	<label for="nZakres2">12h </label>
		<input type="radio" name="nZakres" id="nZakres3" value="24 HOUR" checked="1">				<label for="nZakres2">24h </label>
		<input type="radio" name="nZakres" id="nZakres4" value="1 WEEK">				<label for="nZakres4">1 tyg. </label>
		<input type="radio" name="nZakres" id="nZakres5" value="2 WEEK">				<label for="nZakres5">2 tyg. </label>
		<input type="radio" name="nZakres" id="nZakres6" value="1 MONTH">				<label for="nZakres6">1 mies. </label>
		<input type="radio" name="nZakres" id="nZakres7" value="2 MONTH">				<label for="nZakres7">2 mies. </label>
		<input type="radio" name="nZakres" id="nZakres8" value="4 MONTH">				<label for="nZakres8">4 mies. </label>
		</span>
		</div>
	</div>

	<div class="parent">
		<canvas id="chart1" width="1400px" height="500px" style="position:relative; border:1px solid #d3d3d3; z-index:1;"></canvas>
	</div>

	<span><button type="button" class="btn1" id="CH1_SHOW_CONFIG" onclick="hide_show_config('chart1_setCont')">KONFIGURACJA</button> </span>

	<div class="settingsContainer" id="chart1_setCont">
	<div id="bccon2">  <span>....</span></div>
		<!-- klasa sama zbuduje tu menu ustawien -->
	</div>

</div>

<!--histogram-->
<div class="bigElem">
<div class="elem_head">Temperatura - histogram</div>

	<div class="parent">
		<canvas id="chart2" width="1400px" height="500px" style="position:relative; border:1px solid #d3d3d3; z-index:1;"></canvas>
	</div>

</div>

</div>

<footer>
	<?php echo "Markowiak Paweł &copy ".date("Y-m-d");?>
</footer>
</body>

<script src="js/charts.js?ts=<?=time()?>">			</script>
<script src="js/hists.js?ts=<?=time()?>">			</script>
<script src="js/globalScript.js?ts=<?=time()?>">	</script>

<script>
// tablice na dane X/Y (musza miec nazwy {dataY,dataX})
var dataY = [];
var dataX = [];

var histY = []
var histX = []


$("#fDate").val(getJSdateTime())

var chartObject = new markCharts("chart1","yes");
var histObject = new markHist("chart2");

window.addEventListener('resize', function(){reportWindowSize(chartObject,'C',dataY,dataX); reportWindowSize(histObject,'H',histY,histX);});

getHist('Temperature',0.5,histObject,4)

getDataToChart('Temperature','dz',chartObject);
reportWindowSize(chartObject,'C',dataY,dataX);
reportWindowSize(histObject,'H',histY,histX);
redraw()
getTabDataFromDB("Temperature") // dla tabel
</script>

</html>


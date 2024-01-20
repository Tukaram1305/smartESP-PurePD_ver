<!DOCTYPE HTML5>
<html>
<head>
<title>Strona główna</title>
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
<!--font awesome-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://kit.fontawesome.com/5f5c33d332.js" crossorigin="anonymous"></script> <!--MOj klucz FA5-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
<header>
<div class="parent">
	<div id="h_bg"><img src="img/BG4.png"/></div>
		<div id="h_title">Platforma Smart Home</div>
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
<a href="temperature.php"><div class="menu_elem"> &gt; Temperatura</div></a>
<a href="humidity.php"><div class="menu_elem"> &gt;	Wilgotność powietrza</div></a>
<a href="pressure.php"><div class="menu_elem"> &gt;	Ciśnienie atmosferyczne</div></a>
<div id="fx1" >MENU</div>
</div>
</div>
<!--NAWIGACJA_END-->

<div id="content">
<div class="roomContainer">
<div class="elem_head">Częstotliwość magazynowania danych</div>
	<div class="roomSubContainer">
		<div class="elem">
			<div class="elem_head">Ustawiona</div>
			<div><i class="fas fa-hourglass-half" style="font-size:56px;color:rgb(209, 66, 66)"></i></div><br>
			<div id="refFreqHolder" class="VALL_DISP">...</div>
		</div>
		<div class="elem">
			<div class="elem_head">Nowa</div>
			<div><i class="fa fa-clock-o" style="font-size:56px;color:rgb(53, 53, 53)"></i></div><br>
			<div><input class="numInputVal1" type="number" min="1" step="1" value="" id="refFreq" onchange="getSendRefFrequency('1')"> Min.</div>
		</div>
	</div>
</div>

<div class="roomContainer">
	<div class="elem_head">Czas pomiędzy wysyłaniem do ThingSpeak</div>
		<div class="roomSubContainer">
			<div class="elem">
				<div class="elem_head">Ustawiona</div>
				<div><i class="fas fa-hourglass-half" style="font-size:56px;color:rgb(209, 66, 66)"></i></div><br>
				<div id="refTSFreqHolder" class="VALL_DISP">...</div>
			</div>
			<div class="elem">
				<div class="elem_head">Nowa</div>
				<div><i class="fa fa-clock-o" style="font-size:56px;color:rgb(53, 53, 53)"></i></div><br>
				<div><input class="numInputVal1" type="number" min="1" step="1" value="" id="refTSFreq" onchange="getSendRefTSFrequency('1')"> Min.</div>
			</div>
		</div>
	</div>

	<!--Czujniki - kotlownia-->
	<div class="roomContainer">
	<div class="elem_head">Kotłownia</div>
	<div class="subNote" id="COdataTakenTimestamp"></div>
	<div class="roomSubContainer">
	<div class="elem">
		<div class="elem_head">Temp. CO</div>
		<div><i class="fa fa-thermometer-half" style="font-size:56px;color:rgb(250, 195, 76)"></i></div><br>
		<div id="COTempHolder" class="VALL_DISP">...</div>
	</div>

	<div class="elem">
		<div class="elem_head">LPG/Dym</div>
		<div><i class="fas fa-skull-crossbones" style="font-size:56px;color:rgb(194, 127, 214)"></i></div><br>
		<div id="BRGasHolder" class="VALL_DISP">...</div>
	</div>
	</div>
	</div>
	<!--Czujniki - kotlownia-->

<div class="roomContainer">
<div class="elem_head">Salon</div>
<div class="subNote" id="dataTakenTimestamp"></div>

	<div class="roomSubContainer">
		<div class="elem">
			<div class="elem_head">Temperatura</div>
			<div><i class="fas fa-temperature-high" style="font-size:56px;color:rgb(185, 8, 8)"></i></div><br>
			<div id="tempholder" class="VALL_DISP">...</div>
		</div>

		<div class="elem">
			<div class="elem_head">Wilgotność</div>
			<div><i class="fa fa-percent" style="font-size:56px;color:rgb(42, 158, 77)"></i></div><br>
			<div id="humholder" class="VALL_DISP">...</div>
		</div>

		<div class="elem">
			<div class="elem_head">Ciśnienie</div>
			<div><i class="fa fa-bar-chart" style="font-size:56px;color:rgb(51, 187, 211)"></i></div><br>
			<div id="pressholder" class="VALL_DISP">...</div>
		</div>
		<!-- OSWIETLENIE -->
		<div class="elem">
		<div class="elem_head">Taśma RGB LED</div>
			<div class="parent">
				<div class="LED_BG" id="LED1_bg"></div>
				<div class="LED_FG" style="padding:5px;"><i class="fa fa-lightbulb-o" style="font-size:56px;"></i></div>
			</div>
			<div id="LED1_val" class="VALL_MINOR_TXT">Wył.</div>
		<input id="LED1_inp" name="LED1_inp" style="width:140px; height:40px;" type="color" value="#000000" oninput="updateLED('LED1')">
		</div>
	</div>
</div>

<div class="roomContainer">
	<div class="elem_head">Pokój dzienny</div>
		<div class="roomSubContainer">
			<div class="elem">
			<div class="elem_head">Taśma LED</div>
				<div class="LED_FG" style="padding:5px;"><i class="fa fa-signal" style="font-size:56px; color:rgb(221, 214, 148);"></i></div><br>
				<input type="range" min="0" max="1023" value="512" class="slider" id="oneLedRangeVal" style="margin: 0 0 15 0;" onchange="sendOneLEDVal()">
				<div class="VALL_DISP" id="oneLedRangeValDisp">OFF</div>
			</div>
			<div class="elem">
			<div class="elem_head">Urządzenie #1</div>
				<div class="LED_FG" style="padding:5px;"><i class="fa fa-power-off" style="font-size:56px;color:rgb(96, 202, 206)"></i></div><br>
				<label class="switch">
				<input name="input1" id="chk1" type="checkbox" value="RELAY_1" onchange="sendSwitch()">
				<span class="sliderTGL round"></span>
				</label> 
				<div class="VALL_DISP" id="chkbox1">OFF</div>
			</div>
		</div>
	</div>

</div>

<footer>
	<?php echo "Markowiak Paweł &copy ".date("Y-m-d");?>
</footer>
</body>

<script src="js/globalScript.js?ts=<?=time()?>"></script>

<script>
getSendRefFrequency()
getSendRefTSFrequency()
</script>

</html>


$(document).ready(function(){
	httpGetData();
 	httpChkEspStatus(); 
	httpGetCOData();
	});

var KONSOLA = document.getElementById("konsola");
const ESP_IP = "192.168.1.35";

// konwersja HEX na RGB
function HEXtoRGB(hex){
	var vRGB = hex.replace('#','');
	var RGBVAL = parseInt(vRGB,16);
	var RGBArr = [];
	RGBArr[0] = RGBVAL >> 16; 
	RGBArr[1] = RGBVAL >> 8 & 255;
	RGBArr[2] = RGBVAL & 255;
	return RGBArr;
}

// konwersja HSL na HEX
function hslToHex(h, s, l) {
  l /= 100;
  const a = s * Math.min(l, 1 - l) / 100;
  const f = n => {
    const k = (n + h / 30) % 12;
    const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
    return Math.round(255 * color).toString(16).padStart(2, '0'); 
  };
  return `#${f(0)}${f(8)}${f(4)}`;
}

// przerysuj wykresy przy zmianie rozmaru okna
function redraw(){chartObject.plot(dataY,dataX); histObject.plot(histY,histX); }

function reportWindowSize(chart,type,dY,dX) {
	var adjWinW = window.innerWidth-100;
	chart.adjustWindowChartW(adjWinW);
	if (type=='C') chart.plot(dY,dX);
	if (type='H') chart.plot(dY,dX);
}

// data i czas
function getJSdateTime()
{
	var date = new Date();
	var day = date.getDate(),
		month = date.getMonth() + 1,
		year = date.getFullYear(),
		hour = date.getHours(),
		min  = date.getMinutes();

	month = (month < 10 ? "0" : "") + month;
	day = (day < 10 ? "0" : "") + day;
	hour = (hour < 10 ? "0" : "") + hour;
	min = (min < 10 ? "0" : "") + min;

	var currDate = year + "-" + month + "-" + day,
		currTime = hour + ":" + min; 
		var sqlDTFormat = currDate+"T"+currTime
		return sqlDTFormat;
}

// pobierz dane dla wykresu
function getDataToChart(dtype,input,chart){
	var xhr = new XMLHttpRequest();
	var sendReq = "sensorType="+dtype;
	if (input=="dz")
	{
		var dateTime = $("#fDate").val();
		var dataRange = document.querySelector('input[name="nZakres"]:checked').value;
		sendReq+="&startDate="+dateTime+"&Range="+dataRange;
	}
	if (input=="dd")
	{
		var dtStart = $("#dZakresMin").val();
		var dtEnd = $("#dZakresMax").val();
		sendReq+="&startDate="+dtStart+"&endDate="+dtEnd;
	}
	xhr.open("POST", "../smartesp/api/getChartDataFromDB.php", false);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			mJson = JSON.parse(this.responseText);
			var ile = Object.keys(mJson).length;
			dataY = [];
			dataX = [];
			for (let i=0; i<ile; i++)
				{
					dataY[i] = parseFloat(mJson[i][0]);
					dataX[i] = mJson[i][1];
				}
				chart.plot(dataY,dataX);
				$("#numInfo").html("Zanalzłem ("+ile+") rekordów")
			}
		};
		xhr.send(sendReq);
}

// pobierz dane dla tabeli
function getTabDataFromDB(dtype){
	var xhr = new XMLHttpRequest();
	var sendReq = "sensorType="+dtype;
	var unit = ""
	if (dtype=="Temperature") unit = " &#176;C";
	else if (dtype=="Pressure") unit = " hPa";
	else if (dtype=="Humidity") unit = " %";
	xhr.open("POST", "../smartesp/api/getTabDataFromDB.php", true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			mJson = JSON.parse(this.responseText);
			var subTsStyleMin = "\"font-size:16px; color: #b9b6de; text-shadow:none;\"";
			var subTsStyleMax = "\"font-size:16px; color: #ffd9da; text-shadow:none;\"";
			var subTsStyleAvg = "\"font-size:16px; color: #353535; text-shadow:none;\"";
			
			var aUP = "<img src='img/arrUp.png' width='30'/>"
			var aDOWN = "<img src='img/arrDown.png' width='30'/>"
			
			var rangeNames = {frst:'Ostatnie 48 godzin',
								sec:'Ostatnie 2 tygodnie',
								thrd:'Ostatni 1 miesiąc',
								frth:'Ostatnie 4 miesiące'}

			$("#t0_n").html(mJson.minHour.MIN+unit+(parseFloat(mJson.minHour.MIN) > parseFloat(mJson.minDay.MIN) ? aUP : aDOWN )+"<br><span style="+subTsStyleMin+">"+mJson.minHour.TS+"</span>");
			$("#t0_x").html(mJson.maxHour.MAX+unit+(parseFloat(mJson.maxHour.MAX) > parseFloat(mJson.maxDay.MAX) ? aUP : aDOWN )+"<br><span style="+subTsStyleMax+">"+mJson.maxHour.TS+"</span>");
			$("#t0_g").html(mJson.avgHour.AVG+unit+(parseFloat(mJson.avgHour.AVG) > parseFloat(mJson.avgDay.AVG) ? aUP : aDOWN ));
			$("#th0").html(rangeNames.frst+"<br><span style="+subTsStyleAvg+">Z ( "+mJson.HourNums.ILE+" ) Rekordów</span>");

			$("#t1_n").html(mJson.minDay.MIN+unit+(parseFloat(mJson.minDay.MIN) > parseFloat(mJson.minWeek.MIN) ? aUP : aDOWN )+"<br><span style="+subTsStyleMin+">"+mJson.minDay.TS+"</span>");
			$("#t1_x").html(mJson.maxDay.MAX+unit+(parseFloat(mJson.maxDay.MAX) > parseFloat(mJson.maxWeek.MAX) ? aUP : aDOWN )+"<br><span style="+subTsStyleMax+">"+mJson.maxDay.TS+"</span>");
			$("#t1_g").html(mJson.avgDay.AVG+unit+(parseFloat(mJson.avgDay.AVG) > parseFloat(mJson.avgWeek.AVG) ? aUP : aDOWN ));
			$("#th1").html(rangeNames.sec+"<br><span style="+subTsStyleAvg+">Z ( "+mJson.DayNums.ILE+" ) Rekordów</span>");

			$("#t2_n").html(mJson.minWeek.MIN+unit+(parseFloat(mJson.minWeek.MIN) > parseFloat(mJson.minMonth.MIN) ? aUP : aDOWN )+"<br><span style="+subTsStyleMin+">"+mJson.minWeek.TS+"</span>");
			$("#t2_x").html(mJson.maxWeek.MAX+unit+(parseFloat(mJson.maxWeek.MAX) > parseFloat(mJson.maxMonth.MAX) ? aUP : aDOWN )+"<br><span style="+subTsStyleMax+">"+mJson.maxWeek.TS+"</span>");
			$("#t2_g").html(mJson.avgWeek.AVG+unit+(parseFloat(mJson.avgWeek.AVG) > parseFloat(mJson.avgMonth.AVG) ? aUP : aDOWN ));
			$("#th2").html(rangeNames.thrd+"<br><span style="+subTsStyleAvg+">Z ( "+mJson.WeekNums.ILE+" ) Rekordów</span>");

			$("#t3_n").html(mJson.minMonth.MIN+unit+"<br><span style="+subTsStyleMin+">"+mJson.minMonth.TS+"</span>");
			$("#t3_x").html(mJson.maxMonth.MAX+unit+"<br><span style="+subTsStyleMax+">"+mJson.maxMonth.TS+"</span>");
			$("#t3_g").html(mJson.avgMonth.AVG+unit);
			$("#th3").html(rangeNames.frth+"<br><span style="+subTsStyleAvg+">Z ( "+mJson.MonthNums.ILE+" ) Rekordów</span>");

			var ile = Object.keys(mJson).length;
			}
		};
	xhr.send(sendReq);
}

// boczna nawigacja
var toggle = 1;
function zwin(){
	var snav = document.getElementById("sidenav");
	if (toggle){
		snav.style.marginLeft = -40; toggle=0;
		setTimeout(
		function (){var FX1=document.getElementById("fx1");
		FX1.style.width=400;},450);
		}
	else {snav.style.marginLeft = -380; toggle=1; var FX1=document.getElementById("fx1").style.width=0;}

}

// sprawdz status ESP32
function httpChkEspStatus(){
	var xmlhttp2 = new XMLHttpRequest();
	setInterval(
	function (){		
		xmlhttp2.open("GET", "http://"+ESP_IP+"/espStatus", true);
		xmlhttp2.send();
		xmlhttp2.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			KONSOLA.innerHTML = this.responseText;}
		else {KONSOLA.innerHTML = "ESP Jest OFFLINE";}
		};	
	}
	,5000);
}

// zapytaj o aktualne dane
function httpGetData(){		
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200) {
		var mJson = JSON.parse(this.responseText);
		var space = (mJson.D).indexOf(" ")
		var YDM = (mJson.D).substring(0,space)
		var HMS = (mJson.D).substring(space,(mJson.D).lenth)
		$("#tempholder").html(mJson.T +" &#176;C");
		$("#humholder").html(mJson.H+" %");
		$("#pressholder").html(mJson.C+"<br> hPa");
		$("#dataTakenTimestamp").html("Ostatnia aktualizacja: <br>"+HMS+" - "+YDM+"</font>");
		}
	};
	xhr.open("GET", "http://"+ESP_IP+"/getCurrData", true);
	xhr.send();
}
setInterval(httpGetData,10000);

// Zapytaj ESP o dane CO/GAS
function httpGetCOData(){		
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200) {
		var mJson = JSON.parse(this.responseText);
		var space = (mJson.D).indexOf(" ")
		var YDM = (mJson.D).substring(0,space)
		var HMS = (mJson.D).substring(space,(mJson.D).lenth)
		$("#COTempHolder").html(mJson.TCO +" &#176;C"); 
		var GASPCTG = mJson.GAS
		$("#BRGasHolder").html(GASPCTG+"/1024<br><p style='font-size:14px; margin: 0 0 0 0;'>("+HMS+" )</p>");
		}
	}; 
	xhr.open("GET", "http://"+ESP_IP+"/getCOData", true);
	xhr.send();		
}
setInterval(httpGetCOData,5000);

// pobierz dane do histogramu
function getHist(dataType,inc,hist,range){
	var xmlhttp = new XMLHttpRequest();		
	var req = "sensorType="+dataType+"&Increment="+inc+"&range="+range
	xmlhttp.open("POST", "../smartesp/api/getHistDataFromDB.php", true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send(req);
	xmlhttp.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200) {
		var json = JSON.parse(this.responseText)
		for (var i in json)
		{
			histY[i] = json[i][0]
			histX[i] = json[i][1]
		}
		hist.plot(histY,histX)
		}
	};	
}

// wyslij rzadanie o ustawienie LED
function updateLED(LEDnum)
{
	var LEDid = LEDnum
	var rgbCOL = document.getElementById(LEDid+"_inp").value
	document.getElementById(LEDid+"_bg").style.backgroundColor = rgbCOL;
	var RGBArr = HEXtoRGB(rgbCOL);
	if (RGBArr[0]==0 && RGBArr[1]==0 && RGBArr[2]==0)
	{ document.getElementById(LEDid+"_val").innerHTML = "Wył." }
	else
	{ document.getElementById(LEDid+"_val").innerHTML = RGBArr[0]+" "+RGBArr[1]+" "+RGBArr[2]; }
	
	var mesage = "Red="+RGBArr[0]+"&Green="+RGBArr[1]+"&Blue="+RGBArr[2];
	var xhr = new XMLHttpRequest();
	xhr.open("GET", "http://"+ESP_IP+"/get"+LEDid+"?"+mesage, true);
		xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			KONSOLA.innerHTML = "ESP ODP: "+this.responseText;
			}
		};
		xhr.send(null);
}	

function LEDsOFF(lNum){
	document.getElementById(lNum+"_inp").value = "#000000"
	updateLED(lNum)
}

// wyslij zadanie o zmiane stanu przekaznika
function sendSwitch(){
	var xhr = new XMLHttpRequest();
	var swstate = document.getElementById("chk1").checked;
	$("#chkbox1").html((swstate) ? "<font color='green'>ON</font>" : "<font color='red'>OFF</font>")
	xhr.open("GET", "http://"+ESP_IP+"/getSW1?input1="+!swstate, true);
		xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			console.log(this.responseText)
			}
		};
		xhr.send(null);
}

// wyslij zadanie o zmiane jednokanalowej tasmy LED
function sendOneLEDVal(){
	var xhr = new XMLHttpRequest();
	var ledValState = document.getElementById("oneLedRangeVal").value;
	console.log(ledValState);
	$("#oneLedRangeValDisp").html(ledValState > 0 ? Math.ceil(ledValState*0.09765625)+"%" : "OFF")
	xhr.open("GET", "http://"+ESP_IP+"/getONE_LED_W1?input1="+ledValState, true);
		xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {}};
		xhr.send(null);
}

// zmiana/pozyskanie czasu pomiedzy agregowaniem danych
function getSendRefFrequency(update)
{
	var xhr = new XMLHttpRequest();
	if (update==="1")
	{
		var RF = $("#refFreq").val()
		xhr.open("GET", "http://"+ESP_IP+"/getSetRefresh?input1="+RF, true);
	}
	else {xhr.open("GET", "http://"+ESP_IP+"/getSetRefresh", true);}
		xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			$("#refFreqHolder").html(this.responseText+" min.")
			$("#refFreq").val(parseInt(this.responseText,10))
			}
		};
		xhr.send(null);
}

// zmiana/pozyskanie czasu pomiedzy wysylaniem do ThingSpeak
function getSendRefTSFrequency(update){
	var xhr = new XMLHttpRequest();
	if (update==="1")
	{
		var RF = $("#refTSFreq").val()
		xhr.open("GET", "http://"+ESP_IP+"/getSetTSRefresh?input1="+RF, true);
	}
	else {xhr.open("GET", "http://"+ESP_IP+"/getSetTSRefresh", true);}
		xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			$("#refTSFreqHolder").html(this.responseText+" min.")
			$("#refTSFreq").val(parseInt(this.responseText,10))
			}
		};
		xhr.send(null);
}
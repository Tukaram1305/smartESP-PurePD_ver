// pomocnicza funkcja dla zmiany rozmiaru okna i przerysowania wykresow
function reportWindowSize(chart,type) {
	var adjWinW = window.innerWidth-100;
	chart.adjustWindowChartW(adjWinW);
	if (type=='C') chart.plot(dataY,dataX);
	if (type='H') chart.plot(histY,histX);
}


// funkcja pobierania danych z bazy dla wykresu
/* PARAMETRY:
dtype (string) - nazwa kolumny, jak 'Temperature'
input (string) - 'dz' lub 'dd' - 'dz' = Datetime > Zakres / 'dd' = DateTime > DateTime (4.2.2015 > 15.3.2016)
chart (string) - ID obiektu HTML canvas na ktorym rysowany bedzie wykres
*/
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
			console.log('Chart:'+mJson)
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

// funkcja pobierania danych z bazy dla histogramu
/*PARAMETRY:
dataType (string) - nazwa kolumny, jak  'Temperature'
inc (int) - inkrementator pomiedzy pobieranymi i sumowanymi wielkosciami
hist (string) - ID obiektu HTML canvas na ktorym rysowany bedzie wykres
range (number) - zakres czasu dla zliczanych wartosci (w miesiacach)
*/
function getHist(dataType,inc,hist,range){
	var xmlhttp = new XMLHttpRequest();		
	var req = "sensorType="+dataType+"&Increment="+inc+"&range="+range
	xmlhttp.open("POST", "../smartesp/api/getHistDataFromDB.php", true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send(req);
	xmlhttp.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200) {
		var json = JSON.parse(this.responseText)
		console.log(json)
		for (var i in json)
		{
			histY[i] = json[i][0]
			histX[i] = json[i][1]
		}
		hist.plot(histY,histX)
		}
	};	
}
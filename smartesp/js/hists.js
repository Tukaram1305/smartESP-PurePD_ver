// Markowiak Pawel - klasa markCharts dla rysowania wykresow

// Moja klasa dla rysowania histogramow - konstruktor przyjmuje ID canvas jaki bedzie uzywany
class markHist
{
	constructor(canID)
	{
		this.canID = canID;
		this.canvas = document.getElementById(canID);
		this.chartBordLeft = 0;
		this.chartBordBot = 100;
		this.chartBordRight = 30;

		this.winWidChngParams = 1080;
		this.chartTOPBOT_margin = 20;

		this.ChartMaxWidth = 2048;
		//FONT
		this.labFntName = "sans-serif"
		this.labFntSize = 10
		this.LabFont = this.labFntSize+"px "+this.labFntName;
	}

	adjustWindowChartW(adjWinW)
	{ this.canvas.width = Math.min(adjWinW,this.ChartMaxWidth); }

	plot (daneY,daneX){
		var ctx = this.canvas.getContext("2d");
		// autoreg dolnego marginesu
		var labLen;
		if (typeof daneX[daneX.length-1] == Number) labLen = (daneX[daneX.length-1].toFixed(2)).toString();
		else labLen = daneX[daneX.length-1];
		this.chartBordBot = ctx.measureText(labLen).width+20

		// --- PARAMETRY KONFIGURACJI ---
		this.canvas.height = 440;
		var BTN_L_MARGIN_COL = '#020202';
		var BTN_B_MARGIN_COL = '#020202';
		var BG_GRD_1 = '#121212';
		var BG_GRD_2 = '#131313';
		var BG_GRD_SPAN = 0.5
		
		const RAD = 0.01745329251994329576923690768489;
		// charakterystyka danych
		var dataNumElem = daneY.length;
		var dataMIN = Math.min(...daneY);
		var dataMAX = Math.max(...daneY);
		var dataMAXMOD; if (Math.abs(dataMIN)>Math.abs(dataMAX)) dataMAXMOD = Math.abs(dataMIN); else  dataMAXMOD = Math.abs(dataMAX);
		var dataRange = dataMAX-dataMIN;

		var CanW = this.canvas.width;
		var CanH = this.canvas.height;

		var ChartW = this.canvas.width-this.chartBordLeft-this.chartBordRight;
		var ChartH = this.canvas.height-this.chartBordBot;

		var ChartLS = this.chartBordLeft;
		if (window.innerWidth<this.winWidChngParams)
		{
			 ChartLS = this.chartBordLeft/2;
			 ChartW = this.canvas.width-ChartLS-this.chartBordRight;
		}
		else ChartLS = this.chartBordLeft;

		ctx.globalAlpha = 1;
		// budowanie gradientu
		var bggrd = ctx.createLinearGradient(0, 0, 0, ChartH);
		bggrd.addColorStop(0, BG_GRD_1);
		bggrd.addColorStop(BG_GRD_SPAN, BG_GRD_1);
		bggrd.addColorStop(0.5, BG_GRD_2);
		bggrd.addColorStop(1-BG_GRD_SPAN, BG_GRD_1);
		bggrd.addColorStop(1, BG_GRD_1);

		// Tlo calosci
		ctx.fillStyle = bggrd;
		ctx.fillRect(0, 0, CanW, CanH);
		// Tlo lewego marginesu
		ctx.fillStyle = BTN_L_MARGIN_COL;
		ctx.fillRect(0, 0, ChartLS, ChartH);
		// Tlo dolnego marginesu
		ctx.fillStyle = BTN_B_MARGIN_COL;
		ctx.fillRect(0, ChartH, CanW, CanH-ChartH);
		ctx.globalAlpha = 1;

// linie podzialow | --
	// --- [  (ChartH/2-20) ] - odsuniecie 20px z gory i dolu dla klarownosci
	// glowny wspolczynnik rozpietosci W wykresu/daneY(max modul)---------poprawic------------------------------------------
	var coefH = (ChartH/2-this.chartTOPBOT_margin)/(dataMAXMOD);
	// glowny wspolczynnik rozpietosci H wykresu/ilosc elem-------------------------------------------
	var coefV = (ChartW/dataNumElem);

	var pivot;  // --- os top/mid/bot
	var zero = 0;
	var czesc ="all";
	// same dodatnie
	if (dataMIN >=0)
	{
		if (dataMAXMOD>dataRange) zero = Math.floor(dataMIN);
		pivot = ChartH;
		coefH = (ChartH-this.chartTOPBOT_margin)/(dataMAXMOD-zero);
	}
	// same ujemne
	else if (dataMAX <=0)
	{
		if (dataMAXMOD>dataRange) zero = Math.ceil(dataMAX);
		pivot = 0;
		coefH = (ChartH-this.chartTOPBOT_margin)/(dataMAXMOD+zero);
	}
	// rowny podzial
	else  {pivot= Math.round(ChartH/2-zero); czesc="half" }

	var coefGrid;
	var newHdiv;
	var ile_pod;
	if (czesc=="all") 	{ ile_pod = Math.floor(ChartH/this.labFntSize); }
	else 				{ ile_pod = Math.floor(ChartH/2/this.labFntSize); }
	
	newHdiv = ile_pod;

	 if (czesc=="all")  coefGrid = (ChartH-this.chartTOPBOT_margin)/newHdiv;
	 else 				coefGrid = (ChartH-this.chartTOPBOT_margin*2)/2/newHdiv;

	for (let h=1; h<=newHdiv; h++)
	{
		ctx.beginPath();
		ctx.moveTo(0, pivot-coefGrid*h);
			if ( pivot!=0 && h%(2)==0) 		ctx.lineTo(ChartW+ChartLS, pivot-coefGrid*h);
			if (h%(4)==0) ctx.strokeStyle = '#aaaaaa'; else ctx.strokeStyle = '#ababab'; 
		
		ctx.lineWidth = 1;
		ctx.globalAlpha = 0.35;
		ctx.stroke();
	}
	// GRIND HORYZONTALNY ----------------------------------------------------------------------------------------------GRID H
	// dostosowywanie rozpietosci dolnych etykiet
	var numSpanV = 1

	// GRIND WERTYKALNY ----------------------------------------------------------------------------------------------- GRID V
	for (let w=0; w<dataNumElem; w++)
	{
		ctx.beginPath();
		ctx.globalAlpha = 1;
		ctx.fillStyle = "white";
		ctx.font = this.LabFont;
		ctx.textAlign = "right";
		ctx.strokeStyle = '#fcfcfc'; 
		ctx.globalAlpha = 0.75;
		ctx.rotate(-90*RAD);
		if (w%numSpanV==0)
		{  ctx.fillText((daneX[w]), -1*(ChartH+10), ChartLS+(ChartW/dataNumElem/2)+(coefV)*w); } 
		ctx.rotate(90*RAD);
		ctx.closePath();
		ctx.stroke();
	}

		// etykiety
		ctx.beginPath();
		ctx.globalAlpha = 1;
		ctx.fillStyle = "white";
		ctx.textAlign = "left";
		ctx.font = "14px sans-serif";
		ctx.textAlign = "center";
		ctx.beginPath();
		ctx.moveTo(ChartLS, pivot);
		// glowne rysowanie wykresu
		for (var i=0; i<dataNumElem; i++)
		{
			var ccoef = 220/dataMAXMOD // adekwatny kolor dla wielkosci bloku
			var hex = hslToHex(120+daneY[i]*ccoef,50,50)
			ctx.fillStyle = hex
			ctx.fillRect(ChartLS+(i)*coefV,ChartH,(ChartW/(dataNumElem+1)),-1*daneY[i]*coefH)
			ctx.fillStyle = "white";
			ctx.fillText((daneY[i]), ChartLS+(i)*coefV+(ChartW/dataNumElem/2), ChartH-daneY[i]*coefH-5);
			//ctx.lineTo(ChartLS+(i)*coefV, pivot-((daneY[i]-zero)*(coefH)));
		}
		// chart top/bot margin
		ctx.globalAlpha = 0.18;
		ctx.fillStyle = "black";
		ctx.fillRect(ChartLS, 0, ChartW, this.chartTOPBOT_margin);
		ctx.globalAlpha = 1;
	} //---plot
}

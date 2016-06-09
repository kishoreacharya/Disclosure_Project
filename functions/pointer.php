<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function flevDivPositionValue(sDiv, sProperty) { // v2.1, Marja Ribbers-de Vroed, FlevOOware
	this.opera = (window.opera); // Opera 5+
	this.ns4 = (document.layers); // Netscape 4.x
	this.ns6 = (document.getElementById && !document.all && !this.opera); // Netscape 6+
	this.ie = (document.all);  // Internet Explorer 4+
    var sValue = ""; docObj = eval("MM_findObj('" + sDiv + "')"); if (docObj == null) {return 0;}
	if ((sProperty == "left") || (sProperty == "top")) {
		if (!this.ns4) {docObj = docObj.style;} 
		sValue = eval("docObj." + sProperty);
		if ((this.ie) && (sValue == "")) { // IE (on PC) bug with nested layers
			if (sProperty == "top") { sValue = eval(sDiv + ".offsetTop"); } 
			else { sValue = eval(sDiv + ".offsetLeft"); } 
		};
	}
	else {
		if (this.opera) {
			docObj = docObj.style;
			if (sProperty == "height") { sValue = docObj.pixelHeight; } 
			else if (sProperty == "width") { sValue = docObj.pixelWidth; } 
		}
		else if (this.ns4) {sValue = eval("docObj.clip." + sProperty);} 
		else if (this.ns6) {sValue = document.defaultView.getComputedStyle(docObj, "").getPropertyValue(sProperty); } 
	    else if (this.ie) { 
			if (sProperty == "width") { sValue = eval(sDiv + ".offsetWidth"); } 
			else if (sProperty == "height") { sValue = eval(sDiv + ".offsetHeight"); } 
		}
   	}
	sValue = (sValue == "") ? 0 : sValue; 
	if (isNaN(sValue)) { if (sValue.indexOf('px') > 0) { sValue = sValue.substring(0,sValue.indexOf('px')); } } 
	return parseInt(sValue); 
}

function flevPersistentLayer() { // v3.3, Marja Ribbers-de Vroed, FlevOOware
	var sD = arguments[0], oD = eval("MM_findObj('" + sD + "')"), iWW, iWH, iSX, iSY, iT = 10, sS = "";
	if (!document.layers) {oD = oD.style;}
	if (oD.tmpTimeout != null) {clearTimeout(oD.tmpTimeout);}
	var sXL = arguments[1], sXC = arguments[2], sXR = arguments[3], sYT = arguments[4], sYC = arguments[5], sYB = arguments[6];
	var iS = (arguments.length > 7) ? parseInt(arguments[7]) : 0, iPx = (arguments.length > 8) ? parseInt(arguments[8]) : 0;
	if (window.innerWidth) { // NS4, NS6 and Opera
		var oW = window; iWW = oW.innerWidth; iWH = oW.innerHeight; iSX = oW.pageXOffset; iSY = oW.pageYOffset; }
	else if (document.documentElement && document.documentElement.clientWidth) { // IE6 in standards compliant mode
		var oDE = document.documentElement; iWW = oDE.clientWidth; iWH = oDE.clientHeight; iSX = oDE.scrollLeft; iSY = oDE.scrollTop; }
	else if (document.body) { // IE4+
		var oDB = document.body; iWW = oDB.clientWidth; iWH = oDB.clientHeight; iSX = oDB.scrollLeft; iSY = oDB.scrollTop; }
	else {return;}
	var iCX = iNX = flevDivPositionValue(sD, 'left'), iCY = iNY = flevDivPositionValue(sD, 'top');
	if (sXL != "") {iNX = iSX + parseInt(sXL);} 
	else if (sXC != "") {iNX = Math.round(iSX + (iWW/2) - (flevDivPositionValue(sD, 'width')/2));}
	else if (sXR != "") {iNX = iSX + iWW - (flevDivPositionValue(sD, 'width') + parseInt(sXR));}
	if (sYT != "") {iNY = iSY + parseInt(sYT);}
	else if (sYC != "") {iNY = Math.round(iSY + (iWH/2) - (flevDivPositionValue(sD, 'height')/2));}
	else if (sYB != "") {iNY = iSY + (iWH - flevDivPositionValue(sD, 'height') - parseInt(sYB));}
	if ((iCX != iNX) || (iCY != iNY)) {
		if (iS > 0) {
			if (iPx > 0) { iT = iS;
				var iPxX = iPx, iPxY = iPx, iMX = Math.abs(iCX - iNX), iMY = Math.abs(iCY - iNY);
				// take care of diagonal movement
				if (iMX < iMY) {iPxY = (iMX != 0) ? ((iMY/iMX)*iPx) : iPx;}
				else {iPxX = (iMY != 0) ? ((iMX/iMY)*iPx) : iPx;}
				if (iPxX >= iMX) {iPxX = Math.min(Math.ceil(iPxX), iPx);}
				if (iPxY >= iMY) {iPxY = Math.min(Math.ceil(iPxY), iPx);}
				// temporary X/Y coordinates
				if ((iCX < iNX) && (iCX + iPxX < iNX)) {iNX = iCX + iPxX;}
				if ((iCX > iNX) && (iCX - iPxX > iNX)) {iNX = iCX - iPxX;}
				if ((iCY < iNY) && (iCY + iPxY < iNY)) {iNY = iCY + iPxY;}
				if ((iCY > iNY) && (iCY - iPxY > iNY)) {iNY = iCY - iPxY;} }
			else { 
				var iMX = ((iNX - iCX) / iS), iMY = ((iNY - iCY) / iS); 
				iMX = (iMX > 0) ? Math.ceil(iMX) : Math.floor(iMX); iNX = iCX + iMX; 
				iMY = (iMY > 0) ? Math.ceil(iMY) : Math.floor(iMY); iNY = iCY + iMY; } }
		if ((parseInt(navigator.appVersion)>4 || navigator.userAgent.indexOf("MSIE")>-1) && (!window.opera)) {sS="px";}
		if (iMX != 0) {eval("oD.left = '" + iNX + sS + "'");}
		if (iMY != 0) {eval("oD.top = '" + iNY + sS + "'");} }
	var sF = "flevPersistentLayer('" + sD + "','" + sXL + "','" + sXC + "','" + sXR + "','" + sYT + "','" + sYC + "','" + sYB + "'," + iS + "," + iPx + ")";
	oD.tmpTimeout = setTimeout(sF,10);
}

function flevInitPersistentLayer() { // v3.3, Marja Ribbers-de Vroed, FlevOOware
	if (arguments.length < 8) {return;}
	var sD = arguments[0]; if (sD == "") {return;}
	var	oD = eval("MM_findObj('" + sD + "')"); if (!oD) {return;}
	var iCSS = parseInt(arguments[1]);
	var sXL = arguments[2], sXC = arguments[3], sXR = arguments[4], sYT = arguments[5], sYC = arguments[6], sYB = arguments[7];
	var iS = (arguments.length > 8) ? parseInt(arguments[8]) : 0, iPx = (arguments.length > 9) ? parseInt(arguments[9]) : 0;
	if (iCSS != 0) { if (!document.layers) {oD = oD.style;} sXL = parseInt(oD.left), sYT = parseInt(oD.top);}
	var sF = "flevPersistentLayer('" + sD + "','" + sXL + "','" + sXC + "','" + sXR + "','" + sYT + "','" + sYC + "','" + sYB + "'," + iS + "," + iPx + ")";
	eval(sF);
}

function setPointer(theRow, theAction, theDefaultColor, thePointerColor, theMarkColor)
{
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof(document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    }
    else if (typeof(theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    }
    else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt  = theCells.length;
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect    = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect    = false;
    } // end 3

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor = thePointerColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor = theMarkColor;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()) {
        if (theAction == 'out') {
            newColor = theDefaultColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor = theMarkColor;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
        if (theAction == 'click') {
            newColor = (thePointerColor != '')
                     ? thePointerColor
                     : theDefaultColor;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'setPointer()' function
//-->
</script>
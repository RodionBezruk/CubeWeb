DOM = (document.getElementById) ? 1 : 0;
NS4 = (document.layers) ? 1 : 0;
IE4 = (document.all) ? 1 : 0;
var loaded = 0;	
Konqueror = (navigator.userAgent.indexOf("Konqueror") > -1) ? 1 : 0;
Opera5 = (navigator.userAgent.indexOf("Opera 5") > -1 || navigator.userAgent.indexOf("Opera/5") > -1 || navigator.userAgent.indexOf("Opera 6") > -1 || navigator.userAgent.indexOf("Opera/6") > -1) ? 1 : 0;
currentY = -1;
function grabMouse(e) {
	if ((DOM && !IE4) || Opera5) {
		currentY = e.clientY;
	} else if (NS4) {
		currentY = e.pageY;
	} else {
		currentY = event.y;
	}
	if (DOM && !IE4 && !Opera5 && !Konqueror) {
		currentY += window.pageYOffset;
	} else if (IE4 && DOM && !Opera5 && !Konqueror) {
		currentY += document.body.scrollTop;
	}
}
if ((DOM || NS4) && !IE4) {
	document.captureEvents(Event.MOUSEDOWN | Event.MOUSEMOVE);
}
document.onmousemove = grabMouse;
function popUp(menuName,on) {
	if (loaded) {	
		if (on) {
			if (DOM) {
				document.getElementById(menuName).style.visibility = "visible";
				document.getElementById(menuName).style.zIndex = 1000;
			} else if (NS4) {
				document.layers[menuName].visibility = "show";
				document.layers[menuName].zIndex = 1000;
			} else {
				document.all[menuName].style.visibility = "visible";
				document.all[menuName].style.zIndex = 1000;
			}
		} else {
			if (DOM) {
				document.getElementById(menuName).style.visibility = "hidden";
			} else if (NS4) {
				document.layers[menuName].visibility = "hide";
			} else {
				document.all[menuName].style.visibility = "hidden";
			}
		}
	}
}
function setleft(layer,x) {
	if (DOM) {
		document.getElementById(layer).style.left = x;
		document.getElementById(layer).style.left = x + 'px';
	} else if (NS4) {
		document.layers[layer].left = x;
	} else {
		document.all[layer].style.pixelLeft = x;
	}
}
function settop(layer,y) {
	if (DOM) {
		document.getElementById(layer).style.top = y;
		document.getElementById(layer).style.top = y + 'px';
	} else if (NS4) {
		document.layers[layer].top = y;
	} else {
		document.all[layer].style.pixelTop = y;
	}
}
function setwidth(layer,w) {
	if (DOM) {
		document.getElementById(layer).style.width = w;
		document.getElementById(layer).style.width = w + 'px';
	} else if (NS4) {
	} else {
		document.all[layer].style.pixelWidth = w;
	}
}
function moveLayerY(menuName, ordinata, e) {
	if (loaded) {	
		if (ordinata != -1 && !isNaN(ordinata)) {	
			if (DOM) {
				if (e && e.clientY) { 
					document.getElementById(menuName).style.top = e.clientY + 'px';
				} else {
					appoggio = parseInt(document.getElementById(menuName).style.top);
					if (isNaN(appoggio)) appoggio = 0;
					if (Math.abs(appoggio + ordinata_margin - ordinata) > thresholdY)
						document.getElementById(menuName).style.top = (ordinata - ordinata_margin) + 'px';
				}
			} else if (NS4) {
					if (Math.abs(document.layers[menuName].top + ordinata_margin - ordinata) > thresholdY)
						document.layers[menuName].top = ordinata - ordinata_margin;
			} else {
				if (Math.abs(document.all[menuName].style.pixelTop + ordinata_margin - ordinata) > thresholdY)
					document.all[menuName].style.pixelTop = ordinata - ordinata_margin;
			}
		}
	}
}

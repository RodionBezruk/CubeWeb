Number.prototype.RoundTo = function( precission )
{
	var base = Math.pow(10, precission) ;
	return Math.round( this * base ) / base ;
} ;
function Import(aSrc) {
   document.write('<scr'+'ipt type="text/javascript" src="' + aSrc + '"></sc' + 'ript>');
}
var oEditor		= window.parent.InnerDialogLoaded() ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;
var FCKTools = oEditor.FCKTools ;
Import(FCKConfig.FullBasePath + 'dialog/common/fck_dialog_common.js');
Import('http:
Import('polyline.js');
window.parent.AddTab( 'Map', FCKLang.GMapsMap ) ;
window.parent.AddTab( 'Search', FCKLang.GMapsSearch ) ;
window.parent.AddTab( 'Marker', FCKLang.GMapsMarker ) ;
window.parent.AddTab( 'Line', FCKLang.GMapsLine ) ;
var ActiveTab ;
function OnDialogTabChange( tabCode )
{
	ActiveTab = tabCode ;
	ShowE('MapInfo', ( tabCode == 'Map' ) ) ;
	ShowE('SearchInfo', ( tabCode == 'Search' ) ) ;
	ShowE('MarkerInfo', ( tabCode == 'Marker' ) ) ;
	ShowE('LineInfo', ( tabCode == 'Line' ) ) ;
	if (tabCode == 'Line')
		ShowLinePoints()
	else
		HideLinePoints();
	if (tabCode != 'Marker')
		FinishAddMarker() ;
	ResizeParent() ;
}
var oFakeImage = FCK.Selection.GetSelectedElement() ;
var oParsedMap ;
if ( oFakeImage )
{
	if ( oFakeImage.parsedMap )
	{
		oParsedMap = oFakeImage.parsedMap ;
		oParsedMap.updateDimensions( oFakeImage );
	}
	else
		oFakeImage = null ;
}
if ( !oParsedMap )
		oParsedMap = oEditor.createGoogleMap() ;
window.onload = function()
{
	window.onunload = GUnload ;
	oEditor.FCKLanguageManager.TranslatePage(document) ;
	var btn = GetE('btnAddNewMarker') ;
	btn.alt = btn.title = FCKLang.GMapsAddMarker ;
	LoadSelection() ;
	ConfigureEvents() ;
	SetupHelpButton( oEditor.FCKPlugins.Items['googlemaps'].Path + 'docs/' + FCKLang.GMapsUserHelpFile ) ;
	window.parent.SetOkButton( true ) ;
	if (window.parent.Sizer) window.parent.SetAutoSize( true ) ;
} ;
function ConfigureEvents()
{
	GetE('txtWidth').onblur = UpdateDimensions ;
	GetE('txtHeight').onblur = UpdateDimensions ;
	GetE('cmbZoom').onchange  = UpdatePreview ;
	GetE('btnAddNewMarker').onclick = function () {AddMarker(); return false;};
	FCKTools.AddEventListener(GetE('searchDirection') , 'keydown', searchDirection_keydown) ;
	GetE('searchButton').onclick = function () {doSearch(); return false;};
}
function searchDirection_keydown(e)
{
	if (!e) e = window.event ;
	if ( e.keyCode == 13 )
	{
		doSearch();
		if (e.preventDefault) e.preventDefault() ;
		if (e.stopPropagation) e.stopPropagation() ;
		return false;
	}
}
function LoadSelection()
{
	GetE('txtWidth').value  = oParsedMap.width ;
	GetE('txtHeight').value = oParsedMap.height ;
	GetE('cmbZoom').value  = oParsedMap.zoom ;
	GetE('txtCenterLatitude').value  = oParsedMap.centerLat ;
	GetE('txtCenterLongitude').value = oParsedMap.centerLon ;
	var markerPoints = oParsedMap.markerPoints;
	GetE('encodedPolyline').value = oParsedMap.LinePoints ;
	GetE('encodedLevels').value = oParsedMap.LineLevels ;
	SetPreviewElement() ;
	UpdatePreview() ;
	for (var i=0; i<markerPoints.length ; i++)
	{
		var point = new GLatLng(parseFloat(markerPoints[i].lat), parseFloat(markerPoints[i].lon))
		AddMarkerAtPoint(point, markerPoints[i].text, false);
	}
	decodePolyline() ;
}
function Ok()
{
	oEditor.FCKUndo.SaveUndoStep() ;
	oParsedMap.width = GetE('txtWidth').value ;
	oParsedMap.height = GetE('txtHeight').value ;
	oParsedMap.zoom = GetE('cmbZoom').value ;
	oParsedMap.centerLat = GetE('txtCenterLatitude').value ;
	oParsedMap.centerLon = GetE('txtCenterLongitude').value ;
	var markerPoints = [];
	for (var i=0; i<markers.length ; i++)
	{
		var point = markers[i].getPoint() ;
		markerPoints.push({lat: point.lat().RoundTo(5), lon:point.lng().RoundTo(5), text:markers[i].text.replace(/\'/g, "\\'")});
	}
	oParsedMap.markerPoints = markerPoints ;
	oParsedMap.LinePoints = GetE('encodedPolyline').value ;
	oParsedMap.LineLevels = GetE('encodedLevels').value ;
	var script = oParsedMap.BuildScript() ;
	if ( !oFakeImage )
		oFakeImage = oParsedMap.createHtmlElement() ;
	oParsedMap.updateHTMLElement(oFakeImage);
	return true ;
}
var map ;
var mapDiv ;
var geocoder ;
var markers = [] ;
var activeMarker = null;
var Mode = '' ;
function SetPreviewElement()
{
	mapDiv = document.getElementById("GMapPreview") ;
	UpdateDimensions() ;
	if(!GBrowserIsCompatible())
		return;
	map = new GMap2(mapDiv);
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
	GEvent.addListener(map, "zoomend", Map_ZoomEnd); 
	GEvent.addListener(map, "drag", Map_Drag); 
  GEvent.addListener(map, "click", Map_Click);
  GEvent.addDomListener(mapDiv, "keydown", DomMap_KeyDown);
}
function Map_ZoomEnd(oldLevel, newLevel)
{
	GetE('cmbZoom').value = newLevel ;
}
function Map_Drag()
{
	var point = map.getCenter() ;
	GetE("txtCenterLatitude").value = point.lat().RoundTo(5) ;
	GetE("txtCenterLongitude").value = point.lng().RoundTo(5) ;
}
function Map_MoveEnd()
{
	Map_Drag() ;
}
function Map_Click(overlay, point)
{
	if ( !overlay )
	{
		switch(ActiveTab) {
			case 'Map':
				break;
			case 'Marker':
				if (Mode == 'AddMarker')
					AddMarkerAtPoint( point, FCKConfig.GoogleMaps_MarkerText || FCKLang.GMapsMarkerDefaultText, true ) ;
				break;
			case 'Line':
				createPoint(point.lat(), point.lng(), 3);
				createEncodings(false);
				break;
		}
	}
	if (!overlay || overlay.focusable)
	{
		mapDiv.focus();
	}
}
function DomMap_KeyDown( e )
{
	if ( !e )
		e = window.event ;
	var iCode = ( e.keyCode || e.charCode ) ;
	if (iCode == 46)
		switch(ActiveTab) {
			case 'Map':
				break;
			case 'Marker':
				break;
			case 'Line':
				deletePoint() ;
				break;
		}
}
function UpdatePreview()
{
	if ( !map )
		return ;
	var zoom = parseInt(GetE('cmbZoom').value, 10) ;
	map.setCenter(new GLatLng(GetE('txtCenterLatitude').value, GetE('txtCenterLongitude').value), zoom);
	map.setMapType(G_NORMAL_MAP);
}
function UpdateDimensions()
{
	mapDiv.style.width = GetE('txtWidth').value + 'px' ;
	mapDiv.style.height = GetE('txtHeight').value + 'px' ;
	ResizeParent();
}
function createMarker(point, html) 
{
	var marker = new GMarker(point, {draggable: true});
	marker.text = html ;
	GEvent.addListener(marker, "click", function() {
		EditMarker(this) ;
	});
	return marker;
}
function generateEditPopupString(text)
{
	return '<div style="width:250px; height:7em;">' +
			'<label for="txtMarkerText">' + FCKLang.GMapsMarkerText + '</label><br>' +
			'<textarea id="txtMarkerText" style="width:250px; height:4em;">' + text + '</textarea><br>' +
			'<div style="float:left"><input type="button" id="btnDeleteMarker" onclick="DeleteCurrentMarker()" value="' + FCKLang.GMapsDeleteMarker + '"></div>' +
			'<div style="float:right"><input type="button" id="btnOK2" onclick="UpdateCurrentMarker()" value="' + FCKLang.DlgBtnOK + '">' +
			'<input type="button" id="btnCancel2" onclick="CloseInfoWindow()" value="' + FCKLang.DlgBtnCancel + '"></div>' +
		'</div>'
		;
}
function doSearch()
{
	if (!geocoder) geocoder = new GClientGeocoder();
	function processPoint(point)
	{
			if (point) 
			{
				GetE("txtCenterLatitude").value = point.lat().RoundTo(5) ;
				GetE("txtCenterLongitude").value = point.lng().RoundTo(5) ;
				AddMarkerAtPoint( point, GetE('searchDirection').value ) ;
				UpdatePreview() ;
			}
			else {
				alert( FCKLang.GMapsNotFound.replace("%s", GetE('searchDirection').value) ) ;
			}
	}
	geocoder.getLatLng ( GetE('searchDirection').value, processPoint ) ;
}
function AddMarker()
{
	if (Mode=='AddMarker')
	{
		FinishAddMarker() ;
		return ;
	}
	GetE( 'btnAddNewMarker' ).src = '../images/AddMarkerDown.png' ;
	GetE( 'instructions' ).innerHTML = FCKLang.GMapsClickToAddMarker ;
	Mode = 'AddMarker' ;
	mapDiv.firstChild.firstChild.style.cursor = "crosshair" ;
}
function AddMarkerAtPoint( point, text, interactive )
{
	var marker = createMarker(point, text) ;
	map.addOverlay( marker ) ;
	markers.push( marker );
	FinishAddMarker();
	if (interactive)
		EditMarker( marker );
}
function FinishAddMarker()
{
	Mode = '';
	GetE( 'btnAddNewMarker' ).src = '../images/AddMarker.png' ;
	GetE( 'instructions' ).innerHTML = '';
	mapDiv.firstChild.firstChild.style.cursor = "default" ;
}
function EditMarker(obj)
{
	if (ActiveTab!='Marker')
	{
		obj.openInfoWindowHtml(obj.text) ;
		return;
	}
	activeMarker = obj ;
	Mode = 'EditMarker' ;
	obj.openInfoWindowHtml(generateEditPopupString(obj.text));
}
function CloseInfoWindow()
{
	Mode = '' ;
	map.closeInfoWindow() ;
	activeMarker = null ;
}
function UpdateCurrentMarker()
{
	activeMarker.text = GetE( 'txtMarkerText' ).value;
	CloseInfoWindow();
}
function DeleteCurrentMarker()
{
	for ( var j = 0 ; j < markers.length ; j++ )
	{
		if ( markers[j] == activeMarker)
		{
				markers.splice(j, 1);
				break ;
		}
	}
	var tmp = activeMarker ;
	CloseInfoWindow() ;
	map.removeOverlay(tmp);
}
function ResizeParent()
{
	var oParentWindow = window.parent;
	if (window.parent.Sizer) {
	oParentWindow.Sizer.RefreshSize() ;
	return;
}
	var oInnerWindow = window ;
	var oInnerDoc = oInnerWindow.document ;
	var iDiff, iFrameHeight, iFrameWidth ;
		if ( document.all )
			iFrameHeight = oInnerDoc.body.offsetHeight ;
		else
			iFrameHeight = oInnerWindow.innerHeight ;
		var iInnerHeight = oInnerDoc.body.scrollHeight ;
		iDiff = iInnerHeight - iFrameHeight ;
		if ( iDiff !== 0 )
		{
			if ( document.all )
				oParentWindow.dialogHeight = ( parseInt( oParentWindow.dialogHeight, 10 ) + iDiff ) + 'px' ;
			else
				oParentWindow.resizeBy( 0, iDiff ) ;
		}
		if ( document.all )
			iFrameWidth = oInnerDoc.body.offsetWidth ;
		else
			iFrameWidth = oInnerWindow.innerWidth ;
		var iInnerWidth = oInnerDoc.body.scrollWidth ;
		iDiff = iInnerWidth - iFrameWidth ;
		if ( iDiff !== 0 )
		{
			if ( document.all )
				oParentWindow.dialogWidth = ( parseInt( oParentWindow.dialogWidth, 10 ) + iDiff ) + 'px' ;
			else
				oParentWindow.resizeBy( iDiff, 0 ) ;
		}
}
function SetupHelpButton( url )
{
	var doc = window.parent.document ;
	var helpButton = doc.createElement( 'INPUT' ) ;
	helpButton.type = 'button' ;
	helpButton.value = FCKLang.Help ;
	helpButton.className = 'Button' ;
	helpButton.onclick = function () { window.open( url ); return false; };
	var okButton = doc.getElementById( 'btnOk' ) ;
	var cell = okButton.parentNode.previousSibling ;
	if (cell.nodeName != 'TD')
			cell = cell.previousSibling ;
	cell.appendChild( helpButton );
}

if ( typeof( FCKConfig.GoogleMaps_Key ) != 'string')
{
	alert( 'Error.\r\nThe configuration doesn\'t contain the Google Maps key.\r\n' + 
		'Please read the Configuration section.') ;
	window.open(FCKPlugins.Items['googlemaps'].Path + 'docs/' + FCKLang.GMapsHelpFile + '#configure');
}
if ( !FCKConfig.GoogleMaps_Key || FCKConfig.GoogleMaps_Key.length === 0)
{
	for( var name in FCKConfig.ToolbarSets )
		RemoveButtonFromToolbarSet( FCKConfig.ToolbarSets[name], 'googlemaps' ) ;
}
function RemoveButtonFromToolbarSet(ToolbarSet, CommandName)
{
	if (!ToolbarSet)
		return;
	for ( var x = 0 ; x < ToolbarSet.length ; x++ )
	{
		var oToolbarItems = ToolbarSet[x] ;
		if ( !oToolbarItems ) 
			continue ;
		if ( typeof( oToolbarItems ) == 'object' )
		{
			for ( var j = 0 ; j < oToolbarItems.length ; j++ )
			{
				if ( oToolbarItems[j] == CommandName)
				{
						oToolbarItems.splice(j, 1);
						ToolbarSet[x] = oToolbarItems ;
						return;
				}
			}
		}
	}
}
FCKCommands.RegisterCommand( 'googlemaps', new FCKDialogCommand( 'googlemaps', FCKLang.DlgGMapsTitle, FCKPlugins.Items['googlemaps'].Path + 'dialog/googleMaps.html', 450, 428 ) ) ;
var oGoogleMapsItem = new FCKToolbarButton( 'googlemaps', FCKLang.GMapsBtn,  FCKLang.GMapsBtnTooltip) ;
oGoogleMapsItem.IconPath = FCKPlugins.Items['googlemaps'].Path + 'images/mapIcon.gif' ;
FCKToolbarItems.RegisterItem( 'googlemaps', oGoogleMapsItem ) ;
var FCKCommentsProcessor = FCKDocumentProcessor.AppendNew() ;
FCKCommentsProcessor.ProcessDocument = function( oDoc )
{
	if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
		return ;
	if ( !oDoc )
		return ;
	if ( oDoc.evaluate )
		this.findCommentsXPath( oDoc );
	else
	{
		if (oDoc.all)
			this.findCommentsIE( oDoc.body ) ;
		else
			this.findComments( oDoc.body ) ;
	}
}
FCKCommentsProcessor.findCommentsXPath = function(oDoc) {
	var nodesSnapshot = oDoc.evaluate('
	for ( var i=0 ; i < nodesSnapshot.snapshotLength; i++ )
	{
		this.parseComment( nodesSnapshot.snapshotItem(i) ) ;
	}
}
FCKCommentsProcessor.findCommentsIE = function(oNode) {
	var aComments = oNode.getElementsByTagName( '!' );
	for(var i=aComments.length-1; i >=0 ; i--)
	{
		var comment = aComments[i] ;
		if (comment.nodeType == 8 ) 
			this.parseComment( comment ) ;
	}
}
FCKCommentsProcessor.findComments = function( oNode ) 
{
	if (oNode.nodeType == 8 ) 
	{
		this.parseComment( oNode ) ;
	}
	else 
	{
		if (oNode.hasChildNodes()) 
		{
			var children = oNode.childNodes ;
			for (var i = children.length-1; i >=0 ; i--) 
				this.findComments( children[ i ] );
		}
	}
}
FCKCommentsProcessor.parseComment = function( oNode )
{
	var value = oNode.nodeValue ;
	var prefix = ( FCKConfig.ProtectedSource._CodeTag || 'PS\\.\\.' ) ;
	var regex = new RegExp( "\\{" + prefix + "(\\d+)\\}", "g" ) ;
	if ( regex.test( value ) ) 
	{
		var index = RegExp.$1 ;
		var content = FCKTempBin.Elements[ index ] ;
		var oCalls = this.ParserHandlers ;
		if ( oCalls )
		{
			for ( var i = 0 ; i < oCalls.length ; i++ )
				oCalls[ i ]( oNode, content, index ) ;
		}
	}
}
FCKCommentsProcessor.AddParser = function( handlerFunction )
{
	if ( !this.ParserHandlers )
		this.ParserHandlers = [ handlerFunction ] ;
	else
	{
		if ( aTargets.IndexOf( handlerFunction ) == -1 )
			aTargets.push( handlerFunction ) ;
	}
}
var GoogleMaps_CommentsProcessorParser = function( oNode, oContent, index)
{
		var oMap = new FCKGoogleMap();
		if ( oMap.parse( oContent ) )
			oMap.createHtmlElement( oNode, index ) ;
		else
		{
			if ( oMap.detectGoogleScript( oContent ) )
				oNode.parentNode.removeChild( oNode );
		}
}
FCKCommentsProcessor.AddParser( GoogleMaps_CommentsProcessorParser );
FCK.ContextMenu.RegisterListener( {
	AddItems : function( menu, tag, tagName )
	{
		if ( tagName == 'IMG' && tag.parsedMap )
		{
			menu.RemoveAllItems() ;
			menu.AddItem( 'googlemaps', FCKLang.DlgGMapsTitle, oGoogleMapsItem.IconPath ) ;
		}
	}}
);
FCK.RegisterDoubleClickHandler( editMap, 'IMG' ) ;
function editMap( oNode )
{
	if ( !oNode.parsedMap)
		return ;
	FCK.Commands.GetCommand( 'googlemaps' ).Execute() ;
}
var FCKGoogleMap = function() 
{
	var now = new Date() ;
	this.number = '' + now.getFullYear() + now.getMonth() + now.getDate() + now.getHours() + now.getMinutes() + now.getSeconds() ;
	this.width = FCKConfig.GoogleMaps_Width || 400 ;
	this.height = FCKConfig.GoogleMaps_Height || 240 ;
	this.centerLat = FCKConfig.GoogleMaps_CenterLat || 37.4419 ;
	this.centerLon =  FCKConfig.GoogleMaps_CenterLon || -122.1419 ;
	this.zoom = FCKConfig.GoogleMaps_Zoom || 11 ;
	this.markerPoints = [] ;
	this.LinePoints = '' ;
	this.LineLevels = '' ;
}
FCKGoogleMap.prototype.detectGoogleScript = function( script )
{
	return ( /^<script src="http:\/\/maps\.google\.com/.test(script) || /FCK googlemapsEnd v1\./.test(script) ) ;
}
FCKGoogleMap.prototype.createHtmlElement = function( oReplacedNode, index)
{
	var oFakeNode = FCK.EditorDocument.createElement( 'IMG' ) ;
	if ( !oReplacedNode )
	{
    index = FCKTempBin.AddElement( this.BuildScript() ) ;
		var prefix = ( FCKConfig.ProtectedSource._CodeTag || 'PS..' ) ;
		oReplacedNode = FCK.EditorDocument.createComment( '{' + prefix + index + '}' ) ;
		FCK.InsertElement(oReplacedNode);
	}
	oFakeNode.contentEditable = false ;
	oFakeNode.src = FCKConfig.FullBasePath + 'images/spacer.gif' ;
	oFakeNode.setAttribute( '_fckrealelement', FCKTempBin.AddElement( oReplacedNode ), 0 ) ;
	oFakeNode.setAttribute( '_fckBinNode', index, 0 ) ;
	oFakeNode.style.display = 'block' ;
	oFakeNode.style.border = '1px solid black' ;
	oFakeNode.style.background = 'center center url("' + FCKPlugins.Items['googlemaps'].Path + 'images/maps_res_logo.png' + '") no-repeat' ;
	oFakeNode.parsedMap = this ;
	oReplacedNode.parentNode.insertBefore( oFakeNode, oReplacedNode ) ;
	oReplacedNode.parentNode.removeChild( oReplacedNode ) ;
	this.updateHTMLElement( oFakeNode );
	return oFakeNode ;
}
FCKGoogleMap.prototype.updateScript = function( oFakeNode )
{
	this.updateDimensions( oFakeNode ) ;
	var index = oFakeNode.getAttribute( '_fckBinNode');
	FCKTempBin.Elements[ index ] =  this.BuildScript() ;
}
FCKGoogleMap.prototype.updateHTMLElement = function( oFakeNode )
{
	oFakeNode.width = this.width ;
	oFakeNode.height = this.height ;
}
FCKGoogleMap.prototype.updateDimensions = function( oFakeNode )
{
	var iWidth, iHeight ;
	var regexSize = /^\s*(\d+)px\s*$/i ;
	if ( oFakeNode.style.width )
	{
		var aMatchW  = oFakeNode.style.width.match( regexSize ) ;
		if ( aMatchW )
		{
			iWidth = aMatchW[1] ;
			oFakeNode.style.width = '' ;
			oFakeNode.width = iWidth ;
		}
	}
	if ( oFakeNode.style.height )
	{
		var aMatchH  = oFakeNode.style.height.match( regexSize ) ;
		if ( aMatchH )
		{
			iHeight = aMatchH[1] ;
			oFakeNode.style.height = '' ;
			oFakeNode.height = iHeight ;	
		}
	}
	this.width	= iWidth ? iWidth : oFakeNode.width ;
	this.height	= iHeight ? iHeight : oFakeNode.height ;
}
FCKGoogleMap.prototype.parse = function( script )
{
	if ( !(/FCK googlemaps v1\.(\d+)/.test(script)) )
		return false;
	var version = parseInt(RegExp.$1, 10) ;
	var regexpDimensions = /<div id="gmap(\d+)" style="width\:\s*(\d+)px; height\:\s*(\d+)px;">/ ;
	if (regexpDimensions.test( script ) )
	{
		this.number = RegExp.$1 ;
		this.width = RegExp.$2 ;
		this.height = RegExp.$3 ;
	}
	var regexpPosition = /map\.setCenter\(new GLatLng\((-?\d{1,3}\.\d{1,6}),(-?\d{1,3}\.\d{1,6})\), (\d{1,2})\);/ ;
	if (regexpPosition.test( script ) )
	{
		this.centerLat = RegExp.$1 ;
		this.centerLon = RegExp.$2 ;
		this.zoom = RegExp.$3 ;
	}
	if ( version<=5 )
	{
		var markerText, markerLat=0, markerLon=0;
		var regexpText = /var text\s*=\s*("|')(.*)\1;\s*\n/ ;
		if (regexpText.test( script ) )
		{
			markerText = RegExp.$2 ;
		}
		var regexpMarker = /var point\s*=\s*new GLatLng\((-?\d{1,3}\.\d{1,6}),(-?\d{1,3}\.\d{1,6})\)/ ;
		if (regexpMarker.test( script ) )
		{
			markerLat = RegExp.$1 ;
			markerLon = RegExp.$2 ;
		}
		if (markerLat!=0 && markerLon!=0)
			this.markerPoints.push( {lat:markerLat, lon:markerLon, text:markerText} ) ;
	}
	else
	{
		var regexpMarkers = /\{lat\:(-?\d{1,3}\.\d{1,6}),\s*lon\:(-?\d{1,3}\.\d{1,6}),\s*text\:("|')(.*)\3}(?:,|])/ ;
		var point;
		var sampleText = script ;
		var startIndex = 0;
		var totalLength = sampleText.length;
		var result, pos;
		while (startIndex != totalLength) {
			result = regexpMarkers.exec(sampleText);
			if (result && result.length > 0) {
				pos = sampleText.indexOf(result[0]);
				startIndex += pos;
				this.markerPoints.push( {lat:result[1], lon:result[2], text:result[4]} ) ;
				sampleText = sampleText.substr(pos + result[0].length);
				startIndex += result[0].length;
			} else {
				break;
			}
		}
	}
	var regexpLinePoints = /var encodedPoints\s*=\s*("|')(.*)\1;\s*\n/ ;
	if (regexpLinePoints.test( script ) )
	{
		this.LinePoints = RegExp.$2 ;
	}
	var regexpLineLevels = /var encodedLevels\s*=\s*("|')(.*)\1;\s*\n/ ;
	if (regexpLineLevels.test( script ) )
	{
		this.LineLevels = RegExp.$2 ;
	}
	return true;
}
FCKGoogleMap.prototype.BuildScript = function()
{
	var versionMarker = '
	var aScript = [] ;
	aScript.push('\r\n<script type="text/javascript">') ;
	aScript.push( versionMarker ) ;
	aScript.push('document.write(\'<div id="gmap' + this.number + '" style="width:' + this.width + 'px; height:' + this.height + 'px;">.<\\\/div>\');');
	aScript.push('function CreateGMap' + this.number + '() {');
	aScript.push('	if(!GBrowserIsCompatible()) return;');
	aScript.push('	var map = new GMap2(document.getElementById("gmap' + this.number + '"));');
	aScript.push('	map.setCenter(new GLatLng(' + this.centerLat + ',' + this.centerLon + '), ' + this.zoom + ');');
	aScript.push('	map.addControl(new GSmallMapControl());');
	aScript.push('	map.addControl(new GMapTypeControl());');
	var aPoints = [];
	for (var i=0; i<this.markerPoints.length ; i++)
	{
		var point = this.markerPoints[i] ;
		aPoints.push('{lat:' + point.lat + ', lon:' + point.lon + ', text:\'' + point.text + '\'}');	
	}
	aScript.push('	AddMarkers( map, [' + aPoints.join(',\r\n') + '] ) ;') ;
	if ((this.LinePoints !== '') && (this.LineLevels !== '' ))
	{
		aScript.push('var encodedPoints = "' + this.LinePoints + '";');
		aScript.push('var encodedLevels = "' + this.LineLevels + '";');
		aScript.push('');
		aScript.push('var encodedPolyline = new GPolyline.fromEncoded({');
		aScript.push('	color: "#3333cc",');
		aScript.push('	weight: 5,');
		aScript.push('	points: encodedPoints,');
		aScript.push('	levels: encodedLevels,');
		aScript.push('	zoomFactor: 32,');
		aScript.push('	numLevels: 4');
		aScript.push('	});');
		aScript.push('map.addOverlay(encodedPolyline);');
	}
	aScript.push('}');
	aScript.push('</script>');
	return aScript.join('\r\n');
}
FCKGoogleMap.BuildEndingScript = function()
{
	var versionMarker = '
	var aScript = [] ;
	aScript.push('\r\n<script type="text/javascript">') ;
	aScript.push( versionMarker ) ;
	aScript.push('function AddMarkers( map, aPoints )');
	aScript.push('{');
	aScript.push('	for (var i=0; i<aPoints.length ; i++)');
	aScript.push('	{');
	aScript.push('		var point = aPoints[i] ;');
	aScript.push('		map.addOverlay( createMarker(new GLatLng(point.lat, point.lon), point.text) );');
	aScript.push('	}');
	aScript.push('}');
	aScript.push('function createMarker( point, html )');
	aScript.push('{');
	aScript.push('	var marker = new GMarker(point);');
	aScript.push('	GEvent.addListener(marker, "click", function() {');
	aScript.push('		marker.openInfoWindowHtml(html, {maxWidth:200});');
	aScript.push('	});');
	aScript.push('	return marker;');
	aScript.push('}');
	aScript.push('onload = function() {');
	for (var i = 0; i<CreatedMapsNames.length; i++)
	{
		aScript.push('	CreateGMap' + CreatedMapsNames[i]  + '();');
	}
	aScript.push('}');
	aScript.push('onunload = GUnload ;');
	aScript.push('</script>');
	return aScript.join('\r\n');
}
FCKGoogleMap.prototype.GenerateGoogleScript = function()
{
	return '\r\n<script src="http:
}
function createGoogleMap()
{
	return new FCKGoogleMap() ;
}
var CreatedMapsNames = [];
FCKGoogleMap.GetXHTMLAfter = function( node, includeNode, format, Result )
{
	if (CreatedMapsNames.length > 0)
	{
		Result += FCKGoogleMap.BuildEndingScript() ;
	}
	CreatedMapsNames = [];
	return Result ;
}
FCKXHtml.GetXHTML = Inject(FCKXHtml.GetXHTML, null, FCKGoogleMap.GetXHTMLAfter ) ;
FCKGoogleMap.previousProcessor = FCKXHtml.TagProcessors[ 'img' ] ;
FCKXHtml.TagProcessors.img = function( node, htmlNode, xmlNode )
{
	if ( htmlNode.parsedMap )
	{
		var oMap = htmlNode.parsedMap ;
		CreatedMapsNames.push ( oMap.number ) ;
		oMap.updateScript( htmlNode );
		node = FCK.GetRealElement( htmlNode ) ;
		if ( CreatedMapsNames.length == 1 )
		{
			var index = FCKTempBin.AddElement( oMap.GenerateGoogleScript() ) ;
			var prefix = ( FCKConfig.ProtectedSource._CodeTag || 'PS..' ) ;
			oScriptCommentNode = xmlNode.ownerDocument.createComment( '{' + prefix + index + '}' ) ;
			xmlNode.appendChild( oScriptCommentNode ) ;
		}
		return xmlNode.ownerDocument.createComment( node.nodeValue ) ;
	}
	if (typeof FCKGoogleMap.previousProcessor == 'function') 
		node = FCKGoogleMap.previousProcessor ( node, htmlNode ) ;
	else
		node = FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;
	return node ;
};
function Inject( aOrgFunc, aBeforeExec, aAtferExec ) {
  return function() {
    if (typeof(aBeforeExec) == 'function') arguments = aBeforeExec.apply(this, arguments) || arguments;
    var Result, args = [].slice.call(arguments); 
    args.push(aOrgFunc.apply(this, args));
    if (typeof(aAtferExec) == 'function') Result = aAtferExec.apply(this, args);
    return (typeof(Result) != 'undefined')?Result:args.pop();
  } ;
}

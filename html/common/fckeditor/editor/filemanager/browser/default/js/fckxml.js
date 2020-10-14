var FCKXml = function()
{}
FCKXml.prototype.GetHttpRequest = function()
{
	try { return new XMLHttpRequest(); }
	catch(e) {}
	try { return new ActiveXObject( 'Msxml2.XMLHTTP' ) ; }
	catch(e) {}
	try { return new ActiveXObject( 'Microsoft.XMLHTTP' ) ; }
	catch(e) {}
	return null ;
}
FCKXml.prototype.LoadUrl = function( urlToCall, asyncFunctionPointer )
{
	var oFCKXml = this ;
	var bAsync = ( typeof(asyncFunctionPointer) == 'function' ) ;
	var oXmlHttp = this.GetHttpRequest() ;
	oXmlHttp.open( "GET", urlToCall, bAsync ) ;
	if ( bAsync )
	{
		oXmlHttp.onreadystatechange = function()
		{
			if ( oXmlHttp.readyState == 4 )
			{
				var oXml ;
				try
				{
					var test = oXmlHttp.responseXML.firstChild ;
					oXml = oXmlHttp.responseXML ;
				}
				catch ( e )
				{
					try
					{
						oXml = (new DOMParser()).parseFromString( oXmlHttp.responseText, 'text/xml' ) ;
					}
					catch ( e ) {}
				}
				if ( !oXml || !oXml.firstChild || oXml.firstChild.nodeName == 'parsererror' )
				{
					alert( 'The server didn\'t send back a proper XML response. Please contact your system administrator.\n\n' +
							'XML request error: ' + oXmlHttp.statusText + ' (' + oXmlHttp.status + ')\n\n' +
							'Requested URL:\n' + urlToCall + '\n\n' +
							'Response text:\n' + oXmlHttp.responseText ) ;
					return ;
				}
				oFCKXml.DOMDocument = oXml ;
				asyncFunctionPointer( oFCKXml ) ;
			}
		}
	}
	oXmlHttp.send( null ) ;
	if ( ! bAsync )
	{
		if ( oXmlHttp.status == 200 || oXmlHttp.status == 304 )
			this.DOMDocument = oXmlHttp.responseXML ;
		else
		{
			alert( 'XML request error: ' + oXmlHttp.statusText + ' (' + oXmlHttp.status + ')' ) ;
		}
	}
}
FCKXml.prototype.SelectNodes = function( xpath )
{
	if ( navigator.userAgent.indexOf('MSIE') >= 0 )		
		return this.DOMDocument.selectNodes( xpath ) ;
	else					
	{
		var aNodeArray = new Array();
		var xPathResult = this.DOMDocument.evaluate( xpath, this.DOMDocument,
				this.DOMDocument.createNSResolver(this.DOMDocument.documentElement), XPathResult.ORDERED_NODE_ITERATOR_TYPE, null) ;
		if ( xPathResult )
		{
			var oNode = xPathResult.iterateNext() ;
 			while( oNode )
 			{
 				aNodeArray[aNodeArray.length] = oNode ;
 				oNode = xPathResult.iterateNext();
 			}
		}
		return aNodeArray ;
	}
}
FCKXml.prototype.SelectSingleNode = function( xpath )
{
	if ( navigator.userAgent.indexOf('MSIE') >= 0 )		
		return this.DOMDocument.selectSingleNode( xpath ) ;
	else					
	{
		var xPathResult = this.DOMDocument.evaluate( xpath, this.DOMDocument,
				this.DOMDocument.createNSResolver(this.DOMDocument.documentElement), 9, null);
		if ( xPathResult && xPathResult.singleNodeValue )
			return xPathResult.singleNodeValue ;
		else
			return null ;
	}
}
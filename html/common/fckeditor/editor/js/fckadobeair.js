if ( FCKBrowserInfo.IsAIR )
{
	var FCKAdobeAIR = (function()
	{
		var getDocumentHead = function( doc )
		{
			var head ;
			var heads = doc.getElementsByTagName( 'head' ) ;
			if( heads && heads[0] )
				head = heads[0] ;
			else
			{
				head = doc.createElement( 'head' ) ;
				doc.documentElement.insertBefore( head, doc.documentElement.firstChild ) ;
			}
			return head ;
		} ;
		return {
			FCKeditorAPI_Evaluate : function( parentWindow, script )
			{
				eval( script ) ;
				parentWindow.FCKeditorAPI = window.FCKeditorAPI ;
			},
			EditingArea_Start : function( doc, html )
			{
				var headInnerHtml = html.match( /<head>([\s\S]*)<\/head>/i )[1] ;
				if ( headInnerHtml && headInnerHtml.length > 0 )
				{
					var div = doc.createElement( 'div' ) ;
					div.innerHTML = headInnerHtml ;
					FCKDomTools.MoveChildren( div, getDocumentHead( doc ) ) ;
				}
				doc.body.innerHTML = html.match( /<body>([\s\S]*)<\/body>/i )[1] ;
				doc.addEventListener('click', function( ev )
					{
						ev.preventDefault() ;
						ev.stopPropagation() ;
					}, true ) ;
			},
			Panel_Contructor : function( doc, baseLocation )
			{
				var head = getDocumentHead( doc ) ;
				head.appendChild( doc.createElement('base') ).href = baseLocation ;
				doc.body.style.margin	= '0px' ;
				doc.body.style.padding	= '0px' ;
			},
			ToolbarSet_GetOutElement : function( win, outMatch )
			{
				var toolbarTarget = win.parent ;
				var targetWindowParts = outMatch[1].split( '.' ) ;
				while ( targetWindowParts.length > 0 )
				{
					var part = targetWindowParts.shift() ;
					if ( part.length > 0 )
						toolbarTarget = toolbarTarget[ part ] ;
				}
				toolbarTarget = toolbarTarget.document.getElementById( outMatch[2] ) ;
			},
			ToolbarSet_InitOutFrame : function( doc )
			{
				var head = getDocumentHead( doc ) ;
				head.appendChild( doc.createElement('base') ).href = window.document.location ;
				var targetWindow = doc.defaultView;
				targetWindow.adjust = function()
				{
					targetWindow.frameElement.height = doc.body.scrollHeight;
				} ;
				targetWindow.onresize = targetWindow.adjust ;
				targetWindow.setTimeout( targetWindow.adjust, 0 ) ;
				doc.body.style.overflow = 'hidden';
				doc.body.innerHTML = document.getElementById( 'xToolbarSpace' ).innerHTML ;
			}
		} ;
	})();
	( function()
	{
		var _Original_FCKPanel_Window_OnFocus	= FCKPanel_Window_OnFocus ;
		var _Original_FCKPanel_Window_OnBlur	= FCKPanel_Window_OnBlur ;
		var _Original_FCK_StartEditor			= FCK.StartEditor ;
		FCKPanel_Window_OnFocus = function( e, panel )
		{
			_Original_FCKPanel_Window_OnFocus.call( this, e, panel ) ;
			if ( panel._focusTimer )
				clearTimeout( panel._focusTimer ) ;
		}
		FCKPanel_Window_OnBlur = function( e, panel )
		{
			panel._focusTimer = FCKTools.SetTimeout( _Original_FCKPanel_Window_OnBlur, 100, this, [ e, panel ] ) ;
		}
		FCK.StartEditor = function()
		{
			window.FCK_InternalCSS			= FCKConfig.BasePath + 'css/fck_internal.css' ;
			window.FCK_ShowTableBordersCSS	= FCKConfig.BasePath + 'css/fck_showtableborders_gecko.css' ;
			_Original_FCK_StartEditor.apply( this, arguments ) ;
		}
	})();
}

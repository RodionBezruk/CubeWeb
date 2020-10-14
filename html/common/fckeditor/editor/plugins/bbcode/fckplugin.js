FCK.DataProcessor =
{
	ConvertToHtml : function( data )
	{
        data = data.replace( /</g, '&lt;' ) ;
        data = data.replace( />/g, '&gt;' ) ;
        data = data.replace( /(?:\r\n|\n|\r)/g, '<br>' ) ;
        data = data.replace( /\[url\](.+?)\[\/url]/gi, '<a href="$1">$1</a>' ) ;
        data = data.replace( /\[url\=([^\]]+)](.+?)\[\/url]/gi, '<a href="$1">$2</a>' ) ;
        data = data.replace( /\[b\](.+?)\[\/b]/gi, '<b>$1</b>' ) ;
        data = data.replace( /\[i\](.+?)\[\/i]/gi, '<i>$1</i>' ) ;
        data = data.replace( /\[u\](.+?)\[\/u]/gi, '<u>$1</u>' ) ;
		return '<html><head><title></title></head><body>' + data + '</body></html>' ;
	},
	ConvertToDataFormat : function( rootNode, excludeRoot, ignoreIfEmptyParagraph, format )
	{
		var data = rootNode.innerHTML ;
		data = data.replace( /<br(?=[ \/>]).*?>/gi, '\r\n') ;
		data = data.replace( /<a .*?href=(["'])(.+?)\1.*?>(.+?)<\/a>/gi, '[url=$2]$3[/url]') ;
		data = data.replace( /<(?:b|strong)>/gi, '[b]') ;
		data = data.replace( /<\/(?:b|strong)>/gi, '[/b]') ;
		data = data.replace( /<(?:i|em)>/gi, '[i]') ;
		data = data.replace( /<\/(?:i|em)>/gi, '[/i]') ;
		data = data.replace( /<u>/gi, '[u]') ;
		data = data.replace( /<\/u>/gi, '[/u]') ;
		data = data.replace( /<[^>]+>/g, '') ;
		return data ;
	},
	FixHtml : function( html )
	{
		return html ;
	}
} ;
FCKConfig.EnterMode = 'br' ;
FCKConfig.ForcePasteAsPlainText	= true ;
FCKToolbarItems.RegisterItem( 'Source', new FCKToolbarButton( 'Source', 'BBCode', null, FCK_TOOLBARITEM_ICONTEXT, true, true, 1 ) ) ;
FCKConfig.ToolbarSets["Default"] = [
	['Source'],
	['Bold','Italic','Underline','-','Link'],
	['About']
] ;

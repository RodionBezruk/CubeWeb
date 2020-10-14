var dialog		= window.parent ;
var oEditor		= dialog.InnerDialogLoaded() ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;
var FCKDebug	= oEditor.FCKDebug ;
var FCKTools	= oEditor.FCKTools ;
var bImageButton = ( document.location.search.length > 0 && document.location.search.substr(1) == 'ImageButton' ) ;
dialog.AddTab( 'Info', FCKLang.DlgImgInfoTab ) ;
if ( !bImageButton && !FCKConfig.ImageDlgHideLink )
	dialog.AddTab( 'Link', FCKLang.DlgImgLinkTab ) ;
if ( FCKConfig.ImageUpload )
	dialog.AddTab( 'Upload', FCKLang.DlgLnkUpload ) ;
if ( !FCKConfig.ImageDlgHideAdvanced )
	dialog.AddTab( 'Advanced', FCKLang.DlgAdvancedTag ) ;
function OnDialogTabChange( tabCode )
{
	ShowE('divInfo'		, ( tabCode == 'Info' ) ) ;
	ShowE('divLink'		, ( tabCode == 'Link' ) ) ;
	ShowE('divUpload'	, ( tabCode == 'Upload' ) ) ;
	ShowE('divAdvanced'	, ( tabCode == 'Advanced' ) ) ;
}
var oImage = dialog.Selection.GetSelectedElement() ;
if ( oImage && oImage.tagName != 'IMG' && !( oImage.tagName == 'INPUT' && oImage.type == 'image' ) )
	oImage = null ;
var oLink = dialog.Selection.GetSelection().MoveToAncestorNode( 'A' ) ;
var oImageOriginal ;
function UpdateOriginal( resetSize )
{
	if ( !eImgPreview )
		return ;
	if ( GetE('txtUrl').value.length == 0 )
	{
		oImageOriginal = null ;
		return ;
	}
	oImageOriginal = document.createElement( 'IMG' ) ;	
	if ( resetSize )
	{
		oImageOriginal.onload = function()
		{
			this.onload = null ;
			ResetSizes() ;
		}
	}
	oImageOriginal.src = eImgPreview.src ;
}
var bPreviewInitialized ;
window.onload = function()
{
	oEditor.FCKLanguageManager.TranslatePage(document) ;
	GetE('btnLockSizes').title = FCKLang.DlgImgLockRatio ;
	GetE('btnResetSize').title = FCKLang.DlgBtnResetSize ;
	LoadSelection() ;
	GetE('tdBrowse').style.display				= FCKConfig.ImageBrowser	? '' : 'none' ;
	GetE('divLnkBrowseServer').style.display	= FCKConfig.LinkBrowser		? '' : 'none' ;
	UpdateOriginal() ;
	if ( FCKConfig.ImageUpload )
		GetE('frmUpload').action = FCKConfig.ImageUploadURL ;
	dialog.SetAutoSize( true ) ;
	dialog.SetOkButton( true ) ;
	SelectField( 'txtUrl' ) ;
}
function LoadSelection()
{
	if ( ! oImage ) return ;
	var sUrl = oImage.getAttribute( '_fcksavedurl' ) ;
	if ( sUrl == null )
		sUrl = GetAttribute( oImage, 'src', '' ) ;
	GetE('txtUrl').value    = sUrl ;
	GetE('txtAlt').value    = GetAttribute( oImage, 'alt', '' ) ;
	GetE('txtVSpace').value	= GetAttribute( oImage, 'vspace', '' ) ;
	GetE('txtHSpace').value	= GetAttribute( oImage, 'hspace', '' ) ;
	GetE('txtBorder').value	= GetAttribute( oImage, 'border', '' ) ;
	GetE('cmbAlign').value	= GetAttribute( oImage, 'align', '' ) ;
	var iWidth, iHeight ;
	var regexSize = /^\s*(\d+)px\s*$/i ;
	if ( oImage.style.width )
	{
		var aMatchW  = oImage.style.width.match( regexSize ) ;
		if ( aMatchW )
		{
			iWidth = aMatchW[1] ;
			oImage.style.width = '' ;
			SetAttribute( oImage, 'width' , iWidth ) ;
		}
	}
	if ( oImage.style.height )
	{
		var aMatchH  = oImage.style.height.match( regexSize ) ;
		if ( aMatchH )
		{
			iHeight = aMatchH[1] ;
			oImage.style.height = '' ;
			SetAttribute( oImage, 'height', iHeight ) ;
		}
	}
	GetE('txtWidth').value	= iWidth ? iWidth : GetAttribute( oImage, "width", '' ) ;
	GetE('txtHeight').value	= iHeight ? iHeight : GetAttribute( oImage, "height", '' ) ;
	GetE('txtAttId').value			= oImage.id ;
	GetE('cmbAttLangDir').value		= oImage.dir ;
	GetE('txtAttLangCode').value	= oImage.lang ;
	GetE('txtAttTitle').value		= oImage.title ;
	GetE('txtLongDesc').value		= oImage.longDesc ;
	if ( oEditor.FCKBrowserInfo.IsIE )
	{
		GetE('txtAttClasses').value = oImage.className || '' ;
		GetE('txtAttStyle').value = oImage.style.cssText ;
	}
	else
	{
		GetE('txtAttClasses').value = oImage.getAttribute('class',2) || '' ;
		GetE('txtAttStyle').value = oImage.getAttribute('style',2) ;
	}
	if ( oLink )
	{
		var sLinkUrl = oLink.getAttribute( '_fcksavedurl' ) ;
		if ( sLinkUrl == null )
			sLinkUrl = oLink.getAttribute('href',2) ;
		GetE('txtLnkUrl').value		= sLinkUrl ;
		GetE('cmbLnkTarget').value	= oLink.target ;
	}
	UpdatePreview() ;
}
function Ok()
{
	if ( GetE('txtUrl').value.length == 0 )
	{
		dialog.SetSelectedTab( 'Info' ) ;
		GetE('txtUrl').focus() ;
		alert( FCKLang.DlgImgAlertUrl ) ;
		return false ;
	}
	var bHasImage = ( oImage != null ) ;
	if ( bHasImage && bImageButton && oImage.tagName == 'IMG' )
	{
		if ( confirm( 'Do you want to transform the selected image on a image button?' ) )
			oImage = null ;
	}
	else if ( bHasImage && !bImageButton && oImage.tagName == 'INPUT' )
	{
		if ( confirm( 'Do you want to transform the selected image button on a simple image?' ) )
			oImage = null ;
	}
	oEditor.FCKUndo.SaveUndoStep() ;
	if ( !bHasImage )
	{
		if ( bImageButton )
		{
			oImage = FCK.EditorDocument.createElement( 'input' ) ;
			oImage.type = 'image' ;
			oImage = FCK.InsertElement( oImage ) ;
		}
		else
			oImage = FCK.InsertElement( 'img' ) ;
	}
	UpdateImage( oImage ) ;
	var sLnkUrl = GetE('txtLnkUrl').value.Trim() ;
	if ( sLnkUrl.length == 0 )
	{
		if ( oLink )
			FCK.ExecuteNamedCommand( 'Unlink' ) ;
	}
	else
	{
		if ( oLink )	
			oLink.href = sLnkUrl ;
		else			
		{
			if ( !bHasImage )
				oEditor.FCKSelection.SelectNode( oImage ) ;
			oLink = oEditor.FCK.CreateLink( sLnkUrl )[0] ;
			if ( !bHasImage )
			{
				oEditor.FCKSelection.SelectNode( oLink ) ;
				oEditor.FCKSelection.Collapse( false ) ;
			}
		}
		SetAttribute( oLink, '_fcksavedurl', sLnkUrl ) ;
		SetAttribute( oLink, 'target', GetE('cmbLnkTarget').value ) ;
	}
	return true ;
}
function UpdateImage( e, skipId )
{
	e.src = GetE('txtUrl').value ;
	SetAttribute( e, "_fcksavedurl", GetE('txtUrl').value ) ;
	SetAttribute( e, "alt"   , GetE('txtAlt').value ) ;
	SetAttribute( e, "width" , GetE('txtWidth').value ) ;
	SetAttribute( e, "height", GetE('txtHeight').value ) ;
	SetAttribute( e, "vspace", GetE('txtVSpace').value ) ;
	SetAttribute( e, "hspace", GetE('txtHSpace').value ) ;
	SetAttribute( e, "border", GetE('txtBorder').value ) ;
	SetAttribute( e, "align" , GetE('cmbAlign').value ) ;
	if ( ! skipId )
		SetAttribute( e, 'id', GetE('txtAttId').value ) ;
	SetAttribute( e, 'dir'		, GetE('cmbAttLangDir').value ) ;
	SetAttribute( e, 'lang'		, GetE('txtAttLangCode').value ) ;
	SetAttribute( e, 'title'	, GetE('txtAttTitle').value ) ;
	SetAttribute( e, 'longDesc'	, GetE('txtLongDesc').value ) ;
	if ( oEditor.FCKBrowserInfo.IsIE )
	{
		e.className = GetE('txtAttClasses').value ;
		e.style.cssText = GetE('txtAttStyle').value ;
	}
	else
	{
		SetAttribute( e, 'class'	, GetE('txtAttClasses').value ) ;
		SetAttribute( e, 'style', GetE('txtAttStyle').value ) ;
	}
}
var eImgPreview ;
var eImgPreviewLink ;
function SetPreviewElements( imageElement, linkElement )
{
	eImgPreview = imageElement ;
	eImgPreviewLink = linkElement ;
	UpdatePreview() ;
	UpdateOriginal() ;
	bPreviewInitialized = true ;
}
function UpdatePreview()
{
	if ( !eImgPreview || !eImgPreviewLink )
		return ;
	if ( GetE('txtUrl').value.length == 0 )
		eImgPreviewLink.style.display = 'none' ;
	else
	{
		UpdateImage( eImgPreview, true ) ;
		if ( GetE('txtLnkUrl').value.Trim().length > 0 )
			eImgPreviewLink.href = 'javascript:void(null);' ;
		else
			SetAttribute( eImgPreviewLink, 'href', '' ) ;
		eImgPreviewLink.style.display = '' ;
	}
}
var bLockRatio = true ;
function SwitchLock( lockButton )
{
	bLockRatio = !bLockRatio ;
	lockButton.className = bLockRatio ? 'BtnLocked' : 'BtnUnlocked' ;
	lockButton.title = bLockRatio ? 'Lock sizes' : 'Unlock sizes' ;
	if ( bLockRatio )
	{
		if ( GetE('txtWidth').value.length > 0 )
			OnSizeChanged( 'Width', GetE('txtWidth').value ) ;
		else
			OnSizeChanged( 'Height', GetE('txtHeight').value ) ;
	}
}
function OnSizeChanged( dimension, value )
{
	if ( oImageOriginal && bLockRatio )
	{
		var e = dimension == 'Width' ? GetE('txtHeight') : GetE('txtWidth') ;
		if ( value.length == 0 || isNaN( value ) )
		{
			e.value = '' ;
			return ;
		}
		if ( dimension == 'Width' )
			value = value == 0 ? 0 : Math.round( oImageOriginal.height * ( value  / oImageOriginal.width ) ) ;
		else
			value = value == 0 ? 0 : Math.round( oImageOriginal.width  * ( value / oImageOriginal.height ) ) ;
		if ( !isNaN( value ) )
			e.value = value ;
	}
	UpdatePreview() ;
}
function ResetSizes()
{
	if ( ! oImageOriginal ) return ;
	if ( oEditor.FCKBrowserInfo.IsGecko && !oImageOriginal.complete )
	{
		setTimeout( ResetSizes, 50 ) ;
		return ;
	}
	GetE('txtWidth').value  = oImageOriginal.width ;
	GetE('txtHeight').value = oImageOriginal.height ;
	UpdatePreview() ;
}
function BrowseServer()
{
	OpenServerBrowser(
		'Image',
		FCKConfig.ImageBrowserURL,
		FCKConfig.ImageBrowserWindowWidth,
		FCKConfig.ImageBrowserWindowHeight ) ;
}
function LnkBrowseServer()
{
	OpenServerBrowser(
		'Link',
		FCKConfig.LinkBrowserURL,
		FCKConfig.LinkBrowserWindowWidth,
		FCKConfig.LinkBrowserWindowHeight ) ;
}
function OpenServerBrowser( type, url, width, height )
{
	sActualBrowser = type ;
	OpenFileBrowser( url, width, height ) ;
}
var sActualBrowser ;
function SetUrl( url, width, height, alt )
{
	if ( sActualBrowser == 'Link' )
	{
		GetE('txtLnkUrl').value = url ;
		UpdatePreview() ;
	}
	else
	{
		GetE('txtUrl').value = url ;
		GetE('txtWidth').value = width ? width : '' ;
		GetE('txtHeight').value = height ? height : '' ;
		if ( alt )
			GetE('txtAlt').value = alt;
		UpdatePreview() ;
		UpdateOriginal( true ) ;
	}
	dialog.SetSelectedTab( 'Info' ) ;
}
function OnUploadCompleted( errorNumber, fileUrl, fileName, customMsg )
{
	window.parent.Throbber.Hide() ;
	GetE( 'divUpload' ).style.display  = '' ;
	switch ( errorNumber )
	{
		case 0 :	
			alert( 'Your file has been successfully uploaded' ) ;
			break ;
		case 1 :	
			alert( customMsg ) ;
			return ;
		case 101 :	
			alert( customMsg ) ;
			break ;
		case 201 :
			alert( 'A file with the same name is already available. The uploaded file has been renamed to "' + fileName + '"' ) ;
			break ;
		case 202 :
			alert( 'Invalid file type' ) ;
			return ;
		case 203 :
			alert( "Security error. You probably don't have enough permissions to upload. Please check your server." ) ;
			return ;
		case 500 :
			alert( 'The connector is disabled' ) ;
			break ;
		default :
			alert( 'Error on file upload. Error number: ' + errorNumber ) ;
			return ;
	}
	sActualBrowser = '' ;
	SetUrl( fileUrl ) ;
	GetE('frmUpload').reset() ;
}
var oUploadAllowedExtRegex	= new RegExp( FCKConfig.ImageUploadAllowedExtensions, 'i' ) ;
var oUploadDeniedExtRegex	= new RegExp( FCKConfig.ImageUploadDeniedExtensions, 'i' ) ;
function CheckUpload()
{
	var sFile = GetE('txtUploadFile').value ;
	if ( sFile.length == 0 )
	{
		alert( 'Please select a file to upload' ) ;
		return false ;
	}
	if ( ( FCKConfig.ImageUploadAllowedExtensions.length > 0 && !oUploadAllowedExtRegex.test( sFile ) ) ||
		( FCKConfig.ImageUploadDeniedExtensions.length > 0 && oUploadDeniedExtRegex.test( sFile ) ) )
	{
		OnUploadCompleted( 202 ) ;
		return false ;
	}
	window.parent.Throbber.Show( 100 ) ;
	GetE( 'divUpload' ).style.display  = 'none' ;
	return true ;
}

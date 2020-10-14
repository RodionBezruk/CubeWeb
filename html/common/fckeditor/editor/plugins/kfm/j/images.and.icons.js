function kfm_changeCaption(id){
	kfm_prompt(kfm.lang.ChangeCaption,File_getInstance(id).caption,function(newCaption){
		x_kfm_changeCaption(id,newCaption,function(res){
			File_getInstance(id).caption=newCaption;
		});
	});
}
function kfm_changeCaption_set(){
	var id=$('kfm_newCaptionDetails').kfm_caption_for,newCaption=$('kfm_new_caption').value;
	if(!newCaption||newCaption==File_getInstance(id).caption)return;
	kfm_modal_close();
	if(kfm.confirm(kfm.lang.NewCaptionIsThisCorrect(newCaption))){
		kfm_log(kfm.lang.Log_ChangeCaption(id,newCaption));
		x_kfm_changeCaption(id,newCaption,kfm_refreshFiles);
	}
}
function kfm_resizeImage(id){
	var data=File_getInstance(id);
	var txt=kfm.lang.CurrentSize(data.width,data.height);
	kfm_prompt(txt+kfm.lang.NewWidth,data.width,function(x){
		x=parseInt(x);
		if(!x)return;
		txt+=kfm.lang.NewWidthConfirmTxt(x);
		kfm_prompt(txt+kfm.lang.NewHeight,Math.ceil(data.height*(x/data.width)),function(y){
			y=parseInt(y);
			if(!y)return;
			if(kfm.confirm(txt+kfm.lang.NewHeightConfirmTxt(y)))x_kfm_resizeImage(id,x,y,kfm_refreshFiles);
		});
	});
}
function kfm_returnThumbnail(id,size){
	if(!size)size='64x64';
	valid=1;
	kfm_prompt(kfm.lang.WhatMaximumSize,size,function(size){
		if(!size)return;
		if(!/^[0-9]+x[0-9]+$/.test(size)){
			alert('The size must be in the format XXxYY, where X is the width and Y is the height');
			valid=0;
		}
		if(!valid)return kfm_returnThumbnail(id,size);
		var x=size.replace(/x.*/,''),y=size.replace(/.*x/,'');
		x_kfm_getFileUrl(id,x,y,function(url){
			if(kfm_file_handler=='return'||kfm_file_handler=='fckeditor'){
				window.opener.SetUrl(url,0,0,File_getInstance(id).caption);
				window.close();
			}
			else if(kfm_file_handler=='download'){
				if(/get.php/.test(url))url+='&forcedownload=1';
				document.location=url;
			}
		});
	});
}

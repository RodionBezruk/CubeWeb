var HookCategories=["main", "view", "edit", "returning"];
var kfm_imageExtensions=['jpg','png','gif'];
var HooksSingleReadonly={};
var HooksSingleWritable={};
var HooksMultiple={};
function kfm_addHook(objoriginal, properties){
	var obj=objoriginal;
	if(properties){
		if(typeof(properties.doFunction)!="undefined"){
			if(typeof(properties.doFunction)=="function")obj.doFunction=properties.doFunction;
			if(typeof(properties.doFunction)=="string")obj.doFunction=eval('obj.'+properties.doFunction+';');
		}
		if(typeof(properties.mode)!="undefined")obj.mode=properties.mode;
		if(typeof(properties.title)!="undefined")obj.title=properties.title;
		if(typeof(properties.name)!="undefined")obj.name=properties.name;
		if(typeof(properties.category)!="undefined")obj.category=properties.category;
		if(typeof(properties.defaultOpener)!="undefined")obj.defaultOpener=properties.defaultOpener;
		if(typeof(properties.writable)!="undefined")obj.writable=properties.writable;
		if(typeof(properties.extensions)!="undefined")obj.extensions=properties.extensions;
	}
	if(typeof(obj.name)=="undefined"&&typeof(obj.title)!="undefined")obj.name=obj.title; 
	if(typeof(obj.category)=="undefined")obj.category=HookCategories[0]; 
	if(!obj.extensions)obj.extensions="all";
	if(!kfm_vars.permissions.file.ed && obj.category=='edit')return;
	if(!kfm_vars.permissions.image.manip && obj.category=='edit'){
		if(obj.extensions=='all')return;
		for(var i=0; i<obj.extensions.length; i++){
			for(var j=0; j<kfm_imageExtensions.length; j++){
				if(obj.extensions[i]==kfm_imageExtensions[j]){
					obj.extensions.splice(i,1);
					return;
				}
			}
		}
	}
	if(obj.mode==0 || obj.mode==2){
		if(obj.writable==1 || obj.writable==2)kfm_addHookToArray(obj,"HooksSingleWritable");
		if(obj.writable==0 || obj.writable==2)kfm_addHookToArray(obj,"HooksSingleReadonly");
	}
	if(obj.mode==1 || obj.mode==2){
		kfm_addHookToArray(obj,"HooksMultiple");
	}
}
function kfm_addHookToArray(obj, HooksArray){
	if(!obj.extensions)return false;
	if(typeof(obj.extensions)=="string" && obj.extensions.toLowerCase()=="all" || HooksArray=="HooksMultiple"){
		ext="all";
		if(eval("typeof("+HooksArray+'.'+ext+')=="undefined"'))kfm_addHookExtension(HooksArray, ext);
		if(eval("typeof("+HooksArray+'.'+ext+'.'+obj.category+')=="undefined"'))kfm_addHookCategory(HooksArray, ext, obj.category);
		eval(HooksArray+'.'+ext+'.'+obj.category+'.push(obj);');
	}else{
		for(var i=0;i<obj.extensions.length; i++){
			ext=obj.extensions[i];
			if(eval("typeof("+HooksArray+'.'+ext+')=="undefined"'))kfm_addHookExtension(HooksArray, ext);
			if(eval("typeof("+HooksArray+'.'+ext+'.'+obj.category+')=="undefined"'))kfm_addHookCategory(HooksArray, ext, obj.category);
			eval(HooksArray+'.'+ext+'.'+obj.category+'.push(obj);');
		}
	}
}
function kfm_addHookExtension(HooksArray, ext){
	eval(HooksArray+'.'+ext+'={};');
}
function kfm_addHookCategory(HookArray,ext, newCategory){
	eval(HookArray+'.'+ext+'.'+newCategory+'=[];');
}
function kfm_getLinks(files){
	var HooksArray="";
	var cPlugins=[];
	function addPlugin(plugin, fid){
		var add=true;
		var index=-1;
		for(var i=0;i<cPlugins.length;i++){
			if(cPlugins[i].name==plugin.name){
				add=false;
				index=i;
				break;
			}
		}
		if(add){
			cPlugins.push(plugin);
			index=cPlugins.length-1;
			cPlugins[index].doParameter=[];
		}
		cPlugins[index].doParameter.push(fid);
	}
	if(files.length>1){
		for(var i=0; i<files.length; i++){
			var F=File_getInstance(files[i]);
         var extension=F.name.replace(/.*\./,'').toLowerCase();
			for(var k=0;k<HookCategories.length; k++){
				if(eval('HooksMultiple.all.'+HookCategories[k]))plugins=eval('HooksMultiple.all.'+HookCategories[k]);
				else plugins=[];
				for(var j=0; j<plugins.length; j++){ 
					var plugin=plugins[j];
					if(	F.writable && 
							(plugin.writable==1 || plugin.writable==2) && 
							((typeof(plugin.extensions)=="string" && plugin.extensions=="all") || plugin.extensions.indexOf(extension)!=-1)
						)
						addPlugin(plugin,F.id);
					else if(	!F.writable && 
							(plugin.writable==0 || plugin.writable==2) && 
							((typeof(plugin.extensions)=="string" && plugin.extensions=="all") || plugin.extensions.indexOf(extension)!=-1)
						)
						addPlugin(plugin,F.id);
				}
			}
		}
		return cPlugins;
	}
	var hookObjects=[];
	var F=File_getInstance(files[0]);
	var ext=F.ext;
	if(F.writable)HooksArray="HooksSingleWritable";
	else HooksArray="HooksSingleReadonly";
	for(var j=0;j<HookCategories.length;j++){
		category=HookCategories[j];
		if(typeof(eval(HooksArray+'.all.'+category))!='undefined')hookObjects.extend(eval(HooksArray+'.all.'+category));
		try{
			if(typeof(eval(HooksArray+'.'+ext+'.'+category))!='undefined')hookObjects.extend(eval(HooksArray+'.'+ext+'.'+category));
		}
		catch(e){ 
		}
	}
	hookObjects.forEach(function(item, index){
		item.doParameter=[F.id];
	});
	return hookObjects;
}
function kfm_getDefaultOpener(id){
	var hooks=kfm_getLinks([id]);
	for(var i=0;i<hooks.length;++i){
		if(hooks[i].defaultOpener)return hooks[i];
	}
}

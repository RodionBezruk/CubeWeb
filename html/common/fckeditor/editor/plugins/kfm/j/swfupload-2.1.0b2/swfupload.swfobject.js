var swfobject=function(){var X="undefined",P="object",a="visibility:visible",e="visibility:hidden",B="Shockwave Flash",h="ShockwaveFlash.ShockwaveFlash",V="application/x-shockwave-flash",K="SWFObjectExprInst",G=window,g=document,N=navigator,f=[],H=[],Q=null,L=null,S=false,C=false;var Y=function(){var l=typeof g.getElementById!=X&&typeof g.getElementsByTagName!=X&&typeof g.createElement!=X&&typeof g.appendChild!=X&&typeof g.replaceChild!=X&&typeof g.removeChild!=X&&typeof g.cloneNode!=X,t=[0,0,0],n=null;if(typeof N.plugins!=X&&typeof N.plugins[B]==P){n=N.plugins[B].description;if(n){n=n.replace(/^.*\s+(\S+\s+\S+$)/,"$1");t[0]=parseInt(n.replace(/^(.*)\..*$/,"$1"),10);t[1]=parseInt(n.replace(/^.*\.(.*)\s.*$/,"$1"),10);t[2]=/r/.test(n)?parseInt(n.replace(/^.*r(.*)$/,"$1"),10):0}}else{if(typeof G.ActiveXObject!=X){var o=null,s=false;try{o=new ActiveXObject(h+".7")}catch(k){try{o=new ActiveXObject(h+".6");t=[6,0,21];o.AllowScriptAccess="always"}catch(k){if(t[0]==6){s=true}}if(!s){try{o=new ActiveXObject(h)}catch(k){}}}if(!s&&o){try{n=o.GetVariable("$version");if(n){n=n.split(" ")[1].split(",");t=[parseInt(n[0],10),parseInt(n[1],10),parseInt(n[2],10)]}}catch(k){}}}}var v=N.userAgent.toLowerCase(),j=N.platform.toLowerCase(),r=/webkit/.test(v)?parseFloat(v.replace(/^.*webkit\/(\d+(\.\d+)?).*$/,"$1")):false,i=false,q=j?/win/.test(j):/win/.test(v),m=j?/mac/.test(j):/mac/.test(v);return{w3cdom:l,pv:t,webkit:r,ie:i,win:q,mac:m}}();var d=function(){if(!Y.w3cdom){return }J(I);if(Y.ie&&Y.win){try{g.write("<script id=__ie_ondomload defer=true src=
var SWFUpload;
if (typeof(SWFUpload) === "function") {
	SWFUpload.onload = function () {};
	swfobject.addDomLoadEvent(function () {
		if (typeof(SWFUpload.onload) === "function") {
			SWFUpload.onload.call(window);
		}
	});
	SWFUpload.prototype.initSettings = (function (oldInitSettings) {
		return function () {
			if (typeof(oldInitSettings) === "function") {
				oldInitSettings.call(this);
			}
			this.ensureDefault = function (settingName, defaultValue) {
				this.settings[settingName] = (this.settings[settingName] == undefined) ? defaultValue : this.settings[settingName];
			};
			this.ensureDefault("minimum_flash_version", "9.0.28");
			this.ensureDefault("swfupload_load_failed_handler", null);
			delete this.ensureDefault;
		};
	})(SWFUpload.prototype.initSettings);
	SWFUpload.prototype.loadFlash = function (oldLoadFlash) {
		return function () {
			var hasFlash = swfobject.hasFlashPlayerVersion(this.settings.minimum_flash_version);
			if (hasFlash) {
				this.queueEvent("swfupload_pre_load_handler");
				if (typeof(oldLoadFlash) === "function") {
					oldLoadFlash.call(this);
				}
			} else {
				this.queueEvent("swfupload_load_failed_handler");
			}
		};
	}(SWFUpload.prototype.loadFlash);
	SWFUpload.prototype.displayDebugInfo = function (oldDisplayDebugInfo) {
		return function () {
			if (typeof(oldDisplayDebugInfo) === "function") {
				oldDisplayDebugInfo.call(this);
			}
			this.debug(
				[
					"SWFUpload.SWFObject Plugin settings:", "\n",
					"\t", "minimum_flash_version:                      ", this.settings.minimum_flash_version, "\n",
					"\t", "swfupload_load_failed_handler assigned:     ", (typeof(this.settings.swfupload_load_failed_handler) === "function").toString(), "\n",
				].join("")
			);
		};	
	}(SWFUpload.prototype.displayDebugInfo);
}

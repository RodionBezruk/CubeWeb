jQuery.extend({	
	ImpromptuDefaults: { prefix:'jqi', buttons:{ Ok:true }, loaded:function(){}, submit:function(){return true;}, callback:function(){}, container:'body', opacity:0.6, overlayspeed:'slow', promptspeed:'fast', show:'show'},
	SetImpromptuDefaults: function(o){ 
		jQuery.ImpromptuDefaults = jQuery.extend({},jQuery.ImpromptuDefaults,o);
	},
	prompt: function(m,o){
		o = jQuery.extend({},jQuery.ImpromptuDefaults,o);
		var ie6 = (jQuery.browser.msie && jQuery.browser.version < 7);	
		var b = (ie6)? jQuery(document.body):jQuery(o.container);	
		var fade = '<div class="'+ o.prefix +'fade" id="'+ o.prefix +'fade"></div>';
		if((jQuery.browser.msie && jQuery('object, applet').length > 0) || ie6)
			fade = '<iframe src="" class="'+ o.prefix +'fade" id="'+ o.prefix +'fade"></iframe>';
		var msgbox = '<div class="'+ o.prefix +'" id="'+ o.prefix +'"><div class="'+ o.prefix +'container"><div class="'+ o.prefix +'message">'+ m +'</div><div class="'+ o.prefix +'buttons" id="'+ o.prefix +'buttons">';
		jQuery.each(o.buttons,function(k,v){ msgbox += '<button name="'+ o.prefix +'button'+ k +'" id="'+ o.prefix +'button'+ k +'" value="'+ v +'">'+ k +'</button>'}) ;
		msgbox += '</div></div></div>';
		var jqi = b.prepend(msgbox).children('#'+ o.prefix);
		var jqif = b.prepend(fade).children('#'+ o.prefix +'fade');
		var getfoffset = function(){ return (document.documentElement.scrollTop || document.body.scrollTop) + 'px'; };
		var getjoffset = function(){ return (document.documentElement.scrollTop || document.body.scrollTop) + Math.round(15 * (document.documentElement.offsetHeight || document.body.clientHeight) / 100) + 'px'; };
		var ie6scroll = function(){ jqif.css({ top: getfoffset() }); jqi.css({ top: getjoffset() }); };
		jqif.css({ position: "absolute", height: (ie6)? "100%":b.height(), width: "100%", top: (ie6)? getfoffset():0, left: 0, right: 0, bottom: 0, zIndex: 998, display: "none", opacity: o.opacity });
		jqi.css({ position: (ie6)? "absolute" : "fixed", top: (ie6)? getjoffset():"30%", left: "50%", display: "none", zIndex: 999, marginLeft: ((((jqi.css("paddingLeft").split("px")[0]*1) + jqi.width())/2)*-1) });
		jQuery('#'+ o.prefix +'buttons').children('button').click(function(){ 
			var msg = jqi.children('.'+ o.prefix +'container').children('.'+ o.prefix +'message');
			var clicked = o.buttons[jQuery(this).text()];	
			if(o.submit(clicked,msg)){		
				jqi.remove(); 
				if(ie6)jQuery(window).unbind('scroll',ie6scroll);
				jqif.fadeOut(o.overlayspeed,function(){
					jqif.remove();
					o.callback(clicked,msg);
				});
			}
		});
		if(ie6) jQuery(window).scroll(ie6scroll);
		jqif.fadeIn(o.overlayspeed);
		jqi[o.show](o.promptspeed,o.loaded);
		return jqi;
	}	
});

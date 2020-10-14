CodePress = {
	scrolling : false,
	autocomplete : true,
	initialize : function() {
		if(typeof(editor)=='undefined' && !arguments[0]) return;
		chars = '|32|46|62|'; 
		cc = '\u2009'; 
		editor = document.getElementsByTagName('body')[0];
		document.designMode = 'on';
		document.addEventListener('keyup', this.keyHandler, true);
		window.addEventListener('scroll', function() { if(!CodePress.scrolling) CodePress.syntaxHighlight('scroll') }, false);
		completeChars = this.getCompleteChars();
	},
	keyHandler : function(evt) {
    	keyCode = evt.keyCode;	
		charCode = evt.charCode;
		if((evt.ctrlKey || evt.metaKey) && evt.shiftKey && charCode!=90)  { 
			CodePress.shortcuts(charCode?charCode:keyCode);
		}
		else if(completeChars.indexOf('|'+String.fromCharCode(charCode)+'|')!=-1 && CodePress.autocomplete) { 
			CodePress.complete(String.fromCharCode(charCode));
		}
	    else if(chars.indexOf('|'+charCode+'|')!=-1||keyCode==13) { 
		 	CodePress.syntaxHighlight('generic');
		}
		else if(keyCode==9 || evt.tabKey) {  
			CodePress.snippets(evt);
		}
		else if(keyCode==46||keyCode==8) { 
		 	CodePress.actions.history[CodePress.actions.next()] = editor.innerHTML;
		}
		else if((charCode==122||charCode==121||charCode==90) && evt.ctrlKey) { 
			(charCode==121||evt.shiftKey) ? CodePress.actions.redo() :  CodePress.actions.undo(); 
			evt.preventDefault();
		}
		else if(keyCode==86 && evt.ctrlKey)  { 
		}
	},
	findString : function() {
		var sel = window.getSelection();
		var range = window.document.createRange();
		var span = window.document.getElementsByTagName('span')[0];
		range.selectNode(span);
		sel.removeAllRanges();
		sel.addRange(range);
		span.parentNode.removeChild(span);
	},
	split : function(code,flag) {
		if(flag=='scroll') {
			this.scrolling = true;
			return code;
		}
		else {
			this.scrolling = false;
			mid = code.indexOf('<SPAN>');
			if(mid-2000<0) {ini=0;end=4000;}
			else if(mid+2000>code.length) {ini=code.length-4000;end=code.length;}
			else {ini=mid-2000;end=mid+2000;}
			code = code.substring(ini,end);
			return code;
		}
	},
	syntaxHighlight : function(flag) {
		if(flag!='init') {
			var span = document.createElement('span');
			window.getSelection().getRangeAt(0).insertNode(span);
		}
		o = editor.innerHTML;
		o = o.replace(/<(?!span|\/span|br).*?>/gi,'');
		x = z = this.split(o,flag);
		x = x.replace(/\t/g, '        ');
		if(arguments[1]&&arguments[2]) x = x.replace(arguments[1],arguments[2]);
		for(i=0;i<Language.syntax.length;i++) 
			x = x.replace(Language.syntax[i].input,Language.syntax[i].output);
		editor.innerHTML = this.actions.history[this.actions.next()] = (flag=='scroll') ? x : o.split(z).join(x); 
		if(flag!='init') this.findString();
	},
	getLastWord : function() {
		var rangeAndCaret = CodePress.getRangeAndCaret();
		words = rangeAndCaret[0].substring(rangeAndCaret[1]-40,rangeAndCaret[1]);
		words = words.replace(/[\s\n\r\);\W]/g,'\n').split('\n');
		return words[words.length-1].replace(/[\W]/gi,'').toLowerCase();
	}, 
	snippets : function(evt) {
		var snippets = Language.snippets;	
		var trigger = this.getLastWord();
		for (var i=0; i<snippets.length; i++) {
			if(snippets[i].input == trigger) {
				var content = snippets[i].output.replace(/</g,'&lt;');
				content = content.replace(/>/g,'&gt;');
				if(content.indexOf('$0')<0) content += cc;
				else content = content.replace(/\$0/,cc);
				content = content.replace(/\n/g,'<br>');
				var pattern = new RegExp(trigger+cc,'gi');
				evt.preventDefault(); 
				this.syntaxHighlight('snippets',pattern,content);
			}
		}
	},
	readOnly : function() {
		document.designMode = (arguments[0]) ? 'off' : 'on';
	},
	complete : function(trigger) {
		window.getSelection().getRangeAt(0).deleteContents();
		var complete = Language.complete;
		for (var i=0; i<complete.length; i++) {
			if(complete[i].input == trigger) {
				var pattern = new RegExp('\\'+trigger+cc);
				var content = complete[i].output.replace(/\$0/g,cc);
				parent.setTimeout(function () { CodePress.syntaxHighlight('complete',pattern,content)},0); 
			}
		}
	},
	getCompleteChars : function() {
		var cChars = '';
		for(var i=0;i<Language.complete.length;i++)
			cChars += '|'+Language.complete[i].input;
		return cChars+'|';
	},
	shortcuts : function() {
		var cCode = arguments[0];
		if(cCode==13) cCode = '[enter]';
		else if(cCode==32) cCode = '[space]';
		else cCode = '['+String.fromCharCode(charCode).toLowerCase()+']';
		for(var i=0;i<Language.shortcuts.length;i++)
			if(Language.shortcuts[i].input == cCode)
				this.insertCode(Language.shortcuts[i].output,false);
	},
	getRangeAndCaret : function() {	
		var range = window.getSelection().getRangeAt(0);
		var range2 = range.cloneRange();
		var node = range.endContainer;			
		var caret = range.endOffset;
		range2.selectNode(node);	
		return [range2.toString(),caret];
	},
	insertCode : function(code,replaceCursorBefore) {
		var range = window.getSelection().getRangeAt(0);
		var node = window.document.createTextNode(code);
		var selct = window.getSelection();
		var range2 = range.cloneRange();
		selct.removeAllRanges();
		range.deleteContents();
		range.insertNode(node);
		range2.selectNode(node);		
		range2.collapse(replaceCursorBefore);
		selct.removeAllRanges();
		selct.addRange(range2);
	},
	getCode : function() {
		var code = editor.innerHTML;
		code = code.replace(/<br>/g,'\n');
		code = code.replace(/\u2009/g,'');
		code = code.replace(/<.*?>/g,'');
		code = code.replace(/&lt;/g,'<');
		code = code.replace(/&gt;/g,'>');
		code = code.replace(/&amp;/gi,'&');
		return code;
	},
	setCode : function() {
		var code = arguments[0];
		code = code.replace(/\u2009/gi,'');
		code = code.replace(/&/gi,'&amp;');
       	code = code.replace(/</g,'&lt;');
        code = code.replace(/>/g,'&gt;');
		editor.innerHTML = code;
	},
	actions : {
		pos : -1, 
		history : [], 
		undo : function() {
			if(editor.innerHTML.indexOf(cc)==-1){
				window.getSelection().getRangeAt(0).insertNode(document.createTextNode(cc));
			 	this.history[this.pos] = editor.innerHTML;
			}
			this.pos--;
			if(typeof(this.history[this.pos])=='undefined') this.pos++;
			editor.innerHTML = this.history[this.pos];
			CodePress.findString();
		},
		redo : function() {
			this.pos++;
			if(typeof(this.history[this.pos])=='undefined') this.pos--;
			editor.innerHTML = this.history[this.pos];
			CodePress.findString();
		},
		next : function() { 
			if(this.pos>20) this.history[this.pos-21] = undefined;
			return ++this.pos;
		}
	}
}
Language={};
window.addEventListener('load', function() { CodePress.initialize('new'); }, true);

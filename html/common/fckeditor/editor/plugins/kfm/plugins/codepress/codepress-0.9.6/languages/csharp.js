Language.syntax = [ 
	{ input : /\"(.*?)(\"|<br>|<\/P>)/g, output : '<s>"$1$2</s>' }, 
	{ input : /\'(.?)(\'|<br>|<\/P>)/g, output : '<s>\'$1$2</s>' }, 
	{ input : /\b(abstract|as|base|break|case|catch|checked|continue|default|delegate|do|else|event|explicit|extern|false|finally|fixed|for|foreach|get|goto|if|implicit|in|interface|internal|is|lock|namespace|new|null|object|operator|out|override|params|partial|private|protected|public|readonly|ref|return|set|sealed|sizeof|static|stackalloc|switch|this|throw|true|try|typeof|unchecked|unsafe|using|value|virtual|while)\b/g, output : '<b>$1</b>' }, 
	{ input : /\b(bool|byte|char|class|double|float|int|interface|long|string|struct|void)\b/g, output : '<a>$1</a>' }, 
	{ input : /([^:]|^)\/\/(.*?)(<br|<\/P)/g, output : '$1<i>
	{ input : /\/\*(.*?)\*\
];
Language.snippets = [];
Language.complete = [ 
	{input : '\'',output : '\'$0\'' },
	{input : '"', output : '"$0"' },
	{input : '(', output : '\($0\)' },
	{input : '[', output : '\[$0\]' },
	{input : '{', output : '{\n\t$0\n}' }		
];
Language.shortcuts = [];

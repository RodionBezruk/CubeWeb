<cfsetting enablecfoutputonly="true">
<cfset aspell_dir	  = "C:\Program Files\Aspell\bin">
<cfset lang         = "en_US">
<cfset aspell_opts  = "-a --lang=#lang# --encoding=utf-8 -H --rem-sgml-check=alt">
<cfset tempfile_in  = GetTempFile(GetTempDirectory(), "spell_")>
<cfset tempfile_out = GetTempFile(GetTempDirectory(), "spell_")>
<cfset spellercss   = "../spellerStyle.css">
<cfset word_win_src = "../wordWindow.js">
<cfset form.checktext = form["textinputs[]"]>
<cfparam name="url.checktext"  default="">
<cfparam name="form.checktext" default="#url.checktext#">
<cfset submitted_text = ReplaceList(form.checktext,"%u201C,%u201D","%22,%22")>
<cfset text = "">
<cfset CRLF = Chr(13) & Chr(10)>
<cfloop list="#submitted_text#" index="field" delimiters=",">
	<cfset text = text & "%"  & CRLF
                      & "^A" & CRLF
                      & "!"  & CRLF>
	<cfset field = REReplace(URLDecode(field), "<[^>]+>", " ", "all")>
	<cfloop list="#field#" index="line" delimiters="#CRLF#">
		<cfset text = ListAppend(text, "^" & Trim(JSStringFormat(line)), CRLF)>
	</cfloop>
</cfloop>
<cffile action="write" file="#tempfile_in#" output="#text#" charset="utf-8">
<cfexecute name="cmd.exe" arguments='/c type "#tempfile_in#" | "#aspell_dir#\aspell.exe" #aspell_opts# > "#tempfile_out#"' timeout="100"/>
<cffile action="read" file="#tempfile_out#" variable="food" charset="utf-8">
<cffile action="delete" file="#tempfile_in#">
<cffile action="delete" file="#tempfile_out#">
<cfset texts = StructNew()>
<cfset texts.textinputs = "">
<cfset texts.words      = "">
<cfset texts.abort      = "">
<cfset i = 0>
<cfloop list="#submitted_text#" index="textinput">
  <cfset texts.textinputs = ListAppend(texts.textinputs, 'textinputs[#i#] = decodeURIComponent("#textinput#");', CRLF)>
  <cfset i = i + 1>
</cfloop>
<cfset word_cnt  = 0>
<cfset input_cnt = -1>
<cfloop list="#food#" index="aspell_line" delimiters="#CRLF#">
    <cfset leftChar = Left(aspell_line, 1)>
	<cfif leftChar eq "*">
			<cfset input_cnt   = input_cnt + 1>
			<cfset word_cnt    = 0>
			<cfset texts.words = ListAppend(texts.words, "words[#input_cnt#] = [];", CRLF)>
			<cfset texts.words = ListAppend(texts.words, "suggs[#input_cnt#] = [];", CRLF)>
    <cfelse>
        <cfif leftChar eq "&" or leftChar eq "##">
			<cfset bad_word    = Trim(ListGetAt(aspell_line, 2, " "))>
			<cfset bad_word    = Replace(bad_word, "'", "\'", "ALL")>
			<cfset sug_list    = Trim(ListRest(aspell_line, ":"))>
			<cfset sug_list    = ListQualify(Replace(sug_list, "'", "\'", "ALL"), "'")>
			<cfset texts.words = ListAppend(texts.words, "words[#input_cnt#][#word_cnt#] = '#bad_word#';", CRLF)>
			<cfset texts.words = ListAppend(texts.words, "suggs[#input_cnt#][#word_cnt#] = [#sug_list#];", CRLF)>
			<cfset word_cnt    = word_cnt + 1>
		</cfif>
     </cfif>
</cfloop>
<cfif texts.words eq "">
  <cfset texts.abort = "alert('Spell check complete.\n\nNo misspellings found.'); top.window.close();">
</cfif>
<cfcontent type="text/html; charset=utf-8">
<cfoutput><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="#spellercss#" />
<script language="javascript" src="#word_win_src#"></script>
<script language="javascript">
var suggs      = new Array();
var words      = new Array();
var textinputs = new Array();
var error;
#texts.textinputs##CRLF#
#texts.words#
#texts.abort#
var wordWindowObj = new wordWindow();
wordWindowObj.originalSpellings = words;
wordWindowObj.suggestions = suggs;
wordWindowObj.textInputs = textinputs;
function init_spell() {
	// check if any error occured during server-side processing
	if( error ) {
		alert( error );
	} else {
		// call the init_spell() function in the parent frameset
		if (parent.frames.length) {
			parent.init_spell( wordWindowObj );
		} else {
			alert('This page was loaded outside of a frameset. It might not display properly');
		}
	}
}
</script>
</head>
<body onLoad="init_spell();">
<script type="text/javascript">
wordWindowObj.writeBody();
</script>
</body>
</html></cfoutput>
<cfsetting enablecfoutputonly="false">

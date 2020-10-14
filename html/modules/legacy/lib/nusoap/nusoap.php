<?php
$GLOBALS['_transient']['static']['nusoap_base']->globalDebugLevel = 9;
class nusoap_base {
	var $title = 'NuSOAP';
	var $version = '0.7.2';
	var $revision = '$Revision: 1.1 $';
	var $error_str = '';
    var $debug_str = '';
	var $charencoding = true;
	var $debugLevel;
	var $XMLSchemaVersion = 'http:
    var $soap_defencoding = 'ISO-8859-1';
	var $namespaces = array(
		'SOAP-ENV' => 'http:
		'xsd' => 'http:
		'xsi' => 'http:
		'SOAP-ENC' => 'http:
		);
	var $usedNamespaces = array();
	var $typemap = array(
	'http:
		'string'=>'string','boolean'=>'boolean','float'=>'double','double'=>'double','decimal'=>'double',
		'duration'=>'','dateTime'=>'string','time'=>'string','date'=>'string','gYearMonth'=>'',
		'gYear'=>'','gMonthDay'=>'','gDay'=>'','gMonth'=>'','hexBinary'=>'string','base64Binary'=>'string',
		'anyType'=>'string','anySimpleType'=>'string',
		'normalizedString'=>'string','token'=>'string','language'=>'','NMTOKEN'=>'','NMTOKENS'=>'','Name'=>'','NCName'=>'','ID'=>'',
		'IDREF'=>'','IDREFS'=>'','ENTITY'=>'','ENTITIES'=>'','integer'=>'integer','nonPositiveInteger'=>'integer',
		'negativeInteger'=>'integer','long'=>'integer','int'=>'integer','short'=>'integer','byte'=>'integer','nonNegativeInteger'=>'integer',
		'unsignedLong'=>'','unsignedInt'=>'','unsignedShort'=>'','unsignedByte'=>'','positiveInteger'=>''),
	'http:
		'i4'=>'','int'=>'integer','boolean'=>'boolean','string'=>'string','double'=>'double',
		'float'=>'double','dateTime'=>'string',
		'timeInstant'=>'string','base64Binary'=>'string','base64'=>'string','ur-type'=>'array'),
	'http:
		'i4'=>'','int'=>'integer','boolean'=>'boolean','string'=>'string','double'=>'double',
		'float'=>'double','dateTime'=>'string',
		'timeInstant'=>'string','base64Binary'=>'string','base64'=>'string','ur-type'=>'array'),
	'http:
	'http:
    'http:
	);
	var $xmlEntities = array('quot' => '"','amp' => '&',
		'lt' => '<','gt' => '>','apos' => "'");
	function nusoap_base() {
		$this->debugLevel = $GLOBALS['_transient']['static']['nusoap_base']->globalDebugLevel;
	}
	function getGlobalDebugLevel() {
		return $GLOBALS['_transient']['static']['nusoap_base']->globalDebugLevel;
	}
	function setGlobalDebugLevel($level) {
		$GLOBALS['_transient']['static']['nusoap_base']->globalDebugLevel = $level;
	}
	function getDebugLevel() {
		return $this->debugLevel;
	}
	function setDebugLevel($level) {
		$this->debugLevel = $level;
	}
	function debug($string){
		if ($this->debugLevel > 0) {
			$this->appendDebug($this->getmicrotime().' '.get_class($this).": $string\n");
		}
	}
	function appendDebug($string){
		if ($this->debugLevel > 0) {
			$this->debug_str .= $string;
		}
	}
	function clearDebug() {
		$this->debug_str = '';
	}
	function &getDebug() {
		return $this->debug_str;
	}
	function &getDebugAsXMLComment() {
		while (strpos($this->debug_str, '--')) {
			$this->debug_str = str_replace('--', '- -', $this->debug_str);
		}
    	return "<!--\n" . $this->debug_str . "\n-->";
	}
	function expandEntities($val) {
		if ($this->charencoding) {
	    	$val = str_replace('&', '&amp;', $val);
	    	$val = str_replace("'", '&apos;', $val);
	    	$val = str_replace('"', '&quot;', $val);
	    	$val = str_replace('<', '&lt;', $val);
	    	$val = str_replace('>', '&gt;', $val);
	    }
	    return $val;
	}
	function getError(){
		if($this->error_str != ''){
			return $this->error_str;
		}
		return false;
	}
	function setError($str){
		$this->error_str = $str;
	}
	function isArraySimpleOrStruct($val) {
        $keyList = array_keys($val);
		foreach ($keyList as $keyListValue) {
			if (!is_int($keyListValue)) {
				return 'arrayStruct';
			}
		}
		return 'arraySimple';
	}
	function serialize_val($val,$name=false,$type=false,$name_ns=false,$type_ns=false,$attributes=false,$use='encoded'){
		$this->debug("in serialize_val: name=$name, type=$type, name_ns=$name_ns, type_ns=$type_ns, use=$use");
		$this->appendDebug('value=' . $this->varDump($val));
		$this->appendDebug('attributes=' . $this->varDump($attributes));
    	if(is_object($val) && get_class($val) == 'soapval'){
        	return $val->serialize($use);
        }
		if (is_numeric($name)) {
			$name = '__numeric_' . $name;
		} elseif (! $name) {
			$name = 'noname';
		}
		$xmlns = '';
        if($name_ns){
			$prefix = 'nu'.rand(1000,9999);
			$name = $prefix.':'.$name;
			$xmlns .= " xmlns:$prefix=\"$name_ns\"";
		}
		if($type_ns != '' && $type_ns == $this->namespaces['xsd']){
			$type_prefix = 'xsd';
		} elseif($type_ns){
			$type_prefix = 'ns'.rand(1000,9999);
			$xmlns .= " xmlns:$type_prefix=\"$type_ns\"";
		}
		$atts = '';
		if($attributes){
			foreach($attributes as $k => $v){
				$atts .= " $k=\"".$this->expandEntities($v).'"';
			}
		}
		if (is_null($val)) {
			if ($use == 'literal') {
	        	return "<$name$xmlns $atts/>";
        	} else {
				if (isset($type) && isset($type_prefix)) {
					$type_str = " xsi:type=\"$type_prefix:$type\"";
				} else {
					$type_str = '';
				}
	        	return "<$name$xmlns$type_str $atts xsi:nil=\"true\"/>";
        	}
		}
        if($type != '' && isset($this->typemap[$this->XMLSchemaVersion][$type])){
        	if (is_bool($val)) {
        		if ($type == 'boolean') {
	        		$val = $val ? 'true' : 'false';
	        	} elseif (! $val) {
	        		$val = 0;
	        	}
			} else if (is_string($val)) {
				$val = $this->expandEntities($val);
			}
			if ($use == 'literal') {
	        	return "<$name$xmlns $atts>$val</$name>";
        	} else {
	        	return "<$name$xmlns $atts xsi:type=\"xsd:$type\">$val</$name>";
        	}
        }
		$xml = '';
		switch(true) {
			case (is_bool($val) || $type == 'boolean'):
        		if ($type == 'boolean') {
	        		$val = $val ? 'true' : 'false';
	        	} elseif (! $val) {
	        		$val = 0;
	        	}
				if ($use == 'literal') {
					$xml .= "<$name$xmlns $atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:boolean\"$atts>$val</$name>";
				}
				break;
			case (is_int($val) || is_long($val) || $type == 'int'):
				if ($use == 'literal') {
					$xml .= "<$name$xmlns $atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:int\"$atts>$val</$name>";
				}
				break;
			case (is_float($val)|| is_double($val) || $type == 'float'):
				if ($use == 'literal') {
					$xml .= "<$name$xmlns $atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:float\"$atts>$val</$name>";
				}
				break;
			case (is_string($val) || $type == 'string'):
				$val = $this->expandEntities($val);
				if ($use == 'literal') {
					$xml .= "<$name$xmlns $atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:string\"$atts>$val</$name>";
				}
				break;
			case is_object($val):
				if (! $name) {
					$name = get_class($val);
					$this->debug("In serialize_val, used class name $name as element name");
				} else {
					$this->debug("In serialize_val, do not override name $name for element name for class " . get_class($val));
				}
				foreach(get_object_vars($val) as $k => $v){
					$pXml = isset($pXml) ? $pXml.$this->serialize_val($v,$k,false,false,false,false,$use) : $this->serialize_val($v,$k,false,false,false,false,$use);
				}
				$xml .= '<'.$name.'>'.$pXml.'</'.$name.'>';
				break;
			break;
			case (is_array($val) || $type):
				$valueType = $this->isArraySimpleOrStruct($val);
                if($valueType=='arraySimple' || ereg('^ArrayOf',$type)){
					$i = 0;
					if(is_array($val) && count($val)> 0){
						foreach($val as $v){
	                    	if(is_object($v) && get_class($v) ==  'soapval'){
								$tt_ns = $v->type_ns;
								$tt = $v->type;
							} elseif (is_array($v)) {
								$tt = $this->isArraySimpleOrStruct($v);
							} else {
								$tt = gettype($v);
	                        }
							$array_types[$tt] = 1;
							$xml .= $this->serialize_val($v,'item',false,false,false,false,$use);
							++$i;
						}
						if(count($array_types) > 1){
							$array_typename = 'xsd:anyType';
						} elseif(isset($tt) && isset($this->typemap[$this->XMLSchemaVersion][$tt])) {
							if ($tt == 'integer') {
								$tt = 'int';
							}
							$array_typename = 'xsd:'.$tt;
						} elseif(isset($tt) && $tt == 'arraySimple'){
							$array_typename = 'SOAP-ENC:Array';
						} elseif(isset($tt) && $tt == 'arrayStruct'){
							$array_typename = 'unnamed_struct_use_soapval';
						} else {
							if ($tt_ns != '' && $tt_ns == $this->namespaces['xsd']){
								 $array_typename = 'xsd:' . $tt;
							} elseif ($tt_ns) {
								$tt_prefix = 'ns' . rand(1000, 9999);
								$array_typename = "$tt_prefix:$tt";
								$xmlns .= " xmlns:$tt_prefix=\"$tt_ns\"";
							} else {
								$array_typename = $tt;
							}
						}
						$array_type = $i;
						if ($use == 'literal') {
							$type_str = '';
						} else if (isset($type) && isset($type_prefix)) {
							$type_str = " xsi:type=\"$type_prefix:$type\"";
						} else {
							$type_str = " xsi:type=\"SOAP-ENC:Array\" SOAP-ENC:arrayType=\"".$array_typename."[$array_type]\"";
						}
					} else {
						if ($use == 'literal') {
							$type_str = '';
						} else if (isset($type) && isset($type_prefix)) {
							$type_str = " xsi:type=\"$type_prefix:$type\"";
						} else {
							$type_str = " xsi:type=\"SOAP-ENC:Array\" SOAP-ENC:arrayType=\"xsd:anyType[0]\"";
						}
					}
					$xml = "<$name$xmlns$type_str$atts>".$xml."</$name>";
				} else {
					if(isset($type) && isset($type_prefix)){
						$type_str = " xsi:type=\"$type_prefix:$type\"";
					} else {
						$type_str = '';
					}
					if ($use == 'literal') {
						$xml .= "<$name$xmlns $atts>";
					} else {
						$xml .= "<$name$xmlns$type_str$atts>";
					}
					foreach($val as $k => $v){
						if ($type == 'Map' && $type_ns == 'http:
							$xml .= '<item>';
							$xml .= $this->serialize_val($k,'key',false,false,false,false,$use);
							$xml .= $this->serialize_val($v,'value',false,false,false,false,$use);
							$xml .= '</item>';
						} else {
							$xml .= $this->serialize_val($v,$k,false,false,false,false,$use);
						}
					}
					$xml .= "</$name>";
				}
				break;
			default:
				$xml .= 'not detected, got '.gettype($val).' for '.$val;
				break;
		}
		return $xml;
	}
    function serializeEnvelope($body,$headers=false,$namespaces=array(),$style='rpc',$use='encoded',$encodingStyle='http:
	$this->debug("In serializeEnvelope length=" . strlen($body) . " body (max 1000 characters)=" . substr($body, 0, 1000) . " style=$style use=$use encodingStyle=$encodingStyle");
	$this->debug("headers:");
	$this->appendDebug($this->varDump($headers));
	$this->debug("namespaces:");
	$this->appendDebug($this->varDump($namespaces));
    $ns_string = '';
	foreach(array_merge($this->namespaces,$namespaces) as $k => $v){
		$ns_string .= " xmlns:$k=\"$v\"";
	}
	if($encodingStyle) {
		$ns_string = " SOAP-ENV:encodingStyle=\"$encodingStyle\"$ns_string";
	}
	if($headers){
		if (is_array($headers)) {
			$xml = '';
			foreach ($headers as $header) {
				$xml .= $this->serialize_val($header, false, false, false, false, false, $use);
			}
			$headers = $xml;
			$this->debug("In serializeEnvelope, serialzied array of headers to $headers");
		}
		$headers = "<SOAP-ENV:Header>".$headers."</SOAP-ENV:Header>";
	}
	return
	'<?xml version="1.0" encoding="'.$this->soap_defencoding .'"?'.">".
	'<SOAP-ENV:Envelope'.$ns_string.">".
	$headers.
	"<SOAP-ENV:Body>".
		$body.
	"</SOAP-ENV:Body>".
	"</SOAP-ENV:Envelope>";
    }
    function formatDump($str){
		$str = htmlspecialchars($str);
		return nl2br($str);
    }
	function contractQname($qname){
		if (strrpos($qname, ':')) {
			$name = substr($qname, strrpos($qname, ':') + 1);
			$ns = substr($qname, 0, strrpos($qname, ':'));
			$p = $this->getPrefixFromNamespace($ns);
			if ($p) {
				return $p . ':' . $name;
			}
			return $qname;
		} else {
			return $qname;
		}
	}
	function expandQname($qname){
		if(strpos($qname,':') && !ereg('^http:
			$name = substr(strstr($qname,':'),1);
			$prefix = substr($qname,0,strpos($qname,':'));
			if(isset($this->namespaces[$prefix])){
				return $this->namespaces[$prefix].':'.$name;
			} else {
				return $qname;
			}
		} else {
			return $qname;
		}
	}
	function getLocalPart($str){
		if($sstr = strrchr($str,':')){
			return substr( $sstr, 1 );
		} else {
			return $str;
		}
	}
	function getPrefix($str){
		if($pos = strrpos($str,':')){
			return substr($str,0,$pos);
		}
		return false;
	}
	function getNamespaceFromPrefix($prefix){
		if (isset($this->namespaces[$prefix])) {
			return $this->namespaces[$prefix];
		}
		return false;
	}
	function getPrefixFromNamespace($ns) {
		foreach ($this->namespaces as $p => $n) {
			if ($ns == $n || $ns == $p) {
			    $this->usedNamespaces[$p] = $n;
				return $p;
			}
		}
		return false;
	}
	function getmicrotime() {
		if (function_exists('gettimeofday')) {
			$tod = gettimeofday();
			$sec = $tod['sec'];
			$usec = $tod['usec'];
		} else {
			$sec = time();
			$usec = 0;
		}
		return strftime('%Y-%m-%d %H:%M:%S', $sec) . '.' . sprintf('%06d', $usec);
	}
    function varDump($data) {
		ob_start();
		var_dump($data);
		$ret_val = ob_get_contents();
		ob_end_clean();
		return $ret_val;
	}
}
function timestamp_to_iso8601($timestamp,$utc=true){
	$datestr = date('Y-m-d\TH:i:sO',$timestamp);
	if($utc){
		$eregStr =
		'([0-9]{4})-'.	
		'([0-9]{2})-'.	
		'([0-9]{2})'.	
		'T'.			
		'([0-9]{2}):'.	
		'([0-9]{2}):'.	
		'([0-9]{2})(\.[0-9]*)?'. 
		'(Z|[+\-][0-9]{2}:?[0-9]{2})?'; 
		if(ereg($eregStr,$datestr,$regs)){
			return sprintf('%04d-%02d-%02dT%02d:%02d:%02dZ',$regs[1],$regs[2],$regs[3],$regs[4],$regs[5],$regs[6]);
		}
		return false;
	} else {
		return $datestr;
	}
}
function iso8601_to_timestamp($datestr){
	$eregStr =
	'([0-9]{4})-'.	
	'([0-9]{2})-'.	
	'([0-9]{2})'.	
	'T'.			
	'([0-9]{2}):'.	
	'([0-9]{2}):'.	
	'([0-9]{2})(\.[0-9]+)?'. 
	'(Z|[+\-][0-9]{2}:?[0-9]{2})?'; 
	if(ereg($eregStr,$datestr,$regs)){
		if($regs[8] != 'Z'){
			$op = substr($regs[8],0,1);
			$h = substr($regs[8],1,2);
			$m = substr($regs[8],strlen($regs[8])-2,2);
			if($op == '-'){
				$regs[4] = $regs[4] + $h;
				$regs[5] = $regs[5] + $m;
			} elseif($op == '+'){
				$regs[4] = $regs[4] - $h;
				$regs[5] = $regs[5] - $m;
			}
		}
		return strtotime("$regs[1]-$regs[2]-$regs[3] $regs[4]:$regs[5]:$regs[6]Z");
	} else {
		return false;
	}
}
function usleepWindows($usec)
{
	$start = gettimeofday();
	do
	{
		$stop = gettimeofday();
		$timePassed = 1000000 * ($stop['sec'] - $start['sec'])
		+ $stop['usec'] - $start['usec'];
	}
	while ($timePassed < $usec);
}
?><?php
class soap_fault extends nusoap_base {
	var $faultcode;
	var $faultactor;
	var $faultstring;
	var $faultdetail;
	function soap_fault($faultcode,$faultactor='',$faultstring='',$faultdetail=''){
		parent::nusoap_base();
		$this->faultcode = $faultcode;
		$this->faultactor = $faultactor;
		$this->faultstring = $faultstring;
		$this->faultdetail = $faultdetail;
	}
	function serialize(){
		$ns_string = '';
		foreach($this->namespaces as $k => $v){
			$ns_string .= "\n  xmlns:$k=\"$v\"";
		}
		$return_msg =
			'<?xml version="1.0" encoding="'.$this->soap_defencoding.'"?>'.
			'<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http:
				'<SOAP-ENV:Body>'.
				'<SOAP-ENV:Fault>'.
					$this->serialize_val($this->faultcode, 'faultcode').
					$this->serialize_val($this->faultactor, 'faultactor').
					$this->serialize_val($this->faultstring, 'faultstring').
					$this->serialize_val($this->faultdetail, 'detail').
				'</SOAP-ENV:Fault>'.
				'</SOAP-ENV:Body>'.
			'</SOAP-ENV:Envelope>';
		return $return_msg;
	}
}
?><?php
class XMLSchema extends nusoap_base  {
	var $schema = '';
	var $xml = '';
	var $enclosingNamespaces;
	var $schemaInfo = array();
	var $schemaTargetNamespace = '';
	var $attributes = array();
	var $complexTypes = array();
	var $complexTypeStack = array();
	var $currentComplexType = null;
	var $elements = array();
	var $elementStack = array();
	var $currentElement = null;
	var $simpleTypes = array();
	var $simpleTypeStack = array();
	var $currentSimpleType = null;
	var $imports = array();
	var $parser;
	var $position = 0;
	var $depth = 0;
	var $depth_array = array();
	var $message = array();
	var $defaultNamespace = array();
	function XMLSchema($schema='',$xml='',$namespaces=array()){
		parent::nusoap_base();
		$this->debug('xmlschema class instantiated, inside constructor');
		$this->schema = $schema;
		$this->xml = $xml;
		$this->enclosingNamespaces = $namespaces;
		$this->namespaces = array_merge($this->namespaces, $namespaces);
		if($schema != ''){
			$this->debug('initial schema file: '.$schema);
			$this->parseFile($schema, 'schema');
		}
		if($xml != ''){
			$this->debug('initial xml file: '.$xml);
			$this->parseFile($xml, 'xml');
		}
	}
	function parseFile($xml,$type){
		if($xml != ""){
			$xmlStr = @join("",@file($xml));
			if($xmlStr == ""){
				$msg = 'Error reading XML from '.$xml;
				$this->setError($msg);
				$this->debug($msg);
			return false;
			} else {
				$this->debug("parsing $xml");
				$this->parseString($xmlStr,$type);
				$this->debug("done parsing $xml");
			return true;
			}
		}
		return false;
	}
	function parseString($xml,$type){
		if($xml != ""){
	    	$this->parser = xml_parser_create();
	    	xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
	    	xml_set_object($this->parser, $this);
			if($type == "schema"){
		    	xml_set_element_handler($this->parser, 'schemaStartElement','schemaEndElement');
		    	xml_set_character_data_handler($this->parser,'schemaCharacterData');
			} elseif($type == "xml"){
				xml_set_element_handler($this->parser, 'xmlStartElement','xmlEndElement');
		    	xml_set_character_data_handler($this->parser,'xmlCharacterData');
			}
		    if(!xml_parse($this->parser,$xml,true)){
				$errstr = sprintf('XML error parsing XML schema on line %d: %s',
				xml_get_current_line_number($this->parser),
				xml_error_string(xml_get_error_code($this->parser))
				);
				$this->debug($errstr);
				$this->debug("XML payload:\n" . $xml);
				$this->setError($errstr);
	    	}
			xml_parser_free($this->parser);
		} else{
			$this->debug('no xml passed to parseString()!!');
			$this->setError('no xml passed to parseString()!!');
		}
	}
	function schemaStartElement($parser, $name, $attrs) {
		$pos = $this->position++;
		$depth = $this->depth++;
		$this->depth_array[$depth] = $pos;
		$this->message[$pos] = array('cdata' => ''); 
		if ($depth > 0) {
			$this->defaultNamespace[$pos] = $this->defaultNamespace[$this->depth_array[$depth - 1]];
		} else {
			$this->defaultNamespace[$pos] = false;
		}
		if($prefix = $this->getPrefix($name)){
			$name = $this->getLocalPart($name);
		} else {
        	$prefix = '';
        }
        if(count($attrs) > 0){
        	foreach($attrs as $k => $v){
				if(ereg("^xmlns",$k)){
                	if($ns_prefix = substr(strrchr($k,':'),1)){
						$this->namespaces[$ns_prefix] = $v;
					} else {
						$this->defaultNamespace[$pos] = $v;
						if (! $this->getPrefixFromNamespace($v)) {
							$this->namespaces['ns'.(count($this->namespaces)+1)] = $v;
						}
					}
					if($v == 'http:
						$this->XMLSchemaVersion = $v;
						$this->namespaces['xsi'] = $v.'-instance';
					}
				}
        	}
        	foreach($attrs as $k => $v){
                $k = strpos($k,':') ? $this->expandQname($k) : $k;
                $v = strpos($v,':') ? $this->expandQname($v) : $v;
        		$eAttrs[$k] = $v;
        	}
        	$attrs = $eAttrs;
        } else {
        	$attrs = array();
        }
		switch($name){
			case 'all':			
			case 'choice':
			case 'group':
			case 'sequence':
				$this->complexTypes[$this->currentComplexType]['compositor'] = $name;
			break;
			case 'attribute':	
            	$this->xdebug("parsing attribute:");
            	$this->appendDebug($this->varDump($attrs));
				if (!isset($attrs['form'])) {
					$attrs['form'] = $this->schemaInfo['attributeFormDefault'];
				}
            	if (isset($attrs['http:
					$v = $attrs['http:
					if (!strpos($v, ':')) {
						if ($this->defaultNamespace[$pos]) {
							$attrs['http:
						}
					}
            	}
                if(isset($attrs['name'])){
					$this->attributes[$attrs['name']] = $attrs;
					$aname = $attrs['name'];
				} elseif(isset($attrs['ref']) && $attrs['ref'] == 'http:
					if (isset($attrs['http:
	                	$aname = $attrs['http:
	                } else {
	                	$aname = '';
	                }
				} elseif(isset($attrs['ref'])){
					$aname = $attrs['ref'];
                    $this->attributes[$attrs['ref']] = $attrs;
				}
				if($this->currentComplexType){	
					$this->complexTypes[$this->currentComplexType]['attrs'][$aname] = $attrs;
				}
				if(isset($attrs['http:
					$this->complexTypes[$this->currentComplexType]['phpType'] = 'array';
                	$prefix = $this->getPrefix($aname);
					if(isset($attrs['http:
						$v = $attrs['http:
					} else {
						$v = '';
					}
                    if(strpos($v,'[,]')){
                        $this->complexTypes[$this->currentComplexType]['multidimensional'] = true;
                    }
                    $v = substr($v,0,strpos($v,'[')); 
                    if(!strpos($v,':') && isset($this->typemap[$this->XMLSchemaVersion][$v])){
                        $v = $this->XMLSchemaVersion.':'.$v;
                    }
                    $this->complexTypes[$this->currentComplexType]['arrayType'] = $v;
				}
			break;
			case 'complexContent':	
			break;
			case 'complexType':
				array_push($this->complexTypeStack, $this->currentComplexType);
				if(isset($attrs['name'])){
					$this->xdebug('processing named complexType '.$attrs['name']);
					$this->currentComplexType = $attrs['name'];
					$this->complexTypes[$this->currentComplexType] = $attrs;
					$this->complexTypes[$this->currentComplexType]['typeClass'] = 'complexType';
					if(isset($attrs['base']) && ereg(':Array$',$attrs['base'])){
						$this->xdebug('complexType is unusual array');
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'array';
					} else {
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'struct';
					}
				}else{
					$this->xdebug('processing unnamed complexType for element '.$this->currentElement);
					$this->currentComplexType = $this->currentElement . '_ContainedType';
					$this->complexTypes[$this->currentComplexType] = $attrs;
					$this->complexTypes[$this->currentComplexType]['typeClass'] = 'complexType';
					if(isset($attrs['base']) && ereg(':Array$',$attrs['base'])){
						$this->xdebug('complexType is unusual array');
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'array';
					} else {
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'struct';
					}
				}
			break;
			case 'element':
				array_push($this->elementStack, $this->currentElement);
				if (!isset($attrs['form'])) {
					$attrs['form'] = $this->schemaInfo['elementFormDefault'];
				}
				if(isset($attrs['type'])){
					$this->xdebug("processing typed element ".$attrs['name']." of type ".$attrs['type']);
					if (! $this->getPrefix($attrs['type'])) {
						if ($this->defaultNamespace[$pos]) {
							$attrs['type'] = $this->defaultNamespace[$pos] . ':' . $attrs['type'];
							$this->xdebug('used default namespace to make type ' . $attrs['type']);
						}
					}
					if ($this->currentComplexType && $this->complexTypes[$this->currentComplexType]['phpType'] == 'array') {
						$this->xdebug('arrayType for unusual array is ' . $attrs['type']);
						$this->complexTypes[$this->currentComplexType]['arrayType'] = $attrs['type'];
					}
					$this->currentElement = $attrs['name'];
					$this->elements[ $attrs['name'] ] = $attrs;
					$this->elements[ $attrs['name'] ]['typeClass'] = 'element';
					$ename = $attrs['name'];
				} elseif(isset($attrs['ref'])){
					$this->xdebug("processing element as ref to ".$attrs['ref']);
					$this->currentElement = "ref to ".$attrs['ref'];
					$ename = $this->getLocalPart($attrs['ref']);
				} else {
					$this->xdebug("processing untyped element ".$attrs['name']);
					$this->currentElement = $attrs['name'];
					$this->elements[ $attrs['name'] ] = $attrs;
					$this->elements[ $attrs['name'] ]['typeClass'] = 'element';
					$attrs['type'] = $this->schemaTargetNamespace . ':' . $attrs['name'] . '_ContainedType';
					$this->elements[ $attrs['name'] ]['type'] = $attrs['type'];
					$ename = $attrs['name'];
				}
				if(isset($ename) && $this->currentComplexType){
					$this->complexTypes[$this->currentComplexType]['elements'][$ename] = $attrs;
				}
			break;
			case 'enumeration':	
				$this->xdebug('enumeration ' . $attrs['value']);
				if ($this->currentSimpleType) {
					$this->simpleTypes[$this->currentSimpleType]['enumeration'][] = $attrs['value'];
				} elseif ($this->currentComplexType) {
					$this->complexTypes[$this->currentComplexType]['enumeration'][] = $attrs['value'];
				}
			break;
			case 'extension':	
				$this->xdebug('extension ' . $attrs['base']);
				if ($this->currentComplexType) {
					$this->complexTypes[$this->currentComplexType]['extensionBase'] = $attrs['base'];
				}
			break;
			case 'import':
			    if (isset($attrs['schemaLocation'])) {
                    $this->imports[$attrs['namespace']][] = array('location' => $attrs['schemaLocation'], 'loaded' => false);
				} else {
                    $this->imports[$attrs['namespace']][] = array('location' => '', 'loaded' => true);
					if (! $this->getPrefixFromNamespace($attrs['namespace'])) {
						$this->namespaces['ns'.(count($this->namespaces)+1)] = $attrs['namespace'];
					}
				}
			break;
			case 'list':	
			break;
			case 'restriction':	
				$this->xdebug('restriction ' . $attrs['base']);
				if($this->currentSimpleType){
					$this->simpleTypes[$this->currentSimpleType]['type'] = $attrs['base'];
				} elseif($this->currentComplexType){
					$this->complexTypes[$this->currentComplexType]['restrictionBase'] = $attrs['base'];
					if(strstr($attrs['base'],':') == ':Array'){
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'array';
					}
				}
			break;
			case 'schema':
				$this->schemaInfo = $attrs;
				$this->schemaInfo['schemaVersion'] = $this->getNamespaceFromPrefix($prefix);
				if (isset($attrs['targetNamespace'])) {
					$this->schemaTargetNamespace = $attrs['targetNamespace'];
				}
				if (!isset($attrs['elementFormDefault'])) {
					$this->schemaInfo['elementFormDefault'] = 'unqualified';
				}
				if (!isset($attrs['attributeFormDefault'])) {
					$this->schemaInfo['attributeFormDefault'] = 'unqualified';
				}
			break;
			case 'simpleContent':	
			break;
			case 'simpleType':
				array_push($this->simpleTypeStack, $this->currentSimpleType);
				if(isset($attrs['name'])){
					$this->xdebug("processing simpleType for name " . $attrs['name']);
					$this->currentSimpleType = $attrs['name'];
					$this->simpleTypes[ $attrs['name'] ] = $attrs;
					$this->simpleTypes[ $attrs['name'] ]['typeClass'] = 'simpleType';
					$this->simpleTypes[ $attrs['name'] ]['phpType'] = 'scalar';
				} else {
					$this->xdebug('processing unnamed simpleType for element '.$this->currentElement);
					$this->currentSimpleType = $this->currentElement . '_ContainedType';
					$this->simpleTypes[$this->currentSimpleType] = $attrs;
					$this->simpleTypes[$this->currentSimpleType]['phpType'] = 'scalar';
				}
			break;
			case 'union':	
			break;
			default:
		}
	}
	function schemaEndElement($parser, $name) {
		$this->depth--;
		if(isset($this->depth_array[$this->depth])){
        	$pos = $this->depth_array[$this->depth];
        }
		if ($prefix = $this->getPrefix($name)){
			$name = $this->getLocalPart($name);
		} else {
        	$prefix = '';
        }
		if($name == 'complexType'){
			$this->xdebug('done processing complexType ' . ($this->currentComplexType ? $this->currentComplexType : '(unknown)'));
			$this->currentComplexType = array_pop($this->complexTypeStack);
		}
		if($name == 'element'){
			$this->xdebug('done processing element ' . ($this->currentElement ? $this->currentElement : '(unknown)'));
			$this->currentElement = array_pop($this->elementStack);
		}
		if($name == 'simpleType'){
			$this->xdebug('done processing simpleType ' . ($this->currentSimpleType ? $this->currentSimpleType : '(unknown)'));
			$this->currentSimpleType = array_pop($this->simpleTypeStack);
		}
	}
	function schemaCharacterData($parser, $data){
		$pos = $this->depth_array[$this->depth - 1];
		$this->message[$pos]['cdata'] .= $data;
	}
	function serializeSchema(){
		$schemaPrefix = $this->getPrefixFromNamespace($this->XMLSchemaVersion);
		$xml = '';
		if (sizeof($this->imports) > 0) {
			foreach($this->imports as $ns => $list) {
				foreach ($list as $ii) {
					if ($ii['location'] != '') {
						$xml .= " <$schemaPrefix:import location=\"" . $ii['location'] . '" namespace="' . $ns . "\" />\n";
					} else {
						$xml .= " <$schemaPrefix:import namespace=\"" . $ns . "\" />\n";
					}
				}
			} 
		} 
		foreach($this->complexTypes as $typeName => $attrs){
			$contentStr = '';
			if(isset($attrs['elements']) && (count($attrs['elements']) > 0)){
				foreach($attrs['elements'] as $element => $eParts){
					if(isset($eParts['ref'])){
						$contentStr .= "   <$schemaPrefix:element ref=\"$element\"/>\n";
					} else {
						$contentStr .= "   <$schemaPrefix:element name=\"$element\" type=\"" . $this->contractQName($eParts['type']) . "\"";
						foreach ($eParts as $aName => $aValue) {
							if ($aName != 'name' && $aName != 'type') {
								$contentStr .= " $aName=\"$aValue\"";
							}
						}
						$contentStr .= "/>\n";
					}
				}
				if (isset($attrs['compositor']) && ($attrs['compositor'] != '')) {
					$contentStr = "  <$schemaPrefix:$attrs[compositor]>\n".$contentStr."  </$schemaPrefix:$attrs[compositor]>\n";
				}
			}
			if(isset($attrs['attrs']) && (count($attrs['attrs']) >= 1)){
				foreach($attrs['attrs'] as $attr => $aParts){
					$contentStr .= "    <$schemaPrefix:attribute";
					foreach ($aParts as $a => $v) {
						if ($a == 'ref' || $a == 'type') {
							$contentStr .= " $a=\"".$this->contractQName($v).'"';
						} elseif ($a == 'http:
							$this->usedNamespaces['wsdl'] = $this->namespaces['wsdl'];
							$contentStr .= ' wsdl:arrayType="'.$this->contractQName($v).'"';
						} else {
							$contentStr .= " $a=\"$v\"";
						}
					}
					$contentStr .= "/>\n";
				}
			}
			if (isset($attrs['restrictionBase']) && $attrs['restrictionBase'] != ''){
				$contentStr = "   <$schemaPrefix:restriction base=\"".$this->contractQName($attrs['restrictionBase'])."\">\n".$contentStr."   </$schemaPrefix:restriction>\n";
				if ((isset($attrs['elements']) && count($attrs['elements']) > 0) || (isset($attrs['attrs']) && count($attrs['attrs']) > 0)){
					$contentStr = "  <$schemaPrefix:complexContent>\n".$contentStr."  </$schemaPrefix:complexContent>\n";
				}
			}
			if($contentStr != ''){
				$contentStr = " <$schemaPrefix:complexType name=\"$typeName\">\n".$contentStr." </$schemaPrefix:complexType>\n";
			} else {
				$contentStr = " <$schemaPrefix:complexType name=\"$typeName\"/>\n";
			}
			$xml .= $contentStr;
		}
		if(isset($this->simpleTypes) && count($this->simpleTypes) > 0){
			foreach($this->simpleTypes as $typeName => $eParts){
				$xml .= " <$schemaPrefix:simpleType name=\"$typeName\">\n  <$schemaPrefix:restriction base=\"".$this->contractQName($eParts['type'])."\"/>\n";
				if (isset($eParts['enumeration'])) {
					foreach ($eParts['enumeration'] as $e) {
						$xml .= "  <$schemaPrefix:enumeration value=\"$e\"/>\n";
					}
				}
				$xml .= " </$schemaPrefix:simpleType>";
			}
		}
		if(isset($this->elements) && count($this->elements) > 0){
			foreach($this->elements as $element => $eParts){
				$xml .= " <$schemaPrefix:element name=\"$element\" type=\"".$this->contractQName($eParts['type'])."\"/>\n";
			}
		}
		if(isset($this->attributes) && count($this->attributes) > 0){
			foreach($this->attributes as $attr => $aParts){
				$xml .= " <$schemaPrefix:attribute name=\"$attr\" type=\"".$this->contractQName($aParts['type'])."\"\n/>";
			}
		}
		$el = "<$schemaPrefix:schema targetNamespace=\"$this->schemaTargetNamespace\"\n";
		foreach (array_diff($this->usedNamespaces, $this->enclosingNamespaces) as $nsp => $ns) {
			$el .= " xmlns:$nsp=\"$ns\"\n";
		}
		$xml = $el . ">\n".$xml."</$schemaPrefix:schema>\n";
		return $xml;
	}
	function xdebug($string){
		$this->debug('<' . $this->schemaTargetNamespace . '> '.$string);
	}
	function getPHPType($type,$ns){
		if(isset($this->typemap[$ns][$type])){
			return $this->typemap[$ns][$type];
		} elseif(isset($this->complexTypes[$type])){
			return $this->complexTypes[$type]['phpType'];
		}
		return false;
	}
	function getTypeDef($type){
		if(isset($this->complexTypes[$type])){
			$this->xdebug("in getTypeDef, found complexType $type");
			return $this->complexTypes[$type];
		} elseif(isset($this->simpleTypes[$type])){
			$this->xdebug("in getTypeDef, found simpleType $type");
			if (!isset($this->simpleTypes[$type]['phpType'])) {
				$uqType = substr($this->simpleTypes[$type]['type'], strrpos($this->simpleTypes[$type]['type'], ':') + 1);
				$ns = substr($this->simpleTypes[$type]['type'], 0, strrpos($this->simpleTypes[$type]['type'], ':'));
				$etype = $this->getTypeDef($uqType);
				if ($etype) {
					$this->xdebug("in getTypeDef, found type for simpleType $type:");
					$this->xdebug($this->varDump($etype));
					if (isset($etype['phpType'])) {
						$this->simpleTypes[$type]['phpType'] = $etype['phpType'];
					}
					if (isset($etype['elements'])) {
						$this->simpleTypes[$type]['elements'] = $etype['elements'];
					}
				}
			}
			return $this->simpleTypes[$type];
		} elseif(isset($this->elements[$type])){
			$this->xdebug("in getTypeDef, found element $type");
			if (!isset($this->elements[$type]['phpType'])) {
				$uqType = substr($this->elements[$type]['type'], strrpos($this->elements[$type]['type'], ':') + 1);
				$ns = substr($this->elements[$type]['type'], 0, strrpos($this->elements[$type]['type'], ':'));
				$etype = $this->getTypeDef($uqType);
				if ($etype) {
					$this->xdebug("in getTypeDef, found type for element $type:");
					$this->xdebug($this->varDump($etype));
					if (isset($etype['phpType'])) {
						$this->elements[$type]['phpType'] = $etype['phpType'];
					}
					if (isset($etype['elements'])) {
						$this->elements[$type]['elements'] = $etype['elements'];
					}
				} elseif ($ns == 'http:
					$this->xdebug("in getTypeDef, element $type is an XSD type");
					$this->elements[$type]['phpType'] = 'scalar';
				}
			}
			return $this->elements[$type];
		} elseif(isset($this->attributes[$type])){
			$this->xdebug("in getTypeDef, found attribute $type");
			return $this->attributes[$type];
		} elseif (ereg('_ContainedType$', $type)) {
			$this->xdebug("in getTypeDef, have an untyped element $type");
			$typeDef['typeClass'] = 'simpleType';
			$typeDef['phpType'] = 'scalar';
			$typeDef['type'] = 'http:
			return $typeDef;
		}
		$this->xdebug("in getTypeDef, did not find $type");
		return false;
	}
    function serializeTypeDef($type){
	if($typeDef = $this->getTypeDef($type)){
		$str .= '<'.$type;
	    if(is_array($typeDef['attrs'])){
		foreach($attrs as $attName => $data){
		    $str .= " $attName=\"{type = ".$data['type']."}\"";
		}
	    }
	    $str .= " xmlns=\"".$this->schema['targetNamespace']."\"";
	    if(count($typeDef['elements']) > 0){
		$str .= ">";
		foreach($typeDef['elements'] as $element => $eData){
		    $str .= $this->serializeTypeDef($element);
		}
		$str .= "</$type>";
	    } elseif($typeDef['typeClass'] == 'element') {
		$str .= "></$type>";
	    } else {
		$str .= "/>";
	    }
			return $str;
	}
    	return false;
    }
	function typeToForm($name,$type){
		if($typeDef = $this->getTypeDef($type)){
			if($typeDef['phpType'] == 'struct'){
				$buffer .= '<table>';
				foreach($typeDef['elements'] as $child => $childDef){
					$buffer .= "
					<tr><td align='right'>$childDef[name] (type: ".$this->getLocalPart($childDef['type'])."):</td>
					<td><input type='text' name='parameters[".$name."][$childDef[name]]'></td></tr>";
				}
				$buffer .= '</table>';
			} elseif($typeDef['phpType'] == 'array'){
				$buffer .= '<table>';
				for($i=0;$i < 3; $i++){
					$buffer .= "
					<tr><td align='right'>array item (type: $typeDef[arrayType]):</td>
					<td><input type='text' name='parameters[".$name."][]'></td></tr>";
				}
				$buffer .= '</table>';
			} else {
				$buffer .= "<input type='text' name='parameters[$name]'>";
			}
		} else {
			$buffer .= "<input type='text' name='parameters[$name]'>";
		}
		return $buffer;
	}
	function addComplexType($name,$typeClass='complexType',$phpType='array',$compositor='',$restrictionBase='',$elements=array(),$attrs=array(),$arrayType=''){
		$this->complexTypes[$name] = array(
	    'name'		=> $name,
	    'typeClass'	=> $typeClass,
	    'phpType'	=> $phpType,
		'compositor'=> $compositor,
	    'restrictionBase' => $restrictionBase,
		'elements'	=> $elements,
	    'attrs'		=> $attrs,
	    'arrayType'	=> $arrayType
		);
		$this->xdebug("addComplexType $name:");
		$this->appendDebug($this->varDump($this->complexTypes[$name]));
	}
	function addSimpleType($name, $restrictionBase='', $typeClass='simpleType', $phpType='scalar', $enumeration=array()) {
		$this->simpleTypes[$name] = array(
	    'name'			=> $name,
	    'typeClass'		=> $typeClass,
	    'phpType'		=> $phpType,
	    'type'			=> $restrictionBase,
	    'enumeration'	=> $enumeration
		);
		$this->xdebug("addSimpleType $name:");
		$this->appendDebug($this->varDump($this->simpleTypes[$name]));
	}
	function addElement($attrs) {
		if (! $this->getPrefix($attrs['type'])) {
			$attrs['type'] = $this->schemaTargetNamespace . ':' . $attrs['type'];
		}
		$this->elements[ $attrs['name'] ] = $attrs;
		$this->elements[ $attrs['name'] ]['typeClass'] = 'element';
		$this->xdebug("addElement " . $attrs['name']);
		$this->appendDebug($this->varDump($this->elements[ $attrs['name'] ]));
	}
}
?><?php
class soapval extends nusoap_base {
	var $name;
	var $type;
	var $value;
	var $element_ns;
	var $type_ns;
	var $attributes;
  	function soapval($name='soapval',$type=false,$value=-1,$element_ns=false,$type_ns=false,$attributes=false) {
		parent::nusoap_base();
		$this->name = $name;
		$this->type = $type;
		$this->value = $value;
		$this->element_ns = $element_ns;
		$this->type_ns = $type_ns;
		$this->attributes = $attributes;
    }
	function serialize($use='encoded') {
		return $this->serialize_val($this->value,$this->name,$this->type,$this->element_ns,$this->type_ns,$this->attributes,$use);
    }
	function decode(){
		return $this->value;
	}
}
?><?php
class soap_transport_http extends nusoap_base {
	var $url = '';
	var $uri = '';
	var $digest_uri = '';
	var $scheme = '';
	var $host = '';
	var $port = '';
	var $path = '';
	var $request_method = 'POST';
	var $protocol_version = '1.0';
	var $encoding = '';
	var $outgoing_headers = array();
	var $incoming_headers = array();
	var $incoming_cookies = array();
	var $outgoing_payload = '';
	var $incoming_payload = '';
	var $useSOAPAction = true;
	var $persistentConnection = false;
	var $ch = false;	
	var $username = '';
	var $password = '';
	var $authtype = '';
	var $digestRequest = array();
	var $certRequest = array();	
	function soap_transport_http($url){
		parent::nusoap_base();
		$this->setURL($url);
		ereg('\$Revisio' . 'n: ([^ ]+)', $this->revision, $rev);
		$this->outgoing_headers['User-Agent'] = $this->title.'/'.$this->version.' ('.$rev[1].')';
		$this->debug('set User-Agent: ' . $this->outgoing_headers['User-Agent']);
	}
	function setURL($url) {
		$this->url = $url;
		$u = parse_url($url);
		foreach($u as $k => $v){
			$this->debug("$k = $v");
			$this->$k = $v;
		}
		if(isset($u['query']) && $u['query'] != ''){
            $this->path .= '?' . $u['query'];
		}
		if(!isset($u['port'])){
			if($u['scheme'] == 'https'){
				$this->port = 443;
			} else {
				$this->port = 80;
			}
		}
		$this->uri = $this->path;
		$this->digest_uri = $this->uri;
		if (!isset($u['port'])) {
			$this->outgoing_headers['Host'] = $this->host;
		} else {
			$this->outgoing_headers['Host'] = $this->host.':'.$this->port;
		}
		$this->debug('set Host: ' . $this->outgoing_headers['Host']);
		if (isset($u['user']) && $u['user'] != '') {
			$this->setCredentials(urldecode($u['user']), isset($u['pass']) ? urldecode($u['pass']) : '');
		}
	}
	function connect($connection_timeout=0,$response_timeout=30){
		$this->debug("connect connection_timeout $connection_timeout, response_timeout $response_timeout, scheme $this->scheme, host $this->host, port $this->port");
	  if ($this->scheme == 'http' || $this->scheme == 'ssl') {
		if($this->persistentConnection && isset($this->fp) && is_resource($this->fp)){
			if (!feof($this->fp)) {
				$this->debug('Re-use persistent connection');
				return true;
			}
			fclose($this->fp);
			$this->debug('Closed persistent connection at EOF');
		}
		if ($this->scheme == 'ssl') {
			$host = 'ssl:
		} else {
			$host = $this->host;
		}
		$this->debug('calling fsockopen with host ' . $host . ' connection_timeout ' . $connection_timeout);
		if($connection_timeout > 0){
			$this->fp = @fsockopen( $host, $this->port, $this->errno, $this->error_str, $connection_timeout);
		} else {
			$this->fp = @fsockopen( $host, $this->port, $this->errno, $this->error_str);
		}
		if(!$this->fp) {
			$msg = 'Couldn\'t open socket connection to server ' . $this->url;
			if ($this->errno) {
				$msg .= ', Error ('.$this->errno.'): '.$this->error_str;
			} else {
				$msg .= ' prior to connect().  This is often a problem looking up the host name.';
			}
			$this->debug($msg);
			$this->setError($msg);
			return false;
		}
		$this->debug('set response timeout to ' . $response_timeout);
		socket_set_timeout( $this->fp, $response_timeout);
		$this->debug('socket connected');
		return true;
	  } else if ($this->scheme == 'https') {
		if (!extension_loaded('curl')) {
			$this->setError('CURL Extension, or OpenSSL extension w/ PHP version >= 4.3 is required for HTTPS');
			return false;
		}
		$this->debug('connect using https');
		$this->ch = curl_init();
		$hostURL = ($this->port != '') ? "https:
		$hostURL .= $this->path;
		curl_setopt($this->ch, CURLOPT_URL, $hostURL);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_HEADER, 1);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		if ($this->persistentConnection) {
			$this->persistentConnection = false;
			$this->outgoing_headers['Connection'] = 'close';
			$this->debug('set Connection: ' . $this->outgoing_headers['Connection']);
		}
		if ($connection_timeout != 0) {
			curl_setopt($this->ch, CURLOPT_TIMEOUT, $connection_timeout);
		}
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
		if ($this->authtype == 'certificate') {
			if (isset($this->certRequest['cainfofile'])) {
				curl_setopt($this->ch, CURLOPT_CAINFO, $this->certRequest['cainfofile']);
			}
			if (isset($this->certRequest['verifypeer'])) {
				curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->certRequest['verifypeer']);
			} else {
				curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 1);
			}
			if (isset($this->certRequest['verifyhost'])) {
				curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->certRequest['verifyhost']);
			} else {
				curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 1);
			}
			if (isset($this->certRequest['sslcertfile'])) {
				curl_setopt($this->ch, CURLOPT_SSLCERT, $this->certRequest['sslcertfile']);
			}
			if (isset($this->certRequest['sslkeyfile'])) {
				curl_setopt($this->ch, CURLOPT_SSLKEY, $this->certRequest['sslkeyfile']);
			}
			if (isset($this->certRequest['passphrase'])) {
				curl_setopt($this->ch, CURLOPT_SSLKEYPASSWD , $this->certRequest['passphrase']);
			}
		}
		$this->debug('cURL connection set up');
		return true;
	  } else {
		$this->setError('Unknown scheme ' . $this->scheme);
		$this->debug('Unknown scheme ' . $this->scheme);
		return false;
	  }
	}
	function send($data, $timeout=0, $response_timeout=30, $cookies=NULL) {
		$this->debug('entered send() with data of length: '.strlen($data));
		$this->tryagain = true;
		$tries = 0;
		while ($this->tryagain) {
			$this->tryagain = false;
			if ($tries++ < 2) {
				if (!$this->connect($timeout, $response_timeout)){
					return false;
				}
				if (!$this->sendRequest($data, $cookies)){
					return false;
				}
				$respdata = $this->getResponse();
			} else {
				$this->setError('Too many tries to get an OK response');
			}
		}		
		$this->debug('end of send()');
		return $respdata;
	}
	function sendHTTPS($data, $timeout=0, $response_timeout=30, $cookies) {
		return $this->send($data, $timeout, $response_timeout, $cookies);
	}
	function setCredentials($username, $password, $authtype = 'basic', $digestRequest = array(), $certRequest = array()) {
		$this->debug("Set credentials for authtype $authtype");
		if ($authtype == 'basic') {
			$this->outgoing_headers['Authorization'] = 'Basic '.base64_encode(str_replace(':','',$username).':'.$password);
		} elseif ($authtype == 'digest') {
			if (isset($digestRequest['nonce'])) {
				$digestRequest['nc'] = isset($digestRequest['nc']) ? $digestRequest['nc']++ : 1;
				$A1 = $username. ':' . (isset($digestRequest['realm']) ? $digestRequest['realm'] : '') . ':' . $password;
				$HA1 = md5($A1);
				$A2 = 'POST:' . $this->digest_uri;
				$HA2 =  md5($A2);
				$unhashedDigest = '';
				$nonce = isset($digestRequest['nonce']) ? $digestRequest['nonce'] : '';
				$cnonce = $nonce;
				if ($digestRequest['qop'] != '') {
					$unhashedDigest = $HA1 . ':' . $nonce . ':' . sprintf("%08d", $digestRequest['nc']) . ':' . $cnonce . ':' . $digestRequest['qop'] . ':' . $HA2;
				} else {
					$unhashedDigest = $HA1 . ':' . $nonce . ':' . $HA2;
				}
				$hashedDigest = md5($unhashedDigest);
				$this->outgoing_headers['Authorization'] = 'Digest username="' . $username . '", realm="' . $digestRequest['realm'] . '", nonce="' . $nonce . '", uri="' . $this->digest_uri . '", cnonce="' . $cnonce . '", nc=' . sprintf("%08x", $digestRequest['nc']) . ', qop="' . $digestRequest['qop'] . '", response="' . $hashedDigest . '"';
			}
		} elseif ($authtype == 'certificate') {
			$this->certRequest = $certRequest;
		}
		$this->username = $username;
		$this->password = $password;
		$this->authtype = $authtype;
		$this->digestRequest = $digestRequest;
		if (isset($this->outgoing_headers['Authorization'])) {
			$this->debug('set Authorization: ' . substr($this->outgoing_headers['Authorization'], 0, 12) . '...');
		} else {
			$this->debug('Authorization header not set');
		}
	}
	function setSOAPAction($soapaction) {
		$this->outgoing_headers['SOAPAction'] = '"' . $soapaction . '"';
		$this->debug('set SOAPAction: ' . $this->outgoing_headers['SOAPAction']);
	}
	function setEncoding($enc='gzip, deflate') {
		if (function_exists('gzdeflate')) {
			$this->protocol_version = '1.1';
			$this->outgoing_headers['Accept-Encoding'] = $enc;
			$this->debug('set Accept-Encoding: ' . $this->outgoing_headers['Accept-Encoding']);
			if (!isset($this->outgoing_headers['Connection'])) {
				$this->outgoing_headers['Connection'] = 'close';
				$this->persistentConnection = false;
				$this->debug('set Connection: ' . $this->outgoing_headers['Connection']);
			}
			set_magic_quotes_runtime(0);
			$this->encoding = $enc;
		}
	}
	function setProxy($proxyhost, $proxyport, $proxyusername = '', $proxypassword = '') {
		$this->uri = $this->url;
		$this->host = $proxyhost;
		$this->port = $proxyport;
		if ($proxyusername != '' && $proxypassword != '') {
			$this->outgoing_headers['Proxy-Authorization'] = ' Basic '.base64_encode($proxyusername.':'.$proxypassword);
			$this->debug('set Proxy-Authorization: ' . $this->outgoing_headers['Proxy-Authorization']);
		}
	}
	function decodeChunked($buffer, $lb){
		$length = 0;
		$new = '';
		$chunkend = strpos($buffer, $lb);
		if ($chunkend == FALSE) {
			$this->debug('no linebreak found in decodeChunked');
			return $new;
		}
		$temp = substr($buffer,0,$chunkend);
		$chunk_size = hexdec( trim($temp) );
		$chunkstart = $chunkend + strlen($lb);
		while ($chunk_size > 0) {
			$this->debug("chunkstart: $chunkstart chunk_size: $chunk_size");
			$chunkend = strpos( $buffer, $lb, $chunkstart + $chunk_size);
		  	if ($chunkend == FALSE) {
		  	    $chunk = substr($buffer,$chunkstart);
		    	$new .= $chunk;
		  	    $length += strlen($chunk);
		  	    break;
			}
		  	$chunk = substr($buffer,$chunkstart,$chunkend-$chunkstart);
		  	$new .= $chunk;
		  	$length += strlen($chunk);
		  	$chunkstart = $chunkend + strlen($lb);
		  	$chunkend = strpos($buffer, $lb, $chunkstart) + strlen($lb);
			if ($chunkend == FALSE) {
				break; 
			}
			$temp = substr($buffer,$chunkstart,$chunkend-$chunkstart);
			$chunk_size = hexdec( trim($temp) );
			$chunkstart = $chunkend;
		}
		return $new;
	}
	function buildPayload($data, $cookie_str = '') {
		$this->outgoing_headers['Content-Length'] = strlen($data);
		$this->debug('set Content-Length: ' . $this->outgoing_headers['Content-Length']);
		$req = "$this->request_method $this->uri HTTP/$this->protocol_version";
		$this->debug("HTTP request: $req");
		$this->outgoing_payload = "$req\r\n";
		foreach($this->outgoing_headers as $k => $v){
			$hdr = $k.': '.$v;
			$this->debug("HTTP header: $hdr");
			$this->outgoing_payload .= "$hdr\r\n";
		}
		if ($cookie_str != '') {
			$hdr = 'Cookie: '.$cookie_str;
			$this->debug("HTTP header: $hdr");
			$this->outgoing_payload .= "$hdr\r\n";
		}
		$this->outgoing_payload .= "\r\n";
		$this->outgoing_payload .= $data;
	}
	function sendRequest($data, $cookies = NULL) {
		$cookie_str = $this->getCookiesForRequest($cookies, (($this->scheme == 'ssl') || ($this->scheme == 'https')));
		$this->buildPayload($data, $cookie_str);
	  if ($this->scheme == 'http' || $this->scheme == 'ssl') {
		if(!fputs($this->fp, $this->outgoing_payload, strlen($this->outgoing_payload))) {
			$this->setError('couldn\'t write message data to socket');
			$this->debug('couldn\'t write message data to socket');
			return false;
		}
		$this->debug('wrote data to socket, length = ' . strlen($this->outgoing_payload));
		return true;
	  } else if ($this->scheme == 'https') {
		foreach($this->outgoing_headers as $k => $v){
			$curl_headers[] = "$k: $v";
		}
		if ($cookie_str != '') {
			$curl_headers[] = 'Cookie: ' . $cookie_str;
		}
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $curl_headers);
		if ($this->request_method == "POST") {
	  		curl_setopt($this->ch, CURLOPT_POST, 1);
	  		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
	  	} else {
	  	}
		$this->debug('set cURL payload');
		return true;
	  }
	}
	function getResponse(){
		$this->incoming_payload = '';
	  if ($this->scheme == 'http' || $this->scheme == 'ssl') {
	    $data = '';
	    while (!isset($lb)){
			if(feof($this->fp)) {
				$this->incoming_payload = $data;
				$this->debug('found no headers before EOF after length ' . strlen($data));
				$this->debug("received before EOF:\n" . $data);
				$this->setError('server failed to send headers');
				return false;
			}
			$tmp = fgets($this->fp, 256);
			$tmplen = strlen($tmp);
			$this->debug("read line of $tmplen bytes: " . trim($tmp));
			if ($tmplen == 0) {
				$this->incoming_payload = $data;
				$this->debug('socket read of headers timed out after length ' . strlen($data));
				$this->debug("read before timeout: " . $data);
				$this->setError('socket read of headers timed out');
				return false;
			}
			$data .= $tmp;
			$pos = strpos($data,"\r\n\r\n");
			if($pos > 1){
				$lb = "\r\n";
			} else {
				$pos = strpos($data,"\n\n");
				if($pos > 1){
					$lb = "\n";
				}
			}
			if(isset($lb) && ereg('^HTTP/1.1 100',$data)){
				unset($lb);
				$data = '';
			}
		}
		$this->incoming_payload .= $data;
		$this->debug('found end of headers after length ' . strlen($data));
		$header_data = trim(substr($data,0,$pos));
		$header_array = explode($lb,$header_data);
		$this->incoming_headers = array();
		$this->incoming_cookies = array();
		foreach($header_array as $header_line){
			$arr = explode(':',$header_line, 2);
			if(count($arr) > 1){
				$header_name = strtolower(trim($arr[0]));
				$this->incoming_headers[$header_name] = trim($arr[1]);
				if ($header_name == 'set-cookie') {
					$cookie = $this->parseCookie(trim($arr[1]));
					if ($cookie) {
						$this->incoming_cookies[] = $cookie;
						$this->debug('found cookie: ' . $cookie['name'] . ' = ' . $cookie['value']);
					} else {
						$this->debug('did not find cookie in ' . trim($arr[1]));
					}
    			}
			} else if (isset($header_name)) {
				$this->incoming_headers[$header_name] .= $lb . ' ' . $header_line;
			}
		}
		if (isset($this->incoming_headers['transfer-encoding']) && strtolower($this->incoming_headers['transfer-encoding']) == 'chunked') {
			$content_length =  2147483647;	
			$chunked = true;
			$this->debug("want to read chunked content");
		} elseif (isset($this->incoming_headers['content-length'])) {
			$content_length = $this->incoming_headers['content-length'];
			$chunked = false;
			$this->debug("want to read content of length $content_length");
		} else {
			$content_length =  2147483647;
			$chunked = false;
			$this->debug("want to read content to EOF");
		}
		$data = '';
		do {
			if ($chunked) {
				$tmp = fgets($this->fp, 256);
				$tmplen = strlen($tmp);
				$this->debug("read chunk line of $tmplen bytes");
				if ($tmplen == 0) {
					$this->incoming_payload = $data;
					$this->debug('socket read of chunk length timed out after length ' . strlen($data));
					$this->debug("read before timeout:\n" . $data);
					$this->setError('socket read of chunk length timed out');
					return false;
				}
				$content_length = hexdec(trim($tmp));
				$this->debug("chunk length $content_length");
			}
			$strlen = 0;
		    while (($strlen < $content_length) && (!feof($this->fp))) {
		    	$readlen = min(8192, $content_length - $strlen);
				$tmp = fread($this->fp, $readlen);
				$tmplen = strlen($tmp);
				$this->debug("read buffer of $tmplen bytes");
				if (($tmplen == 0) && (!feof($this->fp))) {
					$this->incoming_payload = $data;
					$this->debug('socket read of body timed out after length ' . strlen($data));
					$this->debug("read before timeout:\n" . $data);
					$this->setError('socket read of body timed out');
					return false;
				}
				$strlen += $tmplen;
				$data .= $tmp;
			}
			if ($chunked && ($content_length > 0)) {
				$tmp = fgets($this->fp, 256);
				$tmplen = strlen($tmp);
				$this->debug("read chunk terminator of $tmplen bytes");
				if ($tmplen == 0) {
					$this->incoming_payload = $data;
					$this->debug('socket read of chunk terminator timed out after length ' . strlen($data));
					$this->debug("read before timeout:\n" . $data);
					$this->setError('socket read of chunk terminator timed out');
					return false;
				}
			}
		} while ($chunked && ($content_length > 0) && (!feof($this->fp)));
		if (feof($this->fp)) {
			$this->debug('read to EOF');
		}
		$this->debug('read body of length ' . strlen($data));
		$this->incoming_payload .= $data;
		$this->debug('received a total of '.strlen($this->incoming_payload).' bytes of data from server');
		if(
			(isset($this->incoming_headers['connection']) && strtolower($this->incoming_headers['connection']) == 'close') || 
			(! $this->persistentConnection) || feof($this->fp)){
			fclose($this->fp);
			$this->fp = false;
			$this->debug('closed socket');
		}
		if($this->incoming_payload == ''){
			$this->setError('no response from server');
			return false;
		}
	  } else if ($this->scheme == 'https') {
		$this->debug('send and receive with cURL');
		$this->incoming_payload = curl_exec($this->ch);
		$data = $this->incoming_payload;
        $cErr = curl_error($this->ch);
		if ($cErr != '') {
        	$err = 'cURL ERROR: '.curl_errno($this->ch).': '.$cErr.'<br>';
			foreach(curl_getinfo($this->ch) as $k => $v){
				$err .= "$k: $v<br>";
			}
			$this->debug($err);
			$this->setError($err);
			curl_close($this->ch);
	    	return false;
		} else {
		}
		$this->debug('No cURL error, closing cURL');
		curl_close($this->ch);
		while (ereg('^HTTP/1.1 100',$data)) {
			if ($pos = strpos($data,"\r\n\r\n")) {
				$data = ltrim(substr($data,$pos));
			} elseif($pos = strpos($data,"\n\n") ) {
				$data = ltrim(substr($data,$pos));
			}
		}
		if ($pos = strpos($data,"\r\n\r\n")) {
			$lb = "\r\n";
		} elseif( $pos = strpos($data,"\n\n")) {
			$lb = "\n";
		} else {
			$this->debug('no proper separation of headers and document');
			$this->setError('no proper separation of headers and document');
			return false;
		}
		$header_data = trim(substr($data,0,$pos));
		$header_array = explode($lb,$header_data);
		$data = ltrim(substr($data,$pos));
		$this->debug('found proper separation of headers and document');
		$this->debug('cleaned data, stringlen: '.strlen($data));
		foreach ($header_array as $header_line) {
			$arr = explode(':',$header_line,2);
			if(count($arr) > 1){
				$header_name = strtolower(trim($arr[0]));
				$this->incoming_headers[$header_name] = trim($arr[1]);
				if ($header_name == 'set-cookie') {
					$cookie = $this->parseCookie(trim($arr[1]));
					if ($cookie) {
						$this->incoming_cookies[] = $cookie;
						$this->debug('found cookie: ' . $cookie['name'] . ' = ' . $cookie['value']);
					} else {
						$this->debug('did not find cookie in ' . trim($arr[1]));
					}
    			}
			} else if (isset($header_name)) {
				$this->incoming_headers[$header_name] .= $lb . ' ' . $header_line;
			}
		}
	  }
		$arr = explode(' ', $header_array[0], 3);
		$http_version = $arr[0];
		$http_status = intval($arr[1]);
		$http_reason = count($arr) > 2 ? $arr[2] : '';
 		if (isset($this->incoming_headers['location']) && $http_status == 301) {
 			$this->debug("Got 301 $http_reason with Location: " . $this->incoming_headers['location']);
 			$this->setURL($this->incoming_headers['location']);
			$this->tryagain = true;
			return false;
		}
 		if (isset($this->incoming_headers['www-authenticate']) && $http_status == 401) {
 			$this->debug("Got 401 $http_reason with WWW-Authenticate: " . $this->incoming_headers['www-authenticate']);
 			if (strstr($this->incoming_headers['www-authenticate'], "Digest ")) {
 				$this->debug('Server wants digest authentication');
 				$digestString = str_replace('Digest ', '', $this->incoming_headers['www-authenticate']);
 				$digestElements = explode(',', $digestString);
 				foreach ($digestElements as $val) {
 					$tempElement = explode('=', trim($val), 2);
 					$digestRequest[$tempElement[0]] = str_replace("\"", '', $tempElement[1]);
 				}
 				if (isset($digestRequest['nonce'])) {
 					$this->setCredentials($this->username, $this->password, 'digest', $digestRequest);
 					$this->tryagain = true;
 					return false;
 				}
 			}
			$this->debug('HTTP authentication failed');
			$this->setError('HTTP authentication failed');
			return false;
 		}
		if (
			($http_status >= 300 && $http_status <= 307) ||
			($http_status >= 400 && $http_status <= 417) ||
			($http_status >= 501 && $http_status <= 505)
		   ) {
			$this->setError("Unsupported HTTP response status $http_status $http_reason (soapclient->response has contents of the response)");
			return false;
		}
		if(isset($this->incoming_headers['content-encoding']) && $this->incoming_headers['content-encoding'] != ''){
			if(strtolower($this->incoming_headers['content-encoding']) == 'deflate' || strtolower($this->incoming_headers['content-encoding']) == 'gzip'){
    			if(function_exists('gzinflate')){
					$this->debug('The gzinflate function exists');
					$datalen = strlen($data);
					if ($this->incoming_headers['content-encoding'] == 'deflate') {
						if ($degzdata = @gzinflate($data)) {
	    					$data = $degzdata;
	    					$this->debug('The payload has been inflated to ' . strlen($data) . ' bytes');
	    					if (strlen($data) < $datalen) {
		    					$this->debug('The inflated payload is smaller than the gzipped one; try again');
								if ($degzdata = @gzinflate($data)) {
			    					$data = $degzdata;
			    					$this->debug('The payload has been inflated again to ' . strlen($data) . ' bytes');
								}
	    					}
	    				} else {
	    					$this->debug('Error using gzinflate to inflate the payload');
	    					$this->setError('Error using gzinflate to inflate the payload');
	    				}
					} elseif ($this->incoming_headers['content-encoding'] == 'gzip') {
						if ($degzdata = @gzinflate(substr($data, 10))) {	
							$data = $degzdata;
	    					$this->debug('The payload has been un-gzipped to ' . strlen($data) . ' bytes');
	    					if (strlen($data) < $datalen) {
		    					$this->debug('The un-gzipped payload is smaller than the gzipped one; try again');
								if ($degzdata = @gzinflate(substr($data, 10))) {
			    					$data = $degzdata;
			    					$this->debug('The payload has been un-gzipped again to ' . strlen($data) . ' bytes');
								}
	    					}
	    				} else {
	    					$this->debug('Error using gzinflate to un-gzip the payload');
							$this->setError('Error using gzinflate to un-gzip the payload');
	    				}
					}
					$this->incoming_payload = $header_data.$lb.$lb.$data;
    			} else {
					$this->debug('The server sent compressed data. Your php install must have the Zlib extension compiled in to support this.');
					$this->setError('The server sent compressed data. Your php install must have the Zlib extension compiled in to support this.');
				}
			} else {
				$this->debug('Unsupported Content-Encoding ' . $this->incoming_headers['content-encoding']);
				$this->setError('Unsupported Content-Encoding ' . $this->incoming_headers['content-encoding']);
			}
		} else {
			$this->debug('No Content-Encoding header');
		}
		if(strlen($data) == 0){
			$this->debug('no data after headers!');
			$this->setError('no data present after HTTP headers');
			return false;
		}
		return $data;
	}
	function setContentType($type, $charset = false) {
		$this->outgoing_headers['Content-Type'] = $type . ($charset ? '; charset=' . $charset : '');
		$this->debug('set Content-Type: ' . $this->outgoing_headers['Content-Type']);
	}
	function usePersistentConnection(){
		if (isset($this->outgoing_headers['Accept-Encoding'])) {
			return false;
		}
		$this->protocol_version = '1.1';
		$this->persistentConnection = true;
		$this->outgoing_headers['Connection'] = 'Keep-Alive';
		$this->debug('set Connection: ' . $this->outgoing_headers['Connection']);
		return true;
	}
	function parseCookie($cookie_str) {
		$cookie_str = str_replace('; ', ';', $cookie_str) . ';';
		$data = split(';', $cookie_str);
		$value_str = $data[0];
		$cookie_param = 'domain=';
		$start = strpos($cookie_str, $cookie_param);
		if ($start > 0) {
			$domain = substr($cookie_str, $start + strlen($cookie_param));
			$domain = substr($domain, 0, strpos($domain, ';'));
		} else {
			$domain = '';
		}
		$cookie_param = 'expires=';
		$start = strpos($cookie_str, $cookie_param);
		if ($start > 0) {
			$expires = substr($cookie_str, $start + strlen($cookie_param));
			$expires = substr($expires, 0, strpos($expires, ';'));
		} else {
			$expires = '';
		}
		$cookie_param = 'path=';
		$start = strpos($cookie_str, $cookie_param);
		if ( $start > 0 ) {
			$path = substr($cookie_str, $start + strlen($cookie_param));
			$path = substr($path, 0, strpos($path, ';'));
		} else {
			$path = '/';
		}
		$cookie_param = ';secure;';
		if (strpos($cookie_str, $cookie_param) !== FALSE) {
			$secure = true;
		} else {
			$secure = false;
		}
		$sep_pos = strpos($value_str, '=');
		if ($sep_pos) {
			$name = substr($value_str, 0, $sep_pos);
			$value = substr($value_str, $sep_pos + 1);
			$cookie= array(	'name' => $name,
			                'value' => $value,
							'domain' => $domain,
							'path' => $path,
							'expires' => $expires,
							'secure' => $secure
							);		
			return $cookie;
		}
		return false;
	}
	function getCookiesForRequest($cookies, $secure=false) {
		$cookie_str = '';
		if ((! is_null($cookies)) && (is_array($cookies))) {
			foreach ($cookies as $cookie) {
				if (! is_array($cookie)) {
					continue;
				}
	    		$this->debug("check cookie for validity: ".$cookie['name'].'='.$cookie['value']);
				if ((isset($cookie['expires'])) && (! empty($cookie['expires']))) {
					if (strtotime($cookie['expires']) <= time()) {
						$this->debug('cookie has expired');
						continue;
					}
				}
				if ((isset($cookie['domain'])) && (! empty($cookie['domain']))) {
					$domain = preg_quote($cookie['domain']);
					if (! preg_match("'.*$domain$'i", $this->host)) {
						$this->debug('cookie has different domain');
						continue;
					}
				}
				if ((isset($cookie['path'])) && (! empty($cookie['path']))) {
					$path = preg_quote($cookie['path']);
					if (! preg_match("'^$path.*'i", $this->path)) {
						$this->debug('cookie is for a different path');
						continue;
					}
				}
				if ((! $secure) && (isset($cookie['secure'])) && ($cookie['secure'])) {
					$this->debug('cookie is secure, transport is not');
					continue;
				}
				$cookie_str .= $cookie['name'] . '=' . $cookie['value'] . '; ';
	    		$this->debug('add cookie to Cookie-String: ' . $cookie['name'] . '=' . $cookie['value']);
			}
		}
		return $cookie_str;
  }
}
?><?php
class soap_server extends nusoap_base {
	var $headers = array();
	var $request = '';
	var $requestHeaders = '';
	var $document = '';
	var $requestSOAP = '';
	var $methodURI = '';
	var $methodname = '';
	var $methodparams = array();
	var $SOAPAction = '';
	var $xml_encoding = '';
    var $decode_utf8 = true;
	var $outgoing_headers = array();
	var $response = '';
	var $responseHeaders = '';
	var $responseSOAP = '';
	var $methodreturn = false;
	var $methodreturnisliteralxml = false;
	var $fault = false;
	var $result = 'successful';
	var $operations = array();
	var $wsdl = false;
	var $externalWSDLURL = false;
	var $debug_flag = false;
	function soap_server($wsdl=false){
		parent::nusoap_base();
		global $debug;
		global $HTTP_SERVER_VARS;
		if (isset($_SERVER)) {
			$this->debug("_SERVER is defined:");
			$this->appendDebug($this->varDump($_SERVER));
		} elseif (isset($HTTP_SERVER_VARS)) {
			$this->debug("HTTP_SERVER_VARS is defined:");
			$this->appendDebug($this->varDump($HTTP_SERVER_VARS));
		} else {
			$this->debug("Neither _SERVER nor HTTP_SERVER_VARS is defined.");
		}
		if (isset($debug)) {
			$this->debug("In soap_server, set debug_flag=$debug based on global flag");
			$this->debug_flag = $debug;
		} elseif (isset($_SERVER['QUERY_STRING'])) {
			$qs = explode('&', $_SERVER['QUERY_STRING']);
			foreach ($qs as $v) {
				if (substr($v, 0, 6) == 'debug=') {
					$this->debug("In soap_server, set debug_flag=" . substr($v, 6) . " based on query string #1");
					$this->debug_flag = substr($v, 6);
				}
			}
		} elseif (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
			$qs = explode('&', $HTTP_SERVER_VARS['QUERY_STRING']);
			foreach ($qs as $v) {
				if (substr($v, 0, 6) == 'debug=') {
					$this->debug("In soap_server, set debug_flag=" . substr($v, 6) . " based on query string #2");
					$this->debug_flag = substr($v, 6);
				}
			}
		}
		if($wsdl){
			$this->debug("In soap_server, WSDL is specified");
			if (is_object($wsdl) && (get_class($wsdl) == 'wsdl')) {
				$this->wsdl = $wsdl;
				$this->externalWSDLURL = $this->wsdl->wsdl;
				$this->debug('Use existing wsdl instance from ' . $this->externalWSDLURL);
			} else {
				$this->debug('Create wsdl from ' . $wsdl);
				$this->wsdl = new wsdl($wsdl);
				$this->externalWSDLURL = $wsdl;
			}
			$this->appendDebug($this->wsdl->getDebug());
			$this->wsdl->clearDebug();
			if($err = $this->wsdl->getError()){
				die('WSDL ERROR: '.$err);
			}
		}
	}
	function service($data){
		global $HTTP_SERVER_VARS;
		if (isset($_SERVER['QUERY_STRING'])) {
			$qs = $_SERVER['QUERY_STRING'];
		} elseif (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
			$qs = $HTTP_SERVER_VARS['QUERY_STRING'];
		} else {
			$qs = '';
		}
		$this->debug("In service, query string=$qs");
		if (ereg('wsdl', $qs) ){
			$this->debug("In service, this is a request for WSDL");
			if($this->externalWSDLURL){
              if (strpos($this->externalWSDLURL,":
				header('Location: '.$this->externalWSDLURL);
              } else { 
                header("Content-Type: text/xml\r\n");
                $fp = fopen($this->externalWSDLURL, 'r');
                fpassthru($fp);
              }
			} elseif ($this->wsdl) {
				header("Content-Type: text/xml; charset=ISO-8859-1\r\n");
				print $this->wsdl->serialize($this->debug_flag);
				if ($this->debug_flag) {
					$this->debug('wsdl:');
					$this->appendDebug($this->varDump($this->wsdl));
					print $this->getDebugAsXMLComment();
				}
			} else {
				header("Content-Type: text/html; charset=ISO-8859-1\r\n");
				print "This service does not provide WSDL";
			}
		} elseif ($data == '' && $this->wsdl) {
			$this->debug("In service, there is no data, so return Web description");
			print $this->wsdl->webDescription();
		} else {
			$this->debug("In service, invoke the request");
			$this->parse_request($data);
			if (! $this->fault) {
				$this->invoke_method();
			}
			if (! $this->fault) {
				$this->serialize_return();
			}
			$this->send_response();
		}
	}
	function parse_http_headers() {
		global $HTTP_SERVER_VARS;
		$this->request = '';
		$this->SOAPAction = '';
		if(function_exists('getallheaders')){
			$this->debug("In parse_http_headers, use getallheaders");
			$headers = getallheaders();
			foreach($headers as $k=>$v){
				$k = strtolower($k);
				$this->headers[$k] = $v;
				$this->request .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
			if(isset($this->headers['soapaction'])){
				$this->SOAPAction = str_replace('"','',$this->headers['soapaction']);
			}
			if(isset($this->headers['content-type']) && strpos($this->headers['content-type'],'=')){
				$enc = str_replace('"','',substr(strstr($this->headers["content-type"],'='),1));
				if(eregi('^(ISO-8859-1|US-ASCII|UTF-8)$',$enc)){
					$this->xml_encoding = strtoupper($enc);
				} else {
					$this->xml_encoding = 'US-ASCII';
				}
			} else {
				$this->xml_encoding = 'ISO-8859-1';
			}
		} elseif(isset($_SERVER) && is_array($_SERVER)){
			$this->debug("In parse_http_headers, use _SERVER");
			foreach ($_SERVER as $k => $v) {
				if (substr($k, 0, 5) == 'HTTP_') {
					$k = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($k, 5)))); 	                                         $k = strtolower(substr($k, 5));
				} else {
					$k = str_replace(' ', '-', strtolower(str_replace('_', ' ', $k))); 	                                         $k = strtolower($k);
				}
				if ($k == 'soapaction') {
					$k = 'SOAPAction';
					$v = str_replace('"', '', $v);
					$v = str_replace('\\', '', $v);
					$this->SOAPAction = $v;
				} else if ($k == 'content-type') {
					if (strpos($v, '=')) {
						$enc = substr(strstr($v, '='), 1);
						$enc = str_replace('"', '', $enc);
						$enc = str_replace('\\', '', $enc);
						if (eregi('^(ISO-8859-1|US-ASCII|UTF-8)$', $enc)) {
							$this->xml_encoding = strtoupper($enc);
						} else {
							$this->xml_encoding = 'US-ASCII';
						}
					} else {
						$this->xml_encoding = 'ISO-8859-1';
					}
				}
				$this->headers[$k] = $v;
				$this->request .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
		} elseif (is_array($HTTP_SERVER_VARS)) {
			$this->debug("In parse_http_headers, use HTTP_SERVER_VARS");
			foreach ($HTTP_SERVER_VARS as $k => $v) {
				if (substr($k, 0, 5) == 'HTTP_') {
					$k = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($k, 5)))); 	                                         $k = strtolower(substr($k, 5));
				} else {
					$k = str_replace(' ', '-', strtolower(str_replace('_', ' ', $k))); 	                                         $k = strtolower($k);
				}
				if ($k == 'soapaction') {
					$k = 'SOAPAction';
					$v = str_replace('"', '', $v);
					$v = str_replace('\\', '', $v);
					$this->SOAPAction = $v;
				} else if ($k == 'content-type') {
					if (strpos($v, '=')) {
						$enc = substr(strstr($v, '='), 1);
						$enc = str_replace('"', '', $enc);
						$enc = str_replace('\\', '', $enc);
						if (eregi('^(ISO-8859-1|US-ASCII|UTF-8)$', $enc)) {
							$this->xml_encoding = strtoupper($enc);
						} else {
							$this->xml_encoding = 'US-ASCII';
						}
					} else {
						$this->xml_encoding = 'ISO-8859-1';
					}
				}
				$this->headers[$k] = $v;
				$this->request .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
		} else {
			$this->debug("In parse_http_headers, HTTP headers not accessible");
			$this->setError("HTTP headers not accessible");
		}
	}
	function parse_request($data='') {
		$this->debug('entering parse_request()');
		$this->parse_http_headers();
		$this->debug('got character encoding: '.$this->xml_encoding);
		if (isset($this->headers['content-encoding']) && $this->headers['content-encoding'] != '') {
			$this->debug('got content encoding: ' . $this->headers['content-encoding']);
			if ($this->headers['content-encoding'] == 'deflate' || $this->headers['content-encoding'] == 'gzip') {
				if (function_exists('gzuncompress')) {
					if ($this->headers['content-encoding'] == 'deflate' && $degzdata = @gzuncompress($data)) {
						$data = $degzdata;
					} elseif ($this->headers['content-encoding'] == 'gzip' && $degzdata = gzinflate(substr($data, 10))) {
						$data = $degzdata;
					} else {
						$this->fault('Client', 'Errors occurred when trying to decode the data');
						return;
					}
				} else {
					$this->fault('Client', 'This Server does not support compressed data');
					return;
				}
			}
		}
		$this->request .= "\r\n".$data;
		$data = $this->parseRequest($this->headers, $data);
		$this->requestSOAP = $data;
		$this->debug('leaving parse_request');
	}
	function invoke_method() {
		$this->debug('in invoke_method, methodname=' . $this->methodname . ' methodURI=' . $this->methodURI . ' SOAPAction=' . $this->SOAPAction);
		if ($this->wsdl) {
			if ($this->opData = $this->wsdl->getOperationData($this->methodname)) {
				$this->debug('in invoke_method, found WSDL operation=' . $this->methodname);
				$this->appendDebug('opData=' . $this->varDump($this->opData));
			} elseif ($this->opData = $this->wsdl->getOperationDataForSoapAction($this->SOAPAction)) {
				$this->debug('in invoke_method, found WSDL soapAction=' . $this->SOAPAction . ' for operation=' . $this->opData['name']);
				$this->appendDebug('opData=' . $this->varDump($this->opData));
				$this->methodname = $this->opData['name'];
			} else {
				$this->debug('in invoke_method, no WSDL for operation=' . $this->methodname);
				$this->fault('Client', "Operation '" . $this->methodname . "' is not defined in the WSDL for this service");
				return;
			}
		} else {
			$this->debug('in invoke_method, no WSDL to validate method');
		}
		$class = '';
		$method = '';
		if (strpos($this->methodname, '..') > 0) {
			$delim = '..';
		} else if (strpos($this->methodname, '.') > 0) {
			$delim = '.';
		} else {
			$delim = '';
		}
		if (strlen($delim) > 0 && substr_count($this->methodname, $delim) == 1 &&
			class_exists(substr($this->methodname, 0, strpos($this->methodname, $delim)))) {
			$class = substr($this->methodname, 0, strpos($this->methodname, $delim));
			$method = substr($this->methodname, strpos($this->methodname, $delim) + strlen($delim));
			$this->debug("in invoke_method, class=$class method=$method delim=$delim");
		}
		if ($class == '') {
			if (!function_exists($this->methodname)) {
				$this->debug("in invoke_method, function '$this->methodname' not found!");
				$this->result = 'fault: method not found';
				$this->fault('Client',"method '$this->methodname' not defined in service");
				return;
			}
		} else {
			$method_to_compare = (substr(phpversion(), 0, 2) == '4.') ? strtolower($method) : $method;
			if (!in_array($method_to_compare, get_class_methods($class))) {
				$this->debug("in invoke_method, method '$this->methodname' not found in class '$class'!");
				$this->result = 'fault: method not found';
				$this->fault('Client',"method '$this->methodname' not defined in service");
				return;
			}
		}
		if(! $this->verify_method($this->methodname,$this->methodparams)){
			$this->debug('ERROR: request not verified against method signature');
			$this->result = 'fault: request failed validation against method signature';
			$this->fault('Client',"Operation '$this->methodname' not defined in service.");
			return;
		}
		$this->debug('in invoke_method, params:');
		$this->appendDebug($this->varDump($this->methodparams));
		$this->debug("in invoke_method, calling '$this->methodname'");
		if (!function_exists('call_user_func_array')) {
			if ($class == '') {
				$this->debug('in invoke_method, calling function using eval()');
				$funcCall = "\$this->methodreturn = $this->methodname(";
			} else {
				if ($delim == '..') {
					$this->debug('in invoke_method, calling class method using eval()');
					$funcCall = "\$this->methodreturn = ".$class."::".$method."(";
				} else {
					$this->debug('in invoke_method, calling instance method using eval()');
					$instname = "\$inst_".time();
					$funcCall = $instname." = new ".$class."(); ";
					$funcCall .= "\$this->methodreturn = ".$instname."->".$method."(";
				}
			}
			if ($this->methodparams) {
				foreach ($this->methodparams as $param) {
					if (is_array($param)) {
						$this->fault('Client', 'NuSOAP does not handle complexType parameters correctly when using eval; call_user_func_array must be available');
						return;
					}
					$funcCall .= "\"$param\",";
				}
				$funcCall = substr($funcCall, 0, -1);
			}
			$funcCall .= ');';
			$this->debug('in invoke_method, function call: '.$funcCall);
			@eval($funcCall);
		} else {
			if ($class == '') {
				$this->debug('in invoke_method, calling function using call_user_func_array()');
				$call_arg = "$this->methodname";	
			} elseif ($delim == '..') {
				$this->debug('in invoke_method, calling class method using call_user_func_array()');
				$call_arg = array ($class, $method);
			} else {
				$this->debug('in invoke_method, calling instance method using call_user_func_array()');
				$instance = new $class ();
				$call_arg = array(&$instance, $method);
			}
			$this->methodreturn = call_user_func_array($call_arg, $this->methodparams);
		}
        $this->debug('in invoke_method, methodreturn:');
        $this->appendDebug($this->varDump($this->methodreturn));
		$this->debug("in invoke_method, called method $this->methodname, received $this->methodreturn of type ".gettype($this->methodreturn));
	}
	function serialize_return() {
		$this->debug('Entering serialize_return methodname: ' . $this->methodname . ' methodURI: ' . $this->methodURI);
		if (isset($this->methodreturn) && (get_class($this->methodreturn) == 'soap_fault')) {
			$this->debug('got a fault object from method');
			$this->fault = $this->methodreturn;
			return;
		} elseif ($this->methodreturnisliteralxml) {
			$return_val = $this->methodreturn;
		} else {
			$this->debug('got a(n) '.gettype($this->methodreturn).' from method');
			$this->debug('serializing return value');
			if($this->wsdl){
				if(sizeof($this->opData['output']['parts']) > 1){
			    	$opParams = $this->methodreturn;
			    } else {
			    	$opParams = array($this->methodreturn);
			    }
			    $return_val = $this->wsdl->serializeRPCParameters($this->methodname,'output',$opParams);
			    $this->appendDebug($this->wsdl->getDebug());
			    $this->wsdl->clearDebug();
				if($errstr = $this->wsdl->getError()){
					$this->debug('got wsdl error: '.$errstr);
					$this->fault('Server', 'unable to serialize result');
					return;
				}
			} else {
				if (isset($this->methodreturn)) {
					$return_val = $this->serialize_val($this->methodreturn, 'return');
				} else {
					$return_val = '';
					$this->debug('in absence of WSDL, assume void return for backward compatibility');
				}
			}
		}
		$this->debug('return value:');
		$this->appendDebug($this->varDump($return_val));
		$this->debug('serializing response');
		if ($this->wsdl) {
			$this->debug('have WSDL for serialization: style is ' . $this->opData['style']);
			if ($this->opData['style'] == 'rpc') {
				$this->debug('style is rpc for serialization: use is ' . $this->opData['output']['use']);
				if ($this->opData['output']['use'] == 'literal') {
					$payload = '<'.$this->methodname.'Response xmlns="'.$this->methodURI.'">'.$return_val.'</'.$this->methodname."Response>";
				} else {
					$payload = '<ns1:'.$this->methodname.'Response xmlns:ns1="'.$this->methodURI.'">'.$return_val.'</ns1:'.$this->methodname."Response>";
				}
			} else {
				$this->debug('style is not rpc for serialization: assume document');
				$payload = $return_val;
			}
		} else {
			$this->debug('do not have WSDL for serialization: assume rpc/encoded');
			$payload = '<ns1:'.$this->methodname.'Response xmlns:ns1="'.$this->methodURI.'">'.$return_val.'</ns1:'.$this->methodname."Response>";
		}
		$this->result = 'successful';
		if($this->wsdl){
            	$this->appendDebug($this->wsdl->getDebug());
			if (isset($opData['output']['encodingStyle'])) {
				$encodingStyle = $opData['output']['encodingStyle'];
			} else {
				$encodingStyle = '';
			}
			$this->responseSOAP = $this->serializeEnvelope($payload,$this->responseHeaders,$this->wsdl->usedNamespaces,$this->opData['style'],$encodingStyle);
		} else {
			$this->responseSOAP = $this->serializeEnvelope($payload,$this->responseHeaders);
		}
		$this->debug("Leaving serialize_return");
	}
	function send_response() {
		$this->debug('Enter send_response');
		if ($this->fault) {
			$payload = $this->fault->serialize();
			$this->outgoing_headers[] = "HTTP/1.0 500 Internal Server Error";
			$this->outgoing_headers[] = "Status: 500 Internal Server Error";
		} else {
			$payload = $this->responseSOAP;
		}
		if(isset($this->debug_flag) && $this->debug_flag){
        	$payload .= $this->getDebugAsXMLComment();
        }
		$this->outgoing_headers[] = "Server: $this->title Server v$this->version";
		ereg('\$Revisio' . 'n: ([^ ]+)', $this->revision, $rev);
		$this->outgoing_headers[] = "X-SOAP-Server: $this->title/$this->version (".$rev[1].")";
		$payload = $this->getHTTPBody($payload);
		$type = $this->getHTTPContentType();
		$charset = $this->getHTTPContentTypeCharset();
		$this->outgoing_headers[] = "Content-Type: $type" . ($charset ? '; charset=' . $charset : '');
		if (strlen($payload) > 1024 && isset($this->headers) && isset($this->headers['accept-encoding'])) {	
			if (strstr($this->headers['accept-encoding'], 'gzip')) {
				if (function_exists('gzencode')) {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= "<!-- Content being gzipped -->";
					}
					$this->outgoing_headers[] = "Content-Encoding: gzip";
					$payload = gzencode($payload);
				} else {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= "<!-- Content will not be gzipped: no gzencode -->";
					}
				}
			} elseif (strstr($this->headers['accept-encoding'], 'deflate')) {
				if (function_exists('gzdeflate')) {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= "<!-- Content being deflated -->";
					}
					$this->outgoing_headers[] = "Content-Encoding: deflate";
					$payload = gzdeflate($payload);
				} else {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= "<!-- Content will not be deflated: no gzcompress -->";
					}
				}
			}
		}
		$this->outgoing_headers[] = "Content-Length: ".strlen($payload);
		reset($this->outgoing_headers);
		foreach($this->outgoing_headers as $hdr){
			header($hdr, false);
		}
		print $payload;
		$this->response = join("\r\n",$this->outgoing_headers)."\r\n\r\n".$payload;
	}
	function verify_method($operation,$request){
		if(isset($this->wsdl) && is_object($this->wsdl)){
			if($this->wsdl->getOperationData($operation)){
				return true;
			}
	    } elseif(isset($this->operations[$operation])){
			return true;
		}
		return false;
	}
    function parseRequest($headers, $data) {
		$this->debug('Entering parseRequest() for data of length ' . strlen($data) . ' and type ' . $headers['content-type']);
		if (!strstr($headers['content-type'], 'text/xml')) {
			$this->setError('Request not of type text/xml');
			return false;
		}
		if (strpos($headers['content-type'], '=')) {
			$enc = str_replace('"', '', substr(strstr($headers["content-type"], '='), 1));
			$this->debug('Got response encoding: ' . $enc);
			if(eregi('^(ISO-8859-1|US-ASCII|UTF-8)$',$enc)){
				$this->xml_encoding = strtoupper($enc);
			} else {
				$this->xml_encoding = 'US-ASCII';
			}
		} else {
			$this->xml_encoding = 'ISO-8859-1';
		}
		$this->debug('Use encoding: ' . $this->xml_encoding . ' when creating soap_parser');
		$parser = new soap_parser($data,$this->xml_encoding,'',$this->decode_utf8);
		$this->debug("parser debug: \n".$parser->getDebug());
		if($err = $parser->getError()){
			$this->result = 'fault: error in msg parsing: '.$err;
			$this->fault('Client',"error in msg parsing:\n".$err);
		} else {
			$this->methodURI = $parser->root_struct_namespace;
			$this->methodname = $parser->root_struct_name;
			$this->debug('methodname: '.$this->methodname.' methodURI: '.$this->methodURI);
			$this->debug('calling parser->get_response()');
			$this->methodparams = $parser->get_response();
			$this->requestHeaders = $parser->getHeaders();
            $this->document = $parser->document;
		}
	 }
	function getHTTPBody($soapmsg) {
		return $soapmsg;
	}
	function getHTTPContentType() {
		return 'text/xml';
	}
	function getHTTPContentTypeCharset() {
		return $this->soap_defencoding;
	}
	function add_to_map($methodname,$in,$out){
			$this->operations[$methodname] = array('name' => $methodname,'in' => $in,'out' => $out);
	}
	function register($name,$in=array(),$out=array(),$namespace=false,$soapaction=false,$style=false,$use=false,$documentation='',$encodingStyle=''){
		global $HTTP_SERVER_VARS;
		if($this->externalWSDLURL){
			die('You cannot bind to an external WSDL file, and register methods outside of it! Please choose either WSDL or no WSDL.');
		}
		if (! $name) {
			die('You must specify a name when you register an operation');
		}
		if (!is_array($in)) {
			die('You must provide an array for operation inputs');
		}
		if (!is_array($out)) {
			die('You must provide an array for operation outputs');
		}
		if(false == $namespace) {
		}
		if(false == $soapaction) {
			if (isset($_SERVER)) {
				$SERVER_NAME = $_SERVER['SERVER_NAME'];
				$SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
			} elseif (isset($HTTP_SERVER_VARS)) {
				$SERVER_NAME = $HTTP_SERVER_VARS['SERVER_NAME'];
				$SCRIPT_NAME = isset($HTTP_SERVER_VARS['PHP_SELF']) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_SERVER_VARS['SCRIPT_NAME'];
			} else {
				$this->setError("Neither _SERVER nor HTTP_SERVER_VARS is available");
			}
			$soapaction = "http:
		}
		if(false == $style) {
			$style = "rpc";
		}
		if(false == $use) {
			$use = "encoded";
		}
		if ($use == 'encoded' && $encodingStyle = '') {
			$encodingStyle = 'http:
		}
		$this->operations[$name] = array(
	    'name' => $name,
	    'in' => $in,
	    'out' => $out,
	    'namespace' => $namespace,
	    'soapaction' => $soapaction,
	    'style' => $style);
        if($this->wsdl){
        	$this->wsdl->addOperation($name,$in,$out,$namespace,$soapaction,$style,$use,$documentation,$encodingStyle);
	    }
		return true;
	}
	function fault($faultcode,$faultstring,$faultactor='',$faultdetail=''){
		if ($faultdetail == '' && $this->debug_flag) {
			$faultdetail = $this->getDebug();
		}
		$this->fault = new soap_fault($faultcode,$faultactor,$faultstring,$faultdetail);
		$this->fault->soap_defencoding = $this->soap_defencoding;
	}
    function configureWSDL($serviceName,$namespace = false,$endpoint = false,$style='rpc', $transport = 'http:
    {
    	global $HTTP_SERVER_VARS;
		if (isset($_SERVER)) {
			$SERVER_NAME = $_SERVER['SERVER_NAME'];
			$SERVER_PORT = $_SERVER['SERVER_PORT'];
			$SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
			$HTTPS = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : false;
		} elseif (isset($HTTP_SERVER_VARS)) {
			$SERVER_NAME = $HTTP_SERVER_VARS['SERVER_NAME'];
			$SERVER_PORT = $HTTP_SERVER_VARS['SERVER_PORT'];
			$SCRIPT_NAME = isset($HTTP_SERVER_VARS['PHP_SELF']) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_SERVER_VARS['SCRIPT_NAME'];
			$HTTPS = $HTTP_SERVER_VARS['HTTPS'];
		} else {
			$this->setError("Neither _SERVER nor HTTP_SERVER_VARS is available");
		}
		if ($SERVER_PORT == 80) {
			$SERVER_PORT = '';
		} else {
			$SERVER_PORT = ':' . $SERVER_PORT;
		}
        if(false == $namespace) {
            $namespace = "http:
        }
        if(false == $endpoint) {
        	if ($HTTPS == '1' || $HTTPS == 'on') {
        		$SCHEME = 'https';
        	} else {
        		$SCHEME = 'http';
        	}
            $endpoint = "$SCHEME:
        }
        if(false == $schemaTargetNamespace) {
            $schemaTargetNamespace = $namespace;
        }
		$this->wsdl = new wsdl;
		$this->wsdl->serviceName = $serviceName;
        $this->wsdl->endpoint = $endpoint;
		$this->wsdl->namespaces['tns'] = $namespace;
		$this->wsdl->namespaces['soap'] = 'http:
		$this->wsdl->namespaces['wsdl'] = 'http:
		if ($schemaTargetNamespace != $namespace) {
			$this->wsdl->namespaces['types'] = $schemaTargetNamespace;
		}
        $this->wsdl->schemas[$schemaTargetNamespace][0] = new xmlschema('', '', $this->wsdl->namespaces);
        $this->wsdl->schemas[$schemaTargetNamespace][0]->schemaTargetNamespace = $schemaTargetNamespace;
        $this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http:
        $this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http:
        $this->wsdl->bindings[$serviceName.'Binding'] = array(
        	'name'=>$serviceName.'Binding',
            'style'=>$style,
            'transport'=>$transport,
            'portType'=>$serviceName.'PortType');
        $this->wsdl->ports[$serviceName.'Port'] = array(
        	'binding'=>$serviceName.'Binding',
            'location'=>$endpoint,
            'bindingType'=>'http:
    }
}
?><?php
class wsdl extends nusoap_base {
    var $wsdl; 
    var $schemas = array();
    var $currentSchema;
    var $message = array();
    var $complexTypes = array();
    var $messages = array();
    var $currentMessage;
    var $currentOperation;
    var $portTypes = array();
    var $currentPortType;
    var $bindings = array();
    var $currentBinding;
    var $ports = array();
    var $currentPort;
    var $opData = array();
    var $status = '';
    var $documentation = false;
    var $endpoint = ''; 
    var $import = array(); 
    var $parser;
    var $position = 0;
    var $depth = 0;
    var $depth_array = array();
	var $proxyhost = '';
    var $proxyport = '';
	var $proxyusername = '';
	var $proxypassword = '';
	var $timeout = 0;
	var $response_timeout = 30;
    function wsdl($wsdl = '',$proxyhost=false,$proxyport=false,$proxyusername=false,$proxypassword=false,$timeout=0,$response_timeout=30){
		parent::nusoap_base();
        $this->wsdl = $wsdl;
        $this->proxyhost = $proxyhost;
        $this->proxyport = $proxyport;
		$this->proxyusername = $proxyusername;
		$this->proxypassword = $proxypassword;
		$this->timeout = $timeout;
		$this->response_timeout = $response_timeout;
        if ($wsdl != "") {
            $this->debug('initial wsdl URL: ' . $wsdl);
            $this->parseWSDL($wsdl);
        }
        	$imported_urls = array();
        	$imported = 1;
        	while ($imported > 0) {
        		$imported = 0;
        		foreach ($this->schemas as $ns => $list) {
        			foreach ($list as $xs) {
						$wsdlparts = parse_url($this->wsdl);	
			            foreach ($xs->imports as $ns2 => $list2) {
			                for ($ii = 0; $ii < count($list2); $ii++) {
			                	if (! $list2[$ii]['loaded']) {
			                		$this->schemas[$ns]->imports[$ns2][$ii]['loaded'] = true;
			                		$url = $list2[$ii]['location'];
									if ($url != '') {
										$urlparts = parse_url($url);
										if (!isset($urlparts['host'])) {
											$url = $wsdlparts['scheme'] . ':
													substr($wsdlparts['path'],0,strrpos($wsdlparts['path'],'/') + 1) .$urlparts['path'];
										}
										if (! in_array($url, $imported_urls)) {
						                	$this->parseWSDL($url);
					                		$imported++;
					                		$imported_urls[] = $url;
					                	}
									} else {
										$this->debug("Unexpected scenario: empty URL for unloaded import");
									}
								}
							}
			            } 
        			}
        		}
				$wsdlparts = parse_url($this->wsdl);	
	            foreach ($this->import as $ns => $list) {
	                for ($ii = 0; $ii < count($list); $ii++) {
	                	if (! $list[$ii]['loaded']) {
	                		$this->import[$ns][$ii]['loaded'] = true;
	                		$url = $list[$ii]['location'];
							if ($url != '') {
								$urlparts = parse_url($url);
								if (!isset($urlparts['host'])) {
									$url = $wsdlparts['scheme'] . ':
											substr($wsdlparts['path'],0,strrpos($wsdlparts['path'],'/') + 1) .$urlparts['path'];
								}
								if (! in_array($url, $imported_urls)) {
				                	$this->parseWSDL($url);
			                		$imported++;
			                		$imported_urls[] = $url;
			                	}
							} else {
								$this->debug("Unexpected scenario: empty URL for unloaded import");
							}
						}
					}
	            } 
			}
        foreach($this->bindings as $binding => $bindingData) {
            if (isset($bindingData['operations']) && is_array($bindingData['operations'])) {
                foreach($bindingData['operations'] as $operation => $data) {
                    $this->debug('post-parse data gathering for ' . $operation);
                    $this->bindings[$binding]['operations'][$operation]['input'] = 
						isset($this->bindings[$binding]['operations'][$operation]['input']) ? 
						array_merge($this->bindings[$binding]['operations'][$operation]['input'], $this->portTypes[ $bindingData['portType'] ][$operation]['input']) :
						$this->portTypes[ $bindingData['portType'] ][$operation]['input'];
                    $this->bindings[$binding]['operations'][$operation]['output'] = 
						isset($this->bindings[$binding]['operations'][$operation]['output']) ?
						array_merge($this->bindings[$binding]['operations'][$operation]['output'], $this->portTypes[ $bindingData['portType'] ][$operation]['output']) :
						$this->portTypes[ $bindingData['portType'] ][$operation]['output'];
                    if(isset($this->messages[ $this->bindings[$binding]['operations'][$operation]['input']['message'] ])){
						$this->bindings[$binding]['operations'][$operation]['input']['parts'] = $this->messages[ $this->bindings[$binding]['operations'][$operation]['input']['message'] ];
					}
					if(isset($this->messages[ $this->bindings[$binding]['operations'][$operation]['output']['message'] ])){
                   		$this->bindings[$binding]['operations'][$operation]['output']['parts'] = $this->messages[ $this->bindings[$binding]['operations'][$operation]['output']['message'] ];
                    }
					if (isset($bindingData['style'])) {
                        $this->bindings[$binding]['operations'][$operation]['style'] = $bindingData['style'];
                    }
                    $this->bindings[$binding]['operations'][$operation]['transport'] = isset($bindingData['transport']) ? $bindingData['transport'] : '';
                    $this->bindings[$binding]['operations'][$operation]['documentation'] = isset($this->portTypes[ $bindingData['portType'] ][$operation]['documentation']) ? $this->portTypes[ $bindingData['portType'] ][$operation]['documentation'] : '';
                    $this->bindings[$binding]['operations'][$operation]['endpoint'] = isset($bindingData['endpoint']) ? $bindingData['endpoint'] : '';
                } 
            } 
        }
    }
    function parseWSDL($wsdl = '')
    {
        if ($wsdl == '') {
            $this->debug('no wsdl passed to parseWSDL()!!');
            $this->setError('no wsdl passed to parseWSDL()!!');
            return false;
        }
        $wsdl_props = parse_url($wsdl);
        if (isset($wsdl_props['scheme']) && ($wsdl_props['scheme'] == 'http' || $wsdl_props['scheme'] == 'https')) {
            $this->debug('getting WSDL http(s) URL ' . $wsdl);
	        $tr = new soap_transport_http($wsdl);
			$tr->request_method = 'GET';
			$tr->useSOAPAction = false;
			if($this->proxyhost && $this->proxyport){
				$tr->setProxy($this->proxyhost,$this->proxyport,$this->proxyusername,$this->proxypassword);
			}
			$tr->setEncoding('gzip, deflate');
			$wsdl_string = $tr->send('', $this->timeout, $this->response_timeout);
			$this->appendDebug($tr->getDebug());
			if($err = $tr->getError() ){
				$errstr = 'HTTP ERROR: '.$err;
				$this->debug($errstr);
	            $this->setError($errstr);
				unset($tr);
	            return false;
			}
			unset($tr);
			$this->debug("got WSDL URL");
        } else {
        	if (isset($wsdl_props['scheme']) && ($wsdl_props['scheme'] == 'file') && isset($wsdl_props['path'])) {
        		$path = isset($wsdl_props['host']) ? ($wsdl_props['host'] . ':' . $wsdl_props['path']) : $wsdl_props['path'];
        	} else {
        		$path = $wsdl;
        	}
            $this->debug('getting WSDL file ' . $path);
            if ($fp = @fopen($path, 'r')) {
                $wsdl_string = '';
                while ($data = fread($fp, 32768)) {
                    $wsdl_string .= $data;
                } 
                fclose($fp);
            } else {
            	$errstr = "Bad path to WSDL file $path";
            	$this->debug($errstr);
                $this->setError($errstr);
                return false;
            } 
        }
        $this->debug('Parse WSDL');
        $this->parser = xml_parser_create(); 
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0); 
        xml_set_object($this->parser, $this); 
        xml_set_element_handler($this->parser, 'start_element', 'end_element');
        xml_set_character_data_handler($this->parser, 'character_data');
        if (!xml_parse($this->parser, $wsdl_string, true)) {
            $errstr = sprintf(
				'XML error parsing WSDL from %s on line %d: %s',
				$wsdl,
                xml_get_current_line_number($this->parser),
                xml_error_string(xml_get_error_code($this->parser))
                );
            $this->debug($errstr);
			$this->debug("XML payload:\n" . $wsdl_string);
            $this->setError($errstr);
            return false;
        } 
        xml_parser_free($this->parser);
        $this->debug('Parsing WSDL done');
		if($this->getError()){
			return false;
		}
        return true;
    } 
    function start_element($parser, $name, $attrs)
    {
        if ($this->status == 'schema') {
            $this->currentSchema->schemaStartElement($parser, $name, $attrs);
            $this->appendDebug($this->currentSchema->getDebug());
            $this->currentSchema->clearDebug();
        } elseif (ereg('schema$', $name)) {
        	$this->debug('Parsing WSDL schema');
            $this->status = 'schema';
            $this->currentSchema = new xmlschema('', '', $this->namespaces);
            $this->currentSchema->schemaStartElement($parser, $name, $attrs);
            $this->appendDebug($this->currentSchema->getDebug());
            $this->currentSchema->clearDebug();
        } else {
            $pos = $this->position++;
            $depth = $this->depth++; 
            $this->depth_array[$depth] = $pos;
            $this->message[$pos] = array('cdata' => ''); 
            if (count($attrs) > 0) {
                foreach($attrs as $k => $v) {
                    if (ereg("^xmlns", $k)) {
                        if ($ns_prefix = substr(strrchr($k, ':'), 1)) {
                            $this->namespaces[$ns_prefix] = $v;
                        } else {
                            $this->namespaces['ns' . (count($this->namespaces) + 1)] = $v;
                        } 
                        if ($v == 'http:
                            $this->XMLSchemaVersion = $v;
                            $this->namespaces['xsi'] = $v . '-instance';
                        } 
                    }
                }
                foreach($attrs as $k => $v) {
                    $k = strpos($k, ':') ? $this->expandQname($k) : $k;
                    if ($k != 'location' && $k != 'soapAction' && $k != 'namespace') {
                        $v = strpos($v, ':') ? $this->expandQname($v) : $v;
                    } 
                    $eAttrs[$k] = $v;
                } 
                $attrs = $eAttrs;
            } else {
                $attrs = array();
            } 
            if (ereg(':', $name)) {
                $prefix = substr($name, 0, strpos($name, ':')); 
                $namespace = isset($this->namespaces[$prefix]) ? $this->namespaces[$prefix] : ''; 
                $name = substr(strstr($name, ':'), 1);
            } 
            switch ($this->status) {
                case 'message':
                    if ($name == 'part') {
			            if (isset($attrs['type'])) {
		                    $this->debug("msg " . $this->currentMessage . ": found part $attrs[name]: " . implode(',', $attrs));
		                    $this->messages[$this->currentMessage][$attrs['name']] = $attrs['type'];
            			} 
			            if (isset($attrs['element'])) {
		                    $this->debug("msg " . $this->currentMessage . ": found part $attrs[name]: " . implode(',', $attrs));
			                $this->messages[$this->currentMessage][$attrs['name']] = $attrs['element'];
			            } 
        			} 
        			break;
			    case 'portType':
			        switch ($name) {
			            case 'operation':
			                $this->currentPortOperation = $attrs['name'];
			                $this->debug("portType $this->currentPortType operation: $this->currentPortOperation");
			                if (isset($attrs['parameterOrder'])) {
			                	$this->portTypes[$this->currentPortType][$attrs['name']]['parameterOrder'] = $attrs['parameterOrder'];
			        		} 
			        		break;
					    case 'documentation':
					        $this->documentation = true;
					        break; 
					    default:
					        $m = isset($attrs['message']) ? $this->getLocalPart($attrs['message']) : '';
					        $this->portTypes[$this->currentPortType][$this->currentPortOperation][$name]['message'] = $m;
					        break;
					} 
			    	break;
				case 'binding':
				    switch ($name) {
				        case 'binding': 
				            if (isset($attrs['style'])) {
				            $this->bindings[$this->currentBinding]['prefix'] = $prefix;
					    	} 
					    	$this->bindings[$this->currentBinding] = array_merge($this->bindings[$this->currentBinding], $attrs);
					    	break;
						case 'header':
						    $this->bindings[$this->currentBinding]['operations'][$this->currentOperation][$this->opStatus]['headers'][] = $attrs;
						    break;
						case 'operation':
						    if (isset($attrs['soapAction'])) {
						        $this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['soapAction'] = $attrs['soapAction'];
						    } 
						    if (isset($attrs['style'])) {
						        $this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['style'] = $attrs['style'];
						    } 
						    if (isset($attrs['name'])) {
						        $this->currentOperation = $attrs['name'];
						        $this->debug("current binding operation: $this->currentOperation");
						        $this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['name'] = $attrs['name'];
						        $this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['binding'] = $this->currentBinding;
						        $this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['endpoint'] = isset($this->bindings[$this->currentBinding]['endpoint']) ? $this->bindings[$this->currentBinding]['endpoint'] : '';
						    } 
						    break;
						case 'input':
						    $this->opStatus = 'input';
						    break;
						case 'output':
						    $this->opStatus = 'output';
						    break;
						case 'body':
						    if (isset($this->bindings[$this->currentBinding]['operations'][$this->currentOperation][$this->opStatus])) {
						        $this->bindings[$this->currentBinding]['operations'][$this->currentOperation][$this->opStatus] = array_merge($this->bindings[$this->currentBinding]['operations'][$this->currentOperation][$this->opStatus], $attrs);
						    } else {
						        $this->bindings[$this->currentBinding]['operations'][$this->currentOperation][$this->opStatus] = $attrs;
						    } 
						    break;
					} 
					break;
				case 'service':
					switch ($name) {
					    case 'port':
					        $this->currentPort = $attrs['name'];
					        $this->debug('current port: ' . $this->currentPort);
					        $this->ports[$this->currentPort]['binding'] = $this->getLocalPart($attrs['binding']);
					        break;
					    case 'address':
					        $this->ports[$this->currentPort]['location'] = $attrs['location'];
					        $this->ports[$this->currentPort]['bindingType'] = $namespace;
					        $this->bindings[ $this->ports[$this->currentPort]['binding'] ]['bindingType'] = $namespace;
					        $this->bindings[ $this->ports[$this->currentPort]['binding'] ]['endpoint'] = $attrs['location'];
					        break;
					} 
					break;
			} 
		switch ($name) {
			case 'import':
			    if (isset($attrs['location'])) {
                    $this->import[$attrs['namespace']][] = array('location' => $attrs['location'], 'loaded' => false);
                    $this->debug('parsing import ' . $attrs['namespace']. ' - ' . $attrs['location'] . ' (' . count($this->import[$attrs['namespace']]).')');
				} else {
                    $this->import[$attrs['namespace']][] = array('location' => '', 'loaded' => true);
					if (! $this->getPrefixFromNamespace($attrs['namespace'])) {
						$this->namespaces['ns'.(count($this->namespaces)+1)] = $attrs['namespace'];
					}
                    $this->debug('parsing import ' . $attrs['namespace']. ' - [no location] (' . count($this->import[$attrs['namespace']]).')');
				}
				break;
			case 'message':
				$this->status = 'message';
				$this->messages[$attrs['name']] = array();
				$this->currentMessage = $attrs['name'];
				break;
			case 'portType':
				$this->status = 'portType';
				$this->portTypes[$attrs['name']] = array();
				$this->currentPortType = $attrs['name'];
				break;
			case "binding":
				if (isset($attrs['name'])) {
					if (strpos($attrs['name'], ':')) {
			    		$this->currentBinding = $this->getLocalPart($attrs['name']);
					} else {
			    		$this->currentBinding = $attrs['name'];
					} 
					$this->status = 'binding';
					$this->bindings[$this->currentBinding]['portType'] = $this->getLocalPart($attrs['type']);
					$this->debug("current binding: $this->currentBinding of portType: " . $attrs['type']);
				} 
				break;
			case 'service':
				$this->serviceName = $attrs['name'];
				$this->status = 'service';
				$this->debug('current service: ' . $this->serviceName);
				break;
			case 'definitions':
				foreach ($attrs as $name => $value) {
					$this->wsdl_info[$name] = $value;
				} 
				break;
			} 
		} 
	} 
	function end_element($parser, $name){ 
		if ( ereg('schema$', $name)) {
			$this->status = "";
            $this->appendDebug($this->currentSchema->getDebug());
            $this->currentSchema->clearDebug();
			$this->schemas[$this->currentSchema->schemaTargetNamespace][] = $this->currentSchema;
        	$this->debug('Parsing WSDL schema done');
		} 
		if ($this->status == 'schema') {
			$this->currentSchema->schemaEndElement($parser, $name);
		} else {
			$this->depth--;
		} 
		if ($this->documentation) {
			$this->documentation = false;
		} 
	} 
	function character_data($parser, $data)
	{
		$pos = isset($this->depth_array[$this->depth]) ? $this->depth_array[$this->depth] : 0;
		if (isset($this->message[$pos]['cdata'])) {
			$this->message[$pos]['cdata'] .= $data;
		} 
		if ($this->documentation) {
			$this->documentation .= $data;
		} 
	} 
	function getBindingData($binding)
	{
		if (is_array($this->bindings[$binding])) {
			return $this->bindings[$binding];
		} 
	}
	function getOperations($bindingType = 'soap')
	{
		$ops = array();
		if ($bindingType == 'soap') {
			$bindingType = 'http:
		}
		foreach($this->ports as $port => $portData) {
			if ($portData['bindingType'] == $bindingType) {
				if (isset($this->bindings[ $portData['binding'] ]['operations'])) {
					$ops = array_merge ($ops, $this->bindings[ $portData['binding'] ]['operations']);
				}
			}
		} 
		return $ops;
	} 
	function getOperationData($operation, $bindingType = 'soap')
	{
		if ($bindingType == 'soap') {
			$bindingType = 'http:
		}
		foreach($this->ports as $port => $portData) {
			if ($portData['bindingType'] == $bindingType) {
				foreach(array_keys($this->bindings[ $portData['binding'] ]['operations']) as $bOperation) {
					if ($operation == $bOperation) {
						$opData = $this->bindings[ $portData['binding'] ]['operations'][$operation];
					    return $opData;
					} 
				} 
			}
		} 
	}
	function getOperationDataForSoapAction($soapAction, $bindingType = 'soap') {
		if ($bindingType == 'soap') {
			$bindingType = 'http:
		}
		foreach($this->ports as $port => $portData) {
			if ($portData['bindingType'] == $bindingType) {
				foreach ($this->bindings[ $portData['binding'] ]['operations'] as $bOperation => $opData) {
					if ($opData['soapAction'] == $soapAction) {
					    return $opData;
					} 
				} 
			}
		} 
	}
	function getTypeDef($type, $ns) {
		$this->debug("in getTypeDef: type=$type, ns=$ns");
		if ((! $ns) && isset($this->namespaces['tns'])) {
			$ns = $this->namespaces['tns'];
			$this->debug("in getTypeDef: type namespace forced to $ns");
		}
		if (isset($this->schemas[$ns])) {
			$this->debug("in getTypeDef: have schema for namespace $ns");
			for ($i = 0; $i < count($this->schemas[$ns]); $i++) {
				$xs = &$this->schemas[$ns][$i];
				$t = $xs->getTypeDef($type);
				$this->appendDebug($xs->getDebug());
				$xs->clearDebug();
				if ($t) {
					if (!isset($t['phpType'])) {
						$uqType = substr($t['type'], strrpos($t['type'], ':') + 1);
						$ns = substr($t['type'], 0, strrpos($t['type'], ':'));
						$etype = $this->getTypeDef($uqType, $ns);
						if ($etype) {
							$this->debug("found type for [element] $type:");
							$this->debug($this->varDump($etype));
							if (isset($etype['phpType'])) {
								$t['phpType'] = $etype['phpType'];
							}
							if (isset($etype['elements'])) {
								$t['elements'] = $etype['elements'];
							}
							if (isset($etype['attrs'])) {
								$t['attrs'] = $etype['attrs'];
							}
						}
					}
					return $t;
				}
			}
		} else {
			$this->debug("in getTypeDef: do not have schema for namespace $ns");
		}
		return false;
	}
    function webDescription(){
    	global $HTTP_SERVER_VARS;
		if (isset($_SERVER)) {
			$PHP_SELF = $_SERVER['PHP_SELF'];
		} elseif (isset($HTTP_SERVER_VARS)) {
			$PHP_SELF = $HTTP_SERVER_VARS['PHP_SELF'];
		} else {
			$this->setError("Neither _SERVER nor HTTP_SERVER_VARS is available");
		}
		$b = '
		<html><head><title>NuSOAP: '.$this->serviceName.'</title>
		<style type="text/css">
		    body    { font-family: arial; color: #000000; background-color: #ffffff; margin: 0px 0px 0px 0px; }
		    p       { font-family: arial; color: #000000; margin-top: 0px; margin-bottom: 12px; }
		    pre { background-color: silver; padding: 5px; font-family: Courier New; font-size: x-small; color: #000000;}
		    ul      { margin-top: 10px; margin-left: 20px; }
		    li      { list-style-type: none; margin-top: 10px; color: #000000; }
		    .content{
			margin-left: 0px; padding-bottom: 2em; }
		    .nav {
			padding-top: 10px; padding-bottom: 10px; padding-left: 15px; font-size: .70em;
			margin-top: 10px; margin-left: 0px; color: #000000;
			background-color: #ccccff; width: 20%; margin-left: 20px; margin-top: 20px; }
		    .title {
			font-family: arial; font-size: 26px; color: #ffffff;
			background-color: #999999; width: 105%; margin-left: 0px;
			padding-top: 10px; padding-bottom: 10px; padding-left: 15px;}
		    .hidden {
			position: absolute; visibility: hidden; z-index: 200; left: 250px; top: 100px;
			font-family: arial; overflow: hidden; width: 600;
			padding: 20px; font-size: 10px; background-color: #999999;
			layer-background-color:#FFFFFF; }
		    a,a:active  { color: charcoal; font-weight: bold; }
		    a:visited   { color: #666666; font-weight: bold; }
		    a:hover     { color: cc3300; font-weight: bold; }
		</style>
		<script language="JavaScript" type="text/javascript">
		<!--
		function lib_bwcheck(){ 
		    this.ver=navigator.appVersion
		    this.agent=navigator.userAgent
		    this.dom=document.getElementById?1:0
		    this.opera5=this.agent.indexOf("Opera 5")>-1
		    this.ie5=(this.ver.indexOf("MSIE 5")>-1 && this.dom && !this.opera5)?1:0;
		    this.ie6=(this.ver.indexOf("MSIE 6")>-1 && this.dom && !this.opera5)?1:0;
		    this.ie4=(document.all && !this.dom && !this.opera5)?1:0;
		    this.ie=this.ie4||this.ie5||this.ie6
		    this.mac=this.agent.indexOf("Mac")>-1
		    this.ns6=(this.dom && parseInt(this.ver) >= 5) ?1:0;
		    this.ns4=(document.layers && !this.dom)?1:0;
		    this.bw=(this.ie6 || this.ie5 || this.ie4 || this.ns4 || this.ns6 || this.opera5)
		    return this
		}
		var bw = new lib_bwcheck()
		function makeObj(obj){
		    this.evnt=bw.dom? document.getElementById(obj):bw.ie4?document.all[obj]:bw.ns4?document.layers[obj]:0;
		    if(!this.evnt) return false
		    this.css=bw.dom||bw.ie4?this.evnt.style:bw.ns4?this.evnt:0;
		    this.wref=bw.dom||bw.ie4?this.evnt:bw.ns4?this.css.document:0;
		    this.writeIt=b_writeIt;
		    return this
		}
		function b_writeIt(text){
		    if (bw.ns4){this.wref.write(text);this.wref.close()}
		    else this.wref.innerHTML = text
		}
		var oDesc;
		function popup(divid){
		    if(oDesc = new makeObj(divid)){
			oDesc.css.visibility = "visible"
		    }
		}
		function popout(){ 
		    if(oDesc) oDesc.css.visibility = "hidden"
		}
		</script>
		</head>
		<body>
		<div class=content>
			<br><br>
			<div class=title>'.$this->serviceName.'</div>
			<div class=nav>
				<p>View the <a href="'.$PHP_SELF.'?wsdl">WSDL</a> for the service.
				Click on an operation name to view it&apos;s details.</p>
				<ul>';
				foreach($this->getOperations() as $op => $data){
				    $b .= "<li><a href='#' onclick=\"popout();popup('$op')\">$op</a></li>";
				    $b .= "<div id='$op' class='hidden'>
				    <a href='#' onclick='popout()'><font color='#ffffff'>Close</font></a><br><br>";
				    foreach($data as $donnie => $marie){ 
						if($donnie == 'input' || $donnie == 'output'){ 
						    $b .= "<font color='white'>".ucfirst($donnie).':</font><br>';
						    foreach($marie as $captain => $tenille){ 
								if($captain == 'parts'){ 
								    $b .= "&nbsp;&nbsp;$captain:<br>";
								    	foreach($tenille as $joanie => $chachi){
											$b .= "&nbsp;&nbsp;&nbsp;&nbsp;$joanie: $chachi<br>";
								    	}
								} else {
								    $b .= "&nbsp;&nbsp;$captain: $tenille<br>";
								}
						    }
						} else {
						    $b .= "<font color='white'>".ucfirst($donnie).":</font> $marie<br>";
						}
				    }
					$b .= '</div>';
				}
				$b .= '
				<ul>
			</div>
		</div></body></html>';
		return $b;
    }
	function serialize($debug = 0)
	{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>';
		$xml .= "\n<definitions";
		foreach($this->namespaces as $k => $v) {
			$xml .= " xmlns:$k=\"$v\"";
		} 
		if (isset($this->namespaces['wsdl'])) {
			$xml .= " xmlns=\"" . $this->namespaces['wsdl'] . "\"";
		} 
		if (isset($this->namespaces['tns'])) {
			$xml .= " targetNamespace=\"" . $this->namespaces['tns'] . "\"";
		} 
		$xml .= '>'; 
		if (sizeof($this->import) > 0) {
			foreach($this->import as $ns => $list) {
				foreach ($list as $ii) {
					if ($ii['location'] != '') {
						$xml .= '<import location="' . $ii['location'] . '" namespace="' . $ns . '" />';
					} else {
						$xml .= '<import namespace="' . $ns . '" />';
					}
				}
			} 
		} 
		if (count($this->schemas)>=1) {
			$xml .= "\n<types>";
			foreach ($this->schemas as $ns => $list) {
				foreach ($list as $xs) {
					$xml .= $xs->serializeSchema();
				}
			}
			$xml .= '</types>';
		} 
		if (count($this->messages) >= 1) {
			foreach($this->messages as $msgName => $msgParts) {
				$xml .= "\n<message name=\"" . $msgName . '">';
				if(is_array($msgParts)){
					foreach($msgParts as $partName => $partType) {
						if (strpos($partType, ':')) {
						    $typePrefix = $this->getPrefixFromNamespace($this->getPrefix($partType));
						} elseif (isset($this->typemap[$this->namespaces['xsd']][$partType])) {
						    $typePrefix = 'xsd';
						} else {
						    foreach($this->typemap as $ns => $types) {
						        if (isset($types[$partType])) {
						            $typePrefix = $this->getPrefixFromNamespace($ns);
						        } 
						    } 
						    if (!isset($typePrefix)) {
						        die("$partType has no namespace!");
						    } 
						}
						$ns = $this->getNamespaceFromPrefix($typePrefix);
						$typeDef = $this->getTypeDef($this->getLocalPart($partType), $ns);
						if ($typeDef['typeClass'] == 'element') {
							$elementortype = 'element';
						} else {
							$elementortype = 'type';
						}
						$xml .= '<part name="' . $partName . '" ' . $elementortype . '="' . $typePrefix . ':' . $this->getLocalPart($partType) . '" />';
					}
				}
				$xml .= '</message>';
			} 
		} 
		if (count($this->bindings) >= 1) {
			$binding_xml = '';
			$portType_xml = '';
			foreach($this->bindings as $bindingName => $attrs) {
				$binding_xml .= "\n<binding name=\"" . $bindingName . '" type="tns:' . $attrs['portType'] . '">';
				$binding_xml .= '<soap:binding style="' . $attrs['style'] . '" transport="' . $attrs['transport'] . '"/>';
				$portType_xml .= "\n<portType name=\"" . $attrs['portType'] . '">';
				foreach($attrs['operations'] as $opName => $opParts) {
					$binding_xml .= '<operation name="' . $opName . '">';
					$binding_xml .= '<soap:operation soapAction="' . $opParts['soapAction'] . '" style="'. $opParts['style'] . '"/>';
					if (isset($opParts['input']['encodingStyle']) && $opParts['input']['encodingStyle'] != '') {
						$enc_style = ' encodingStyle="' . $opParts['input']['encodingStyle'] . '"';
					} else {
						$enc_style = '';
					}
					$binding_xml .= '<input><soap:body use="' . $opParts['input']['use'] . '" namespace="' . $opParts['input']['namespace'] . '"' . $enc_style . '/></input>';
					if (isset($opParts['output']['encodingStyle']) && $opParts['output']['encodingStyle'] != '') {
						$enc_style = ' encodingStyle="' . $opParts['output']['encodingStyle'] . '"';
					} else {
						$enc_style = '';
					}
					$binding_xml .= '<output><soap:body use="' . $opParts['output']['use'] . '" namespace="' . $opParts['output']['namespace'] . '"' . $enc_style . '/></output>';
					$binding_xml .= '</operation>';
					$portType_xml .= '<operation name="' . $opParts['name'] . '"';
					if (isset($opParts['parameterOrder'])) {
					    $portType_xml .= ' parameterOrder="' . $opParts['parameterOrder'] . '"';
					} 
					$portType_xml .= '>';
					if(isset($opParts['documentation']) && $opParts['documentation'] != '') {
						$portType_xml .= '<documentation>' . htmlspecialchars($opParts['documentation']) . '</documentation>';
					}
					$portType_xml .= '<input message="tns:' . $opParts['input']['message'] . '"/>';
					$portType_xml .= '<output message="tns:' . $opParts['output']['message'] . '"/>';
					$portType_xml .= '</operation>';
				} 
				$portType_xml .= '</portType>';
				$binding_xml .= '</binding>';
			} 
			$xml .= $portType_xml . $binding_xml;
		} 
		$xml .= "\n<service name=\"" . $this->serviceName . '">';
		if (count($this->ports) >= 1) {
			foreach($this->ports as $pName => $attrs) {
				$xml .= '<port name="' . $pName . '" binding="tns:' . $attrs['binding'] . '">';
				$xml .= '<soap:address location="' . $attrs['location'] . ($debug ? '?debug=1' : '') . '"/>';
				$xml .= '</port>';
			} 
		} 
		$xml .= '</service>';
		return $xml . "\n</definitions>";
	} 
	function serializeRPCParameters($operation, $direction, $parameters)
	{
		$this->debug("in serializeRPCParameters: operation=$operation, direction=$direction, XMLSchemaVersion=$this->XMLSchemaVersion"); 
		$this->appendDebug('parameters=' . $this->varDump($parameters));
		if ($direction != 'input' && $direction != 'output') {
			$this->debug('The value of the \$direction argument needs to be either "input" or "output"');
			$this->setError('The value of the \$direction argument needs to be either "input" or "output"');
			return false;
		} 
		if (!$opData = $this->getOperationData($operation)) {
			$this->debug('Unable to retrieve WSDL data for operation: ' . $operation);
			$this->setError('Unable to retrieve WSDL data for operation: ' . $operation);
			return false;
		}
		$this->debug('opData:');
		$this->appendDebug($this->varDump($opData));
		$encodingStyle = 'http:
		if(($direction == 'input') && isset($opData['output']['encodingStyle']) && ($opData['output']['encodingStyle'] != $encodingStyle)) {
			$encodingStyle = $opData['output']['encodingStyle'];
			$enc_style = $encodingStyle;
		}
		$xml = '';
		if (isset($opData[$direction]['parts']) && sizeof($opData[$direction]['parts']) > 0) {
			$use = $opData[$direction]['use'];
			$this->debug('have ' . count($opData[$direction]['parts']) . ' part(s) to serialize');
			if (is_array($parameters)) {
				$parametersArrayType = $this->isArraySimpleOrStruct($parameters);
				$this->debug('have ' . count($parameters) . ' parameter(s) provided as ' . $parametersArrayType . ' to serialize');
				foreach($opData[$direction]['parts'] as $name => $type) {
					$this->debug('serializing part "'.$name.'" of type "'.$type.'"');
					if (isset($opData[$direction]['encodingStyle']) && $encodingStyle != $opData[$direction]['encodingStyle']) {
						$encodingStyle = $opData[$direction]['encodingStyle'];			
						$enc_style = $encodingStyle;
					} else {
						$enc_style = false;
					}
					if ($parametersArrayType == 'arraySimple') {
						$p = array_shift($parameters);
						$this->debug('calling serializeType w/indexed param');
						$xml .= $this->serializeType($name, $type, $p, $use, $enc_style);
					} elseif (isset($parameters[$name])) {
						$this->debug('calling serializeType w/named param');
						$xml .= $this->serializeType($name, $type, $parameters[$name], $use, $enc_style);
					} else {
						$this->debug('calling serializeType w/null param');
						$xml .= $this->serializeType($name, $type, null, $use, $enc_style);
					}
				}
			} else {
				$this->debug('no parameters passed.');
			}
		}
		$this->debug("serializeRPCParameters returning: $xml");
		return $xml;
	} 
	function serializeParameters($operation, $direction, $parameters)
	{
		$this->debug("in serializeParameters: operation=$operation, direction=$direction, XMLSchemaVersion=$this->XMLSchemaVersion"); 
		$this->appendDebug('parameters=' . $this->varDump($parameters));
		if ($direction != 'input' && $direction != 'output') {
			$this->debug('The value of the \$direction argument needs to be either "input" or "output"');
			$this->setError('The value of the \$direction argument needs to be either "input" or "output"');
			return false;
		} 
		if (!$opData = $this->getOperationData($operation)) {
			$this->debug('Unable to retrieve WSDL data for operation: ' . $operation);
			$this->setError('Unable to retrieve WSDL data for operation: ' . $operation);
			return false;
		}
		$this->debug('opData:');
		$this->appendDebug($this->varDump($opData));
		$encodingStyle = 'http:
		if(($direction == 'input') && isset($opData['output']['encodingStyle']) && ($opData['output']['encodingStyle'] != $encodingStyle)) {
			$encodingStyle = $opData['output']['encodingStyle'];
			$enc_style = $encodingStyle;
		}
		$xml = '';
		if (isset($opData[$direction]['parts']) && sizeof($opData[$direction]['parts']) > 0) {
			$use = $opData[$direction]['use'];
			$this->debug("use=$use");
			$this->debug('got ' . count($opData[$direction]['parts']) . ' part(s)');
			if (is_array($parameters)) {
				$parametersArrayType = $this->isArraySimpleOrStruct($parameters);
				$this->debug('have ' . $parametersArrayType . ' parameters');
				foreach($opData[$direction]['parts'] as $name => $type) {
					$this->debug('serializing part "'.$name.'" of type "'.$type.'"');
					if(isset($opData[$direction]['encodingStyle']) && $encodingStyle != $opData[$direction]['encodingStyle']) {
						$encodingStyle = $opData[$direction]['encodingStyle'];			
						$enc_style = $encodingStyle;
					} else {
						$enc_style = false;
					}
					if ($parametersArrayType == 'arraySimple') {
						$p = array_shift($parameters);
						$this->debug('calling serializeType w/indexed param');
						$xml .= $this->serializeType($name, $type, $p, $use, $enc_style);
					} elseif (isset($parameters[$name])) {
						$this->debug('calling serializeType w/named param');
						$xml .= $this->serializeType($name, $type, $parameters[$name], $use, $enc_style);
					} else {
						$this->debug('calling serializeType w/null param');
						$xml .= $this->serializeType($name, $type, null, $use, $enc_style);
					}
				}
			} else {
				$this->debug('no parameters passed.');
			}
		}
		$this->debug("serializeParameters returning: $xml");
		return $xml;
	} 
	function serializeType($name, $type, $value, $use='encoded', $encodingStyle=false, $unqualified=false)
	{
		$this->debug("in serializeType: name=$name, type=$type, use=$use, encodingStyle=$encodingStyle, unqualified=" . ($unqualified ? "unqualified" : "qualified"));
		$this->appendDebug("value=" . $this->varDump($value));
		if($use == 'encoded' && $encodingStyle) {
			$encodingStyle = ' SOAP-ENV:encodingStyle="' . $encodingStyle . '"';
		}
    	if (is_object($value) && get_class($value) == 'soapval') {
    		if ($value->type_ns) {
    			$type = $value->type_ns . ':' . $value->type;
		    	$forceType = true;
		    	$this->debug("in serializeType: soapval overrides type to $type");
    		} elseif ($value->type) {
	    		$type = $value->type;
		    	$forceType = true;
		    	$this->debug("in serializeType: soapval overrides type to $type");
	    	} else {
	    		$forceType = false;
		    	$this->debug("in serializeType: soapval does not override type");
	    	}
	    	$attrs = $value->attributes;
	    	$value = $value->value;
	    	$this->debug("in serializeType: soapval overrides value to $value");
	    	if ($attrs) {
	    		if (!is_array($value)) {
	    			$value['!'] = $value;
	    		}
	    		foreach ($attrs as $n => $v) {
	    			$value['!' . $n] = $v;
	    		}
		    	$this->debug("in serializeType: soapval provides attributes");
		    }
        } else {
        	$forceType = false;
        }
		$xml = '';
		if (strpos($type, ':')) {
			$uqType = substr($type, strrpos($type, ':') + 1);
			$ns = substr($type, 0, strrpos($type, ':'));
			$this->debug("in serializeType: got a prefixed type: $uqType, $ns");
			if ($this->getNamespaceFromPrefix($ns)) {
				$ns = $this->getNamespaceFromPrefix($ns);
				$this->debug("in serializeType: expanded prefixed type: $uqType, $ns");
			}
			if($ns == $this->XMLSchemaVersion || $ns == 'http:
				$this->debug('in serializeType: type namespace indicates XML Schema or SOAP Encoding type');
				if ($unqualified  && $use == 'literal') {
					$elementNS = " xmlns=\"\"";
				} else {
					$elementNS = '';
				}
				if (is_null($value)) {
					if ($use == 'literal') {
						$xml = "<$name$elementNS/>";
					} else {
						$xml = "<$name$elementNS xsi:nil=\"true\" xsi:type=\"" . $this->getPrefixFromNamespace($ns) . ":$uqType\"/>";
					}
					$this->debug("in serializeType: returning: $xml");
					return $xml;
				}
		    	if ($uqType == 'boolean') {
		    		if ((is_string($value) && $value == 'false') || (! $value)) {
						$value = 'false';
					} else {
						$value = 'true';
					}
				} 
				if ($uqType == 'string' && gettype($value) == 'string') {
					$value = $this->expandEntities($value);
				}
				if (($uqType == 'long' || $uqType == 'unsignedLong') && gettype($value) == 'double') {
					$value = sprintf("%.0lf", $value);
				}
				if (!$this->getTypeDef($uqType, $ns)) {
					if ($use == 'literal') {
						if ($forceType) {
							$xml = "<$name$elementNS xsi:type=\"" . $this->getPrefixFromNamespace($ns) . ":$uqType\">$value</$name>";
						} else {
							$xml = "<$name$elementNS>$value</$name>";
						}
					} else {
						$xml = "<$name$elementNS xsi:type=\"" . $this->getPrefixFromNamespace($ns) . ":$uqType\"$encodingStyle>$value</$name>";
					}
					$this->debug("in serializeType: returning: $xml");
					return $xml;
				}
				$this->debug('custom type extends XML Schema or SOAP Encoding namespace (yuck)');
			} else if ($ns == 'http:
				$this->debug('in serializeType: appears to be Apache SOAP type');
				if ($uqType == 'Map') {
					$tt_prefix = $this->getPrefixFromNamespace('http:
					if (! $tt_prefix) {
						$this->debug('in serializeType: Add namespace for Apache SOAP type');
						$tt_prefix = 'ns' . rand(1000, 9999);
						$this->namespaces[$tt_prefix] = 'http:
						$tt_prefix = $this->getPrefixFromNamespace('http:
					}
					$contents = '';
					foreach($value as $k => $v) {
						$this->debug("serializing map element: key $k, value $v");
						$contents .= '<item>';
						$contents .= $this->serialize_val($k,'key',false,false,false,false,$use);
						$contents .= $this->serialize_val($v,'value',false,false,false,false,$use);
						$contents .= '</item>';
					}
					if ($use == 'literal') {
						if ($forceType) {
							$xml = "<$name xsi:type=\"" . $tt_prefix . ":$uqType\">$contents</$name>";
						} else {
							$xml = "<$name>$contents</$name>";
						}
					} else {
						$xml = "<$name xsi:type=\"" . $tt_prefix . ":$uqType\"$encodingStyle>$contents</$name>";
					}
					$this->debug("in serializeType: returning: $xml");
					return $xml;
				}
				$this->debug('in serializeType: Apache SOAP type, but only support Map');
			}
		} else {
			$this->debug("in serializeType: No namespace for type $type");
			$ns = '';
			$uqType = $type;
		}
		if(!$typeDef = $this->getTypeDef($uqType, $ns)){
			$this->setError("$type ($uqType) is not a supported type.");
			$this->debug("in serializeType: $type ($uqType) is not a supported type.");
			return false;
		} else {
			$this->debug("in serializeType: found typeDef");
			$this->appendDebug('typeDef=' . $this->varDump($typeDef));
		}
		$phpType = $typeDef['phpType'];
		$this->debug("in serializeType: uqType: $uqType, ns: $ns, phptype: $phpType, arrayType: " . (isset($typeDef['arrayType']) ? $typeDef['arrayType'] : '') ); 
		if ($phpType == 'struct') {
			if (isset($typeDef['typeClass']) && $typeDef['typeClass'] == 'element') {
				$elementName = $uqType;
				if (isset($typeDef['form']) && ($typeDef['form'] == 'qualified')) {
					$elementNS = " xmlns=\"$ns\"";
				} else {
					$elementNS = " xmlns=\"\"";
				}
			} else {
				$elementName = $name;
				if ($unqualified) {
					$elementNS = " xmlns=\"\"";
				} else {
					$elementNS = '';
				}
			}
			if (is_null($value)) {
				if ($use == 'literal') {
					$xml = "<$elementName$elementNS/>";
				} else {
					$xml = "<$elementName$elementNS xsi:nil=\"true\" xsi:type=\"" . $this->getPrefixFromNamespace($ns) . ":$uqType\"/>";
				}
				$this->debug("in serializeType: returning: $xml");
				return $xml;
			}
			if (is_object($value)) {
				$value = get_object_vars($value);
			}
			if (is_array($value)) {
				$elementAttrs = $this->serializeComplexTypeAttributes($typeDef, $value, $ns, $uqType);
				if ($use == 'literal') {
					if ($forceType) {
						$xml = "<$elementName$elementNS$elementAttrs xsi:type=\"" . $this->getPrefixFromNamespace($ns) . ":$uqType\">";
					} else {
						$xml = "<$elementName$elementNS$elementAttrs>";
					}
				} else {
					$xml = "<$elementName$elementNS$elementAttrs xsi:type=\"" . $this->getPrefixFromNamespace($ns) . ":$uqType\"$encodingStyle>";
				}
				$xml .= $this->serializeComplexTypeElements($typeDef, $value, $ns, $uqType, $use, $encodingStyle);
				$xml .= "</$elementName>";
			} else {
				$this->debug("in serializeType: phpType is struct, but value is not an array");
				$this->setError("phpType is struct, but value is not an array: see debug output for details");
				$xml = '';
			}
		} elseif ($phpType == 'array') {
			if (isset($typeDef['form']) && ($typeDef['form'] == 'qualified')) {
				$elementNS = " xmlns=\"$ns\"";
			} else {
				if ($unqualified) {
					$elementNS = " xmlns=\"\"";
				} else {
					$elementNS = '';
				}
			}
			if (is_null($value)) {
				if ($use == 'literal') {
					$xml = "<$name$elementNS/>";
				} else {
					$xml = "<$name$elementNS xsi:nil=\"true\" xsi:type=\"" .
						$this->getPrefixFromNamespace('http:
						":Array\" " .
						$this->getPrefixFromNamespace('http:
						':arrayType="' .
						$this->getPrefixFromNamespace($this->getPrefix($typeDef['arrayType'])) .
						':' .
						$this->getLocalPart($typeDef['arrayType'])."[0]\"/>";
				}
				$this->debug("in serializeType: returning: $xml");
				return $xml;
			}
			if (isset($typeDef['multidimensional'])) {
				$nv = array();
				foreach($value as $v) {
					$cols = ',' . sizeof($v);
					$nv = array_merge($nv, $v);
				} 
				$value = $nv;
			} else {
				$cols = '';
			} 
			if (is_array($value) && sizeof($value) >= 1) {
				$rows = sizeof($value);
				$contents = '';
				foreach($value as $k => $v) {
					$this->debug("serializing array element: $k, $v of type: $typeDef[arrayType]");
					if (!in_array($typeDef['arrayType'],$this->typemap['http:
					    $contents .= $this->serializeType('item', $typeDef['arrayType'], $v, $use);
					} else {
					    $contents .= $this->serialize_val($v, 'item', $typeDef['arrayType'], null, $this->XMLSchemaVersion, false, $use);
					} 
				}
			} else {
				$rows = 0;
				$contents = null;
			}
			if ($use == 'literal') {
				$xml = "<$name$elementNS>"
					.$contents
					."</$name>";
			} else {
				$xml = "<$name$elementNS xsi:type=\"".$this->getPrefixFromNamespace('http:
					$this->getPrefixFromNamespace('http:
					.':arrayType="'
					.$this->getPrefixFromNamespace($this->getPrefix($typeDef['arrayType']))
					.":".$this->getLocalPart($typeDef['arrayType'])."[$rows$cols]\">"
					.$contents
					."</$name>";
			}
		} elseif ($phpType == 'scalar') {
			if (isset($typeDef['form']) && ($typeDef['form'] == 'qualified')) {
				$elementNS = " xmlns=\"$ns\"";
			} else {
				if ($unqualified) {
					$elementNS = " xmlns=\"\"";
				} else {
					$elementNS = '';
				}
			}
			if ($use == 'literal') {
				if ($forceType) {
					$xml = "<$name$elementNS xsi:type=\"" . $this->getPrefixFromNamespace($ns) . ":$uqType\">$value</$name>";
				} else {
					$xml = "<$name$elementNS>$value</$name>";
				}
			} else {
				$xml = "<$name$elementNS xsi:type=\"" . $this->getPrefixFromNamespace($ns) . ":$uqType\"$encodingStyle>$value</$name>";
			}
		}
		$this->debug("in serializeType: returning: $xml");
		return $xml;
	}
	function serializeComplexTypeAttributes($typeDef, $value, $ns, $uqType) {
		$xml = '';
		if (isset($typeDef['attrs']) && is_array($typeDef['attrs'])) {
			$this->debug("serialize attributes for XML Schema type $ns:$uqType");
			if (is_array($value)) {
				$xvalue = $value;
			} elseif (is_object($value)) {
				$xvalue = get_object_vars($value);
			} else {
				$this->debug("value is neither an array nor an object for XML Schema type $ns:$uqType");
				$xvalue = array();
			}
			foreach ($typeDef['attrs'] as $aName => $attrs) {
				if (isset($xvalue['!' . $aName])) {
					$xname = '!' . $aName;
					$this->debug("value provided for attribute $aName with key $xname");
				} elseif (isset($xvalue[$aName])) {
					$xname = $aName;
					$this->debug("value provided for attribute $aName with key $xname");
				} elseif (isset($attrs['default'])) {
					$xname = '!' . $aName;
					$xvalue[$xname] = $attrs['default'];
					$this->debug('use default value of ' . $xvalue[$aName] . ' for attribute ' . $aName);
				} else {
					$xname = '';
					$this->debug("no value provided for attribute $aName");
				}
				if ($xname) {
					$xml .=  " $aName=\"" . $this->expandEntities($xvalue[$xname]) . "\"";
				}
			} 
		} else {
			$this->debug("no attributes to serialize for XML Schema type $ns:$uqType");
		}
		if (isset($typeDef['extensionBase'])) {
			$ns = $this->getPrefix($typeDef['extensionBase']);
			$uqType = $this->getLocalPart($typeDef['extensionBase']);
			if ($this->getNamespaceFromPrefix($ns)) {
				$ns = $this->getNamespaceFromPrefix($ns);
			}
			if ($typeDef = $this->getTypeDef($uqType, $ns)) {
				$this->debug("serialize attributes for extension base $ns:$uqType");
				$xml .= $this->serializeComplexTypeAttributes($typeDef, $value, $ns, $uqType);
			} else {
				$this->debug("extension base $ns:$uqType is not a supported type");
			}
		}
		return $xml;
	}
	function serializeComplexTypeElements($typeDef, $value, $ns, $uqType, $use='encoded', $encodingStyle=false) {
		$xml = '';
		if (isset($typeDef['elements']) && is_array($typeDef['elements'])) {
			$this->debug("in serializeComplexTypeElements, serialize elements for XML Schema type $ns:$uqType");
			if (is_array($value)) {
				$xvalue = $value;
			} elseif (is_object($value)) {
				$xvalue = get_object_vars($value);
			} else {
				$this->debug("value is neither an array nor an object for XML Schema type $ns:$uqType");
				$xvalue = array();
			}
			if (count($typeDef['elements']) != count($xvalue)){
				$optionals = true;
			}
			foreach ($typeDef['elements'] as $eName => $attrs) {
				if (!isset($xvalue[$eName])) {
					if (isset($attrs['default'])) {
						$xvalue[$eName] = $attrs['default'];
						$this->debug('use default value of ' . $xvalue[$eName] . ' for element ' . $eName);
					}
				}
				if (isset($optionals)
				    && (!isset($xvalue[$eName])) 
					&& ( (!isset($attrs['nillable'])) || $attrs['nillable'] != 'true')
					){
					if (isset($attrs['minOccurs']) && $attrs['minOccurs'] <> '0') {
						$this->debug("apparent error: no value provided for element $eName with minOccurs=" . $attrs['minOccurs']);
					}
					$this->debug("no value provided for complexType element $eName and element is not nillable, so serialize nothing");
				} else {
					if (isset($xvalue[$eName])) {
					    $v = $xvalue[$eName];
					} else {
					    $v = null;
					}
					if (isset($attrs['form'])) {
						$unqualified = ($attrs['form'] == 'unqualified');
					} else {
						$unqualified = false;
					}
					if (isset($attrs['maxOccurs']) && ($attrs['maxOccurs'] == 'unbounded' || $attrs['maxOccurs'] > 1) && isset($v) && is_array($v) && $this->isArraySimpleOrStruct($v) == 'arraySimple') {
						$vv = $v;
						foreach ($vv as $k => $v) {
							if (isset($attrs['type']) || isset($attrs['ref'])) {
							    $xml .= $this->serializeType($eName, isset($attrs['type']) ? $attrs['type'] : $attrs['ref'], $v, $use, $encodingStyle, $unqualified);
							} else {
							    $this->debug("calling serialize_val() for $v, $eName, false, false, false, false, $use");
							    $xml .= $this->serialize_val($v, $eName, false, false, false, false, $use);
							}
						}
					} else {
						if (isset($attrs['type']) || isset($attrs['ref'])) {
						    $xml .= $this->serializeType($eName, isset($attrs['type']) ? $attrs['type'] : $attrs['ref'], $v, $use, $encodingStyle, $unqualified);
						} else {
						    $this->debug("calling serialize_val() for $v, $eName, false, false, false, false, $use");
						    $xml .= $this->serialize_val($v, $eName, false, false, false, false, $use);
						}
					}
				}
			} 
		} else {
			$this->debug("no elements to serialize for XML Schema type $ns:$uqType");
		}
		if (isset($typeDef['extensionBase'])) {
			$ns = $this->getPrefix($typeDef['extensionBase']);
			$uqType = $this->getLocalPart($typeDef['extensionBase']);
			if ($this->getNamespaceFromPrefix($ns)) {
				$ns = $this->getNamespaceFromPrefix($ns);
			}
			if ($typeDef = $this->getTypeDef($uqType, $ns)) {
				$this->debug("serialize elements for extension base $ns:$uqType");
				$xml .= $this->serializeComplexTypeElements($typeDef, $value, $ns, $uqType, $use, $encodingStyle);
			} else {
				$this->debug("extension base $ns:$uqType is not a supported type");
			}
		}
		return $xml;
	}
	function addComplexType($name,$typeClass='complexType',$phpType='array',$compositor='',$restrictionBase='',$elements=array(),$attrs=array(),$arrayType='') {
		if (count($elements) > 0) {
	    	foreach($elements as $n => $e){
	            foreach ($e as $k => $v) {
		            $k = strpos($k,':') ? $this->expandQname($k) : $k;
		            $v = strpos($v,':') ? $this->expandQname($v) : $v;
		            $ee[$k] = $v;
		    	}
	    		$eElements[$n] = $ee;
	    	}
	    	$elements = $eElements;
		}
		if (count($attrs) > 0) {
	    	foreach($attrs as $n => $a){
	            foreach ($a as $k => $v) {
		            $k = strpos($k,':') ? $this->expandQname($k) : $k;
		            $v = strpos($v,':') ? $this->expandQname($v) : $v;
		            $aa[$k] = $v;
		    	}
	    		$eAttrs[$n] = $aa;
	    	}
	    	$attrs = $eAttrs;
		}
		$restrictionBase = strpos($restrictionBase,':') ? $this->expandQname($restrictionBase) : $restrictionBase;
		$arrayType = strpos($arrayType,':') ? $this->expandQname($arrayType) : $arrayType;
		$typens = isset($this->namespaces['types']) ? $this->namespaces['types'] : $this->namespaces['tns'];
		$this->schemas[$typens][0]->addComplexType($name,$typeClass,$phpType,$compositor,$restrictionBase,$elements,$attrs,$arrayType);
	}
	function addSimpleType($name, $restrictionBase='', $typeClass='simpleType', $phpType='scalar', $enumeration=array()) {
		$restrictionBase = strpos($restrictionBase,':') ? $this->expandQname($restrictionBase) : $restrictionBase;
		$typens = isset($this->namespaces['types']) ? $this->namespaces['types'] : $this->namespaces['tns'];
		$this->schemas[$typens][0]->addSimpleType($name, $restrictionBase, $typeClass, $phpType, $enumeration);
	}
	function addElement($attrs) {
		$typens = isset($this->namespaces['types']) ? $this->namespaces['types'] : $this->namespaces['tns'];
		$this->schemas[$typens][0]->addElement($attrs);
	}
	function addOperation($name, $in = false, $out = false, $namespace = false, $soapaction = false, $style = 'rpc', $use = 'encoded', $documentation = '', $encodingStyle = ''){
		if ($use == 'encoded' && $encodingStyle == '') {
			$encodingStyle = 'http:
		}
		if ($style == 'document') {
			$elements = array();
			foreach ($in as $n => $t) {
				$elements[$n] = array('name' => $n, 'type' => $t);
			}
			$this->addComplexType($name . 'RequestType', 'complexType', 'struct', 'all', '', $elements);
			$this->addElement(array('name' => $name, 'type' => $name . 'RequestType'));
			$in = array('parameters' => 'tns:' . $name);
			$elements = array();
			foreach ($out as $n => $t) {
				$elements[$n] = array('name' => $n, 'type' => $t);
			}
			$this->addComplexType($name . 'ResponseType', 'complexType', 'struct', 'all', '', $elements);
			$this->addElement(array('name' => $name . 'Response', 'type' => $name . 'ResponseType'));
			$out = array('parameters' => 'tns:' . $name . 'Response');
		}
		$this->bindings[ $this->serviceName . 'Binding' ]['operations'][$name] =
		array(
		'name' => $name,
		'binding' => $this->serviceName . 'Binding',
		'endpoint' => $this->endpoint,
		'soapAction' => $soapaction,
		'style' => $style,
		'input' => array(
			'use' => $use,
			'namespace' => $namespace,
			'encodingStyle' => $encodingStyle,
			'message' => $name . 'Request',
			'parts' => $in),
		'output' => array(
			'use' => $use,
			'namespace' => $namespace,
			'encodingStyle' => $encodingStyle,
			'message' => $name . 'Response',
			'parts' => $out),
		'namespace' => $namespace,
		'transport' => 'http:
		'documentation' => $documentation); 
		if($in)
		{
			foreach($in as $pName => $pType)
			{
				if(strpos($pType,':')) {
					$pType = $this->getNamespaceFromPrefix($this->getPrefix($pType)).":".$this->getLocalPart($pType);
				}
				$this->messages[$name.'Request'][$pName] = $pType;
			}
		} else {
            $this->messages[$name.'Request']= '0';
        }
		if($out)
		{
			foreach($out as $pName => $pType)
			{
				if(strpos($pType,':')) {
					$pType = $this->getNamespaceFromPrefix($this->getPrefix($pType)).":".$this->getLocalPart($pType);
				}
				$this->messages[$name.'Response'][$pName] = $pType;
			}
		} else {
            $this->messages[$name.'Response']= '0';
        }
		return true;
	} 
}
?><?php
class soap_parser extends nusoap_base {
	var $xml = '';
	var $xml_encoding = '';
	var $method = '';
	var $root_struct = '';
	var $root_struct_name = '';
	var $root_struct_namespace = '';
	var $root_header = '';
    var $document = '';			
	var $status = '';
	var $position = 0;
	var $depth = 0;
	var $default_namespace = '';
	var $namespaces = array();
	var $message = array();
    var $parent = '';
	var $fault = false;
	var $fault_code = '';
	var $fault_str = '';
	var $fault_detail = '';
	var $depth_array = array();
	var $debug_flag = true;
	var $soapresponse = NULL;
	var $responseHeaders = '';	
	var $body_position = 0;
	var $ids = array();
	var $multirefs = array();
	var $decode_utf8 = true;
	function soap_parser($xml,$encoding='UTF-8',$method='',$decode_utf8=true){
		parent::nusoap_base();
		$this->xml = $xml;
		$this->xml_encoding = $encoding;
		$this->method = $method;
		$this->decode_utf8 = $decode_utf8;
		if(!empty($xml)){
			$pos_xml = strpos($xml, '<?xml');
			if ($pos_xml !== FALSE) {
				$xml_decl = substr($xml, $pos_xml, strpos($xml, '?>', $pos_xml + 2) - $pos_xml + 1);
				if (preg_match("/encoding=[\"']([^\"']*)[\"']/", $xml_decl, $res)) {
					$xml_encoding = $res[1];
					if (strtoupper($xml_encoding) != $encoding) {
						$err = "Charset from HTTP Content-Type '" . $encoding . "' does not match encoding from XML declaration '" . $xml_encoding . "'";
						$this->debug($err);
						if ($encoding != 'ISO-8859-1' || strtoupper($xml_encoding) != 'UTF-8') {
							$this->setError($err);
							return;
						}
					} else {
						$this->debug('Charset from HTTP Content-Type matches encoding from XML declaration');
					}
				} else {
					$this->debug('No encoding specified in XML declaration');
				}
			} else {
				$this->debug('No XML declaration');
			}
			$this->debug('Entering soap_parser(), length='.strlen($xml).', encoding='.$encoding);
			$this->parser = xml_parser_create($this->xml_encoding);
			xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
			xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->xml_encoding);
			xml_set_object($this->parser, $this);
			xml_set_element_handler($this->parser, 'start_element','end_element');
			xml_set_character_data_handler($this->parser,'character_data');
			if(!xml_parse($this->parser,$xml,true)){
			    $err = sprintf('XML error parsing SOAP payload on line %d: %s',
			    xml_get_current_line_number($this->parser),
			    xml_error_string(xml_get_error_code($this->parser)));
				$this->debug($err);
				$this->debug("XML payload:\n" . $xml);
				$this->setError($err);
			} else {
				$this->debug('parsed successfully, found root struct: '.$this->root_struct.' of name '.$this->root_struct_name);
				$this->soapresponse = $this->message[$this->root_struct]['result'];
				if(sizeof($this->multirefs) > 0){
					foreach($this->multirefs as $id => $hrefs){
						$this->debug('resolving multirefs for id: '.$id);
						$idVal = $this->buildVal($this->ids[$id]);
						if (is_array($idVal) && isset($idVal['!id'])) {
							unset($idVal['!id']);
						}
						foreach($hrefs as $refPos => $ref){
							$this->debug('resolving href at pos '.$refPos);
							$this->multirefs[$id][$refPos] = $idVal;
						}
					}
				}
			}
			xml_parser_free($this->parser);
		} else {
			$this->debug('xml was empty, didn\'t parse!');
			$this->setError('xml was empty, didn\'t parse!');
		}
	}
	function start_element($parser, $name, $attrs) {
		$pos = $this->position++;
		$this->message[$pos] = array('pos' => $pos,'children'=>'','cdata'=>'');
		$this->message[$pos]['depth'] = $this->depth++;
		if($pos != 0){
			$this->message[$this->parent]['children'] .= '|'.$pos;
		}
		$this->message[$pos]['parent'] = $this->parent;
		$this->parent = $pos;
		$this->depth_array[$this->depth] = $pos;
		if(strpos($name,':')){
			$prefix = substr($name,0,strpos($name,':'));
			$name = substr(strstr($name,':'),1);
		}
		if($name == 'Envelope'){
			$this->status = 'envelope';
		} elseif($name == 'Header'){
			$this->root_header = $pos;
			$this->status = 'header';
		} elseif($name == 'Body'){
			$this->status = 'body';
			$this->body_position = $pos;
		} elseif($this->status == 'body' && $pos == ($this->body_position+1)){
			$this->status = 'method';
			$this->root_struct_name = $name;
			$this->root_struct = $pos;
			$this->message[$pos]['type'] = 'struct';
			$this->debug("found root struct $this->root_struct_name, pos $this->root_struct");
		}
		$this->message[$pos]['status'] = $this->status;
		$this->message[$pos]['name'] = htmlspecialchars($name);
		$this->message[$pos]['attrs'] = $attrs;
        $attstr = '';
		foreach($attrs as $key => $value){
        	$key_prefix = $this->getPrefix($key);
			$key_localpart = $this->getLocalPart($key);
            if($key_prefix == 'xmlns'){
				if(ereg('^http:
					$this->XMLSchemaVersion = $value;
					$this->namespaces['xsd'] = $this->XMLSchemaVersion;
					$this->namespaces['xsi'] = $this->XMLSchemaVersion.'-instance';
				}
                $this->namespaces[$key_localpart] = $value;
				if($name == $this->root_struct_name){
					$this->methodNamespace = $value;
				}
            } elseif($key_localpart == 'type'){
            	$value_prefix = $this->getPrefix($value);
                $value_localpart = $this->getLocalPart($value);
				$this->message[$pos]['type'] = $value_localpart;
				$this->message[$pos]['typePrefix'] = $value_prefix;
                if(isset($this->namespaces[$value_prefix])){
                	$this->message[$pos]['type_namespace'] = $this->namespaces[$value_prefix];
                } else if(isset($attrs['xmlns:'.$value_prefix])) {
					$this->message[$pos]['type_namespace'] = $attrs['xmlns:'.$value_prefix];
                }
			} elseif($key_localpart == 'arrayType'){
				$this->message[$pos]['type'] = 'array';
				$expr = '([A-Za-z0-9_]+):([A-Za-z]+[A-Za-z0-9_]+)\[([0-9]+),?([0-9]*)\]';
				if(ereg($expr,$value,$regs)){
					$this->message[$pos]['typePrefix'] = $regs[1];
					$this->message[$pos]['arrayTypePrefix'] = $regs[1];
	                if (isset($this->namespaces[$regs[1]])) {
	                	$this->message[$pos]['arrayTypeNamespace'] = $this->namespaces[$regs[1]];
	                } else if (isset($attrs['xmlns:'.$regs[1]])) {
						$this->message[$pos]['arrayTypeNamespace'] = $attrs['xmlns:'.$regs[1]];
	                }
					$this->message[$pos]['arrayType'] = $regs[2];
					$this->message[$pos]['arraySize'] = $regs[3];
					$this->message[$pos]['arrayCols'] = $regs[4];
				}
			} elseif ($key_localpart == 'nil'){
				$this->message[$pos]['nil'] = ($value == 'true' || $value == '1');
			} elseif ($key != 'href' && $key != 'xmlns' && $key_localpart != 'encodingStyle' && $key_localpart != 'root') {
				$this->message[$pos]['xattrs']['!' . $key] = $value;
			}
			if ($key == 'xmlns') {
				$this->default_namespace = $value;
			}
			if($key == 'id'){
				$this->ids[$value] = $pos;
			}
			if($key_localpart == 'root' && $value == 1){
				$this->status = 'method';
				$this->root_struct_name = $name;
				$this->root_struct = $pos;
				$this->debug("found root struct $this->root_struct_name, pos $pos");
			}
            $attstr .= " $key=\"$value\"";
		}
		if(isset($prefix)){
			$this->message[$pos]['namespace'] = $this->namespaces[$prefix];
			$this->default_namespace = $this->namespaces[$prefix];
		} else {
			$this->message[$pos]['namespace'] = $this->default_namespace;
		}
        if($this->status == 'header'){
        	if ($this->root_header != $pos) {
	        	$this->responseHeaders .= "<" . (isset($prefix) ? $prefix . ':' : '') . "$name$attstr>";
	        }
        } elseif($this->root_struct_name != ''){
        	$this->document .= "<" . (isset($prefix) ? $prefix . ':' : '') . "$name$attstr>";
        }
	}
	function end_element($parser, $name) {
		$pos = $this->depth_array[$this->depth--];
		if(strpos($name,':')){
			$prefix = substr($name,0,strpos($name,':'));
			$name = substr(strstr($name,':'),1);
		}
		if(isset($this->body_position) && $pos > $this->body_position){
			if(isset($this->message[$pos]['attrs']['href'])){
				$id = substr($this->message[$pos]['attrs']['href'],1);
				$this->multirefs[$id][$pos] = 'placeholder';
				$this->message[$pos]['result'] =& $this->multirefs[$id][$pos];
			} elseif($this->message[$pos]['children'] != ''){
				if(!isset($this->message[$pos]['result'])){
					$this->message[$pos]['result'] = $this->buildVal($pos);
				}
			} elseif (isset($this->message[$pos]['xattrs'])) {
				if (isset($this->message[$pos]['nil']) && $this->message[$pos]['nil']) {
					$this->message[$pos]['xattrs']['!'] = null;
				} elseif (isset($this->message[$pos]['cdata']) && trim($this->message[$pos]['cdata']) != '') {
	            	if (isset($this->message[$pos]['type'])) {
						$this->message[$pos]['xattrs']['!'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$pos]['type'], isset($this->message[$pos]['type_namespace']) ? $this->message[$pos]['type_namespace'] : '');
					} else {
						$parent = $this->message[$pos]['parent'];
						if (isset($this->message[$parent]['type']) && ($this->message[$parent]['type'] == 'array') && isset($this->message[$parent]['arrayType'])) {
							$this->message[$pos]['xattrs']['!'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$parent]['arrayType'], isset($this->message[$parent]['arrayTypeNamespace']) ? $this->message[$parent]['arrayTypeNamespace'] : '');
						} else {
							$this->message[$pos]['xattrs']['!'] = $this->message[$pos]['cdata'];
						}
					}
				}
				$this->message[$pos]['result'] = $this->message[$pos]['xattrs'];
			} else {
				if (isset($this->message[$pos]['nil']) && $this->message[$pos]['nil']) {
					$this->message[$pos]['xattrs']['!'] = null;
				} elseif (isset($this->message[$pos]['type'])) {
					$this->message[$pos]['result'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$pos]['type'], isset($this->message[$pos]['type_namespace']) ? $this->message[$pos]['type_namespace'] : '');
				} else {
					$parent = $this->message[$pos]['parent'];
					if (isset($this->message[$parent]['type']) && ($this->message[$parent]['type'] == 'array') && isset($this->message[$parent]['arrayType'])) {
						$this->message[$pos]['result'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$parent]['arrayType'], isset($this->message[$parent]['arrayTypeNamespace']) ? $this->message[$parent]['arrayTypeNamespace'] : '');
					} else {
						$this->message[$pos]['result'] = $this->message[$pos]['cdata'];
					}
				}
			}
		}
        if($this->status == 'header'){
        	if ($this->root_header != $pos) {
	        	$this->responseHeaders .= "</" . (isset($prefix) ? $prefix . ':' : '') . "$name>";
	        }
        } elseif($pos >= $this->root_struct){
        	$this->document .= "</" . (isset($prefix) ? $prefix . ':' : '') . "$name>";
        }
		if($pos == $this->root_struct){
			$this->status = 'body';
			$this->root_struct_namespace = $this->message[$pos]['namespace'];
		} elseif($name == 'Body'){
			$this->status = 'envelope';
		 } elseif($name == 'Header'){
			$this->status = 'envelope';
		} elseif($name == 'Envelope'){
		}
		$this->parent = $this->message[$pos]['parent'];
	}
	function character_data($parser, $data){
		$pos = $this->depth_array[$this->depth];
		if ($this->xml_encoding=='UTF-8'){
			if($this->decode_utf8){
				$data = utf8_decode($data);
			}
		}
        $this->message[$pos]['cdata'] .= $data;
        if($this->status == 'header'){
        	$this->responseHeaders .= $data;
        } else {
        	$this->document .= $data;
        }
	}
	function get_response(){
		return $this->soapresponse;
	}
	function getHeaders(){
	    return $this->responseHeaders;
	}
	function decodeSimple($value, $type, $typens) {
		if ((!isset($type)) || $type == 'string' || $type == 'long' || $type == 'unsignedLong') {
			return (string) $value;
		}
		if ($type == 'int' || $type == 'integer' || $type == 'short' || $type == 'byte') {
			return (int) $value;
		}
		if ($type == 'float' || $type == 'double' || $type == 'decimal') {
			return (double) $value;
		}
		if ($type == 'boolean') {
			if (strtolower($value) == 'false' || strtolower($value) == 'f') {
				return false;
			}
			return (boolean) $value;
		}
		if ($type == 'base64' || $type == 'base64Binary') {
			$this->debug('Decode base64 value');
			return base64_decode($value);
		}
		if ($type == 'nonPositiveInteger' || $type == 'negativeInteger'
			|| $type == 'nonNegativeInteger' || $type == 'positiveInteger'
			|| $type == 'unsignedInt'
			|| $type == 'unsignedShort' || $type == 'unsignedByte') {
			return (int) $value;
		}
		if ($type == 'array') {
			return array();
		}
		return (string) $value;
	}
	function buildVal($pos){
		if(!isset($this->message[$pos]['type'])){
			$this->message[$pos]['type'] = '';
		}
		$this->debug('in buildVal() for '.$this->message[$pos]['name']."(pos $pos) of type ".$this->message[$pos]['type']);
		if($this->message[$pos]['children'] != ''){
			$this->debug('in buildVal, there are children');
			$children = explode('|',$this->message[$pos]['children']);
			array_shift($children); 
			if(isset($this->message[$pos]['arrayCols']) && $this->message[$pos]['arrayCols'] != ''){
            	$r=0; 
            	$c=0; 
            	foreach($children as $child_pos){
					$this->debug("in buildVal, got an MD array element: $r, $c");
					$params[$r][] = $this->message[$child_pos]['result'];
				    $c++;
				    if($c == $this->message[$pos]['arrayCols']){
				    	$c = 0;
						$r++;
				    }
                }
			} elseif($this->message[$pos]['type'] == 'array' || $this->message[$pos]['type'] == 'Array'){
                $this->debug('in buildVal, adding array '.$this->message[$pos]['name']);
                foreach($children as $child_pos){
                	$params[] = &$this->message[$child_pos]['result'];
                }
            } elseif($this->message[$pos]['type'] == 'Map' && $this->message[$pos]['type_namespace'] == 'http:
                $this->debug('in buildVal, Java Map '.$this->message[$pos]['name']);
                foreach($children as $child_pos){
                	$kv = explode("|",$this->message[$child_pos]['children']);
                   	$params[$this->message[$kv[1]]['result']] = &$this->message[$kv[2]]['result'];
                }
		    } else {
                $this->debug('in buildVal, adding Java Vector '.$this->message[$pos]['name']);
				if ($this->message[$pos]['type'] == 'Vector' && $this->message[$pos]['type_namespace'] == 'http:
					$notstruct = 1;
				} else {
					$notstruct = 0;
	            }
            	foreach($children as $child_pos){
            		if($notstruct){
            			$params[] = &$this->message[$child_pos]['result'];
            		} else {
            			if (isset($params[$this->message[$child_pos]['name']])) {
            				if ((!is_array($params[$this->message[$child_pos]['name']])) || (!isset($params[$this->message[$child_pos]['name']][0]))) {
            					$params[$this->message[$child_pos]['name']] = array($params[$this->message[$child_pos]['name']]);
            				}
            				$params[$this->message[$child_pos]['name']][] = &$this->message[$child_pos]['result'];
            			} else {
					    	$params[$this->message[$child_pos]['name']] = &$this->message[$child_pos]['result'];
					    }
                	}
                }
			}
			if (isset($this->message[$pos]['xattrs'])) {
                $this->debug('in buildVal, handling attributes');
				foreach ($this->message[$pos]['xattrs'] as $n => $v) {
					$params[$n] = $v;
				}
			}
			if (isset($this->message[$pos]['cdata']) && trim($this->message[$pos]['cdata']) != '') {
                $this->debug('in buildVal, handling simpleContent');
            	if (isset($this->message[$pos]['type'])) {
					$params['!'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$pos]['type'], isset($this->message[$pos]['type_namespace']) ? $this->message[$pos]['type_namespace'] : '');
				} else {
					$parent = $this->message[$pos]['parent'];
					if (isset($this->message[$parent]['type']) && ($this->message[$parent]['type'] == 'array') && isset($this->message[$parent]['arrayType'])) {
						$params['!'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$parent]['arrayType'], isset($this->message[$parent]['arrayTypeNamespace']) ? $this->message[$parent]['arrayTypeNamespace'] : '');
					} else {
						$params['!'] = $this->message[$pos]['cdata'];
					}
				}
			}
			return is_array($params) ? $params : array();
		} else {
        	$this->debug('in buildVal, no children, building scalar');
			$cdata = isset($this->message[$pos]['cdata']) ? $this->message[$pos]['cdata'] : '';
        	if (isset($this->message[$pos]['type'])) {
				return $this->decodeSimple($cdata, $this->message[$pos]['type'], isset($this->message[$pos]['type_namespace']) ? $this->message[$pos]['type_namespace'] : '');
			}
			$parent = $this->message[$pos]['parent'];
			if (isset($this->message[$parent]['type']) && ($this->message[$parent]['type'] == 'array') && isset($this->message[$parent]['arrayType'])) {
				return $this->decodeSimple($cdata, $this->message[$parent]['arrayType'], isset($this->message[$parent]['arrayTypeNamespace']) ? $this->message[$parent]['arrayTypeNamespace'] : '');
			}
           	return $this->message[$pos]['cdata'];
		}
	}
}
?><?php
class soap_client extends nusoap_base  {
	var $username = '';
	var $password = '';
	var $authtype = '';
	var $certRequest = array();
	var $requestHeaders = false;	
	var $responseHeaders = '';		
	var $document = '';				
	var $endpoint;
	var $forceEndpoint = '';		
    var $proxyhost = '';
    var $proxyport = '';
	var $proxyusername = '';
	var $proxypassword = '';
    var $xml_encoding = '';			
	var $http_encoding = false;
	var $timeout = 0;				
	var $response_timeout = 30;		
	var $endpointType = '';			
	var $persistentConnection = false;
	var $defaultRpcParams = false;	
	var $request = '';				
	var $response = '';				
	var $responseData = '';			
	var $cookies = array();			
    var $decode_utf8 = true;		
	var $operations = array();		
	var $fault;
	var $faultcode;
	var $faultstring;
	var $faultdetail;
	function soap_client($endpoint,$wsdl = false,$proxyhost = false,$proxyport = false,$proxyusername = false, $proxypassword = false, $timeout = 0, $response_timeout = 30){
		parent::nusoap_base();
		$this->endpoint = $endpoint;
		$this->proxyhost = $proxyhost;
		$this->proxyport = $proxyport;
		$this->proxyusername = $proxyusername;
		$this->proxypassword = $proxypassword;
		$this->timeout = $timeout;
		$this->response_timeout = $response_timeout;
		if($wsdl){
			if (is_object($endpoint) && (get_class($endpoint) == 'wsdl')) {
				$this->wsdl = $endpoint;
				$this->endpoint = $this->wsdl->wsdl;
				$this->wsdlFile = $this->endpoint;
				$this->debug('existing wsdl instance created from ' . $this->endpoint);
			} else {
				$this->wsdlFile = $this->endpoint;
				$this->debug('instantiating wsdl class with doc: '.$endpoint);
				$this->wsdl =& new wsdl($this->wsdlFile,$this->proxyhost,$this->proxyport,$this->proxyusername,$this->proxypassword,$this->timeout,$this->response_timeout);
			}
			$this->appendDebug($this->wsdl->getDebug());
			$this->wsdl->clearDebug();
			if($errstr = $this->wsdl->getError()){
				$this->debug('got wsdl error: '.$errstr);
				$this->setError('wsdl error: '.$errstr);
			} elseif($this->operations = $this->wsdl->getOperations()){
				$this->debug( 'got '.count($this->operations).' operations from wsdl '.$this->wsdlFile);
				$this->endpointType = 'wsdl';
			} else {
				$this->debug( 'getOperations returned false');
				$this->setError('no operations defined in the WSDL document!');
			}
		} else {
			$this->debug("instantiate SOAP with endpoint at $endpoint");
			$this->endpointType = 'soap';
		}
	}
	function call($operation,$params=array(),$namespace='http:
		$this->operation = $operation;
		$this->fault = false;
		$this->setError('');
		$this->request = '';
		$this->response = '';
		$this->responseData = '';
		$this->faultstring = '';
		$this->faultcode = '';
		$this->opData = array();
		$this->debug("call: operation=$operation, namespace=$namespace, soapAction=$soapAction, rpcParams=$rpcParams, style=$style, use=$use, endpointType=$this->endpointType");
		$this->appendDebug('params=' . $this->varDump($params));
		$this->appendDebug('headers=' . $this->varDump($headers));
		if ($headers) {
			$this->requestHeaders = $headers;
		}
		if($this->endpointType == 'wsdl' && $opData = $this->getOperationData($operation)){
			$this->opData = $opData;
			$this->debug("found operation");
			$this->appendDebug('opData=' . $this->varDump($opData));
			if (isset($opData['soapAction'])) {
				$soapAction = $opData['soapAction'];
			}
			if (! $this->forceEndpoint) {
				$this->endpoint = $opData['endpoint'];
			} else {
				$this->endpoint = $this->forceEndpoint;
			}
			$namespace = isset($opData['input']['namespace']) ? $opData['input']['namespace'] :	$namespace;
			$style = $opData['style'];
			$use = $opData['input']['use'];
			if($namespace != '' && !isset($this->wsdl->namespaces[$namespace])){
				$nsPrefix = 'ns' . rand(1000, 9999);
				$this->wsdl->namespaces[$nsPrefix] = $namespace;
			}
            $nsPrefix = $this->wsdl->getPrefixFromNamespace($namespace);
			if (is_string($params)) {
				$this->debug("serializing param string for WSDL operation $operation");
				$payload = $params;
			} elseif (is_array($params)) {
				$this->debug("serializing param array for WSDL operation $operation");
				$payload = $this->wsdl->serializeRPCParameters($operation,'input',$params);
			} else {
				$this->debug('params must be array or string');
				$this->setError('params must be array or string');
				return false;
			}
            $usedNamespaces = $this->wsdl->usedNamespaces;
			if (isset($opData['input']['encodingStyle'])) {
				$encodingStyle = $opData['input']['encodingStyle'];
			} else {
				$encodingStyle = '';
			}
			$this->appendDebug($this->wsdl->getDebug());
			$this->wsdl->clearDebug();
			if ($errstr = $this->wsdl->getError()) {
				$this->debug('got wsdl error: '.$errstr);
				$this->setError('wsdl error: '.$errstr);
				return false;
			}
		} elseif($this->endpointType == 'wsdl') {
			$this->appendDebug($this->wsdl->getDebug());
			$this->wsdl->clearDebug();
			$this->setError( 'operation '.$operation.' not present.');
			$this->debug("operation '$operation' not present.");
			return false;
		} else {
			$nsPrefix = 'ns' . rand(1000, 9999);
			$payload = '';
			if (is_string($params)) {
				$this->debug("serializing param string for operation $operation");
				$payload = $params;
			} elseif (is_array($params)) {
				$this->debug("serializing param array for operation $operation");
				foreach($params as $k => $v){
					$payload .= $this->serialize_val($v,$k,false,false,false,false,$use);
				}
			} else {
				$this->debug('params must be array or string');
				$this->setError('params must be array or string');
				return false;
			}
			$usedNamespaces = array();
			if ($use == 'encoded') {
				$encodingStyle = 'http:
			} else {
				$encodingStyle = '';
			}
		}
		if ($style == 'rpc') {
			if ($use == 'literal') {
				$this->debug("wrapping RPC request with literal method element");
				if ($namespace) {
					$payload = "<$operation xmlns=\"$namespace\">" . $payload . "</$operation>";
				} else {
					$payload = "<$operation>" . $payload . "</$operation>";
				}
			} else {
				$this->debug("wrapping RPC request with encoded method element");
				if ($namespace) {
					$payload = "<$nsPrefix:$operation xmlns:$nsPrefix=\"$namespace\">" .
								$payload .
								"</$nsPrefix:$operation>";
				} else {
					$payload = "<$operation>" .
								$payload .
								"</$operation>";
				}
			}
		}
		$soapmsg = $this->serializeEnvelope($payload,$this->requestHeaders,$usedNamespaces,$style,$use,$encodingStyle);
		$this->debug("endpoint=$this->endpoint, soapAction=$soapAction, namespace=$namespace, style=$style, use=$use, encodingStyle=$encodingStyle");
		$this->debug('SOAP message length=' . strlen($soapmsg) . ' contents (max 1000 bytes)=' . substr($soapmsg, 0, 1000));
		$return = $this->send($this->getHTTPBody($soapmsg),$soapAction,$this->timeout,$this->response_timeout);
		if($errstr = $this->getError()){
			$this->debug('Error: '.$errstr);
			return false;
		} else {
			$this->return = $return;
			$this->debug('sent message successfully and got a(n) '.gettype($return));
           	$this->appendDebug('return=' . $this->varDump($return));
			if(is_array($return) && isset($return['faultcode'])){
				$this->debug('got fault');
				$this->setError($return['faultcode'].': '.$return['faultstring']);
				$this->fault = true;
				foreach($return as $k => $v){
					$this->$k = $v;
					$this->debug("$k = $v<br>");
				}
				return $return;
			} elseif ($style == 'document') {
				return $return;
			} else {
				if(is_array($return)){
					if(sizeof($return) > 1){
						return $return;
					}
					$return = array_shift($return);
					$this->debug('return shifted value: ');
					$this->appendDebug($this->varDump($return));
           			return $return;
				} else {
					return "";
				}
			}
		}
	}
	function getOperationData($operation){
		if(isset($this->operations[$operation])){
			return $this->operations[$operation];
		}
		$this->debug("No data for operation: $operation");
	}
	function send($msg, $soapaction = '', $timeout=0, $response_timeout=30) {
		$this->checkCookies();
		switch(true){
			case ereg('^http',$this->endpoint):
				$this->debug('transporting via HTTP');
				if($this->persistentConnection == true && is_object($this->persistentConnection)){
					$http =& $this->persistentConnection;
				} else {
					$http = new soap_transport_http($this->endpoint);
					if ($this->persistentConnection) {
						$http->usePersistentConnection();
					}
				}
				$http->setContentType($this->getHTTPContentType(), $this->getHTTPContentTypeCharset());
				$http->setSOAPAction($soapaction);
				if($this->proxyhost && $this->proxyport){
					$http->setProxy($this->proxyhost,$this->proxyport,$this->proxyusername,$this->proxypassword);
				}
                if($this->authtype != '') {
					$http->setCredentials($this->username, $this->password, $this->authtype, array(), $this->certRequest);
				}
				if($this->http_encoding != ''){
					$http->setEncoding($this->http_encoding);
				}
				$this->debug('sending message, length='.strlen($msg));
				if(ereg('^http:',$this->endpoint)){
					$this->responseData = $http->send($msg,$timeout,$response_timeout,$this->cookies);
				} elseif(ereg('^https',$this->endpoint)){
					$this->responseData = $http->sendHTTPS($msg,$timeout,$response_timeout,$this->cookies);
				} else {
					$this->setError('no http/s in endpoint url');
				}
				$this->request = $http->outgoing_payload;
				$this->response = $http->incoming_payload;
				$this->appendDebug($http->getDebug());
				$this->UpdateCookies($http->incoming_cookies);
				if ($this->persistentConnection) {
					$http->clearDebug();
					if (!is_object($this->persistentConnection)) {
						$this->persistentConnection = $http;
					}
				}
				if($err = $http->getError()){
					$this->setError('HTTP Error: '.$err);
					return false;
				} elseif($this->getError()){
					return false;
				} else {
					$this->debug('got response, length='. strlen($this->responseData).' type='.$http->incoming_headers['content-type']);
					return $this->parseResponse($http->incoming_headers, $this->responseData);
				}
			break;
			default:
				$this->setError('no transport found, or selected transport is not yet supported!');
			return false;
			break;
		}
	}
    function parseResponse($headers, $data) {
		$this->debug('Entering parseResponse() for data of length ' . strlen($data) . ' and type ' . $headers['content-type']);
		if (!strstr($headers['content-type'], 'text/xml')) {
			$this->setError('Response not of type text/xml');
			return false;
		}
		if (strpos($headers['content-type'], '=')) {
			$enc = str_replace('"', '', substr(strstr($headers["content-type"], '='), 1));
			$this->debug('Got response encoding: ' . $enc);
			if(eregi('^(ISO-8859-1|US-ASCII|UTF-8)$',$enc)){
				$this->xml_encoding = strtoupper($enc);
			} else {
				$this->xml_encoding = 'US-ASCII';
			}
		} else {
			$this->xml_encoding = 'ISO-8859-1';
		}
		$this->debug('Use encoding: ' . $this->xml_encoding . ' when creating soap_parser');
		$parser = new soap_parser($data,$this->xml_encoding,$this->operation,$this->decode_utf8);
		$this->appendDebug($parser->getDebug());
		if($errstr = $parser->getError()){
			$this->setError( $errstr);
			unset($parser);
			return false;
		} else {
			$this->responseHeaders = $parser->getHeaders();
			$return = $parser->get_response();
            $this->document = $parser->document;
			unset($parser);
			return $return;
		}
	 }
	function setEndpoint($endpoint) {
		$this->forceEndpoint = $endpoint;
	}
	function setHeaders($headers){
		$this->requestHeaders = $headers;
	}
	function getHeaders(){
		return $this->responseHeaders;
	}
	function setHTTPProxy($proxyhost, $proxyport, $proxyusername = '', $proxypassword = '') {
		$this->proxyhost = $proxyhost;
		$this->proxyport = $proxyport;
		$this->proxyusername = $proxyusername;
		$this->proxypassword = $proxypassword;
	}
	function setCredentials($username, $password, $authtype = 'basic', $certRequest = array()) {
		$this->username = $username;
		$this->password = $password;
		$this->authtype = $authtype;
		$this->certRequest = $certRequest;
	}
	function setHTTPEncoding($enc='gzip, deflate'){
		$this->http_encoding = $enc;
	}
	function useHTTPPersistentConnection(){
		$this->persistentConnection = true;
	}
	function getDefaultRpcParams() {
		return $this->defaultRpcParams;
	}
	function setDefaultRpcParams($rpcParams) {
		$this->defaultRpcParams = $rpcParams;
	}
	function getProxy(){
		$r = rand();
		$evalStr = $this->_getProxyClassCode($r);
		eval($evalStr);
		eval("\$proxy = new soap_proxy_$r('');");
		$proxy->endpointType = 'wsdl';
		$proxy->wsdlFile = $this->wsdlFile;
		$proxy->wsdl = $this->wsdl;
		$proxy->operations = $this->operations;
		$proxy->defaultRpcParams = $this->defaultRpcParams;
		$proxy->username = $this->username;
		$proxy->password = $this->password;
		$proxy->authtype = $this->authtype;
		$proxy->proxyhost = $this->proxyhost;
		$proxy->proxyport = $this->proxyport;
		$proxy->proxyusername = $this->proxyusername;
		$proxy->proxypassword = $this->proxypassword;
		$proxy->timeout = $this->timeout;
		$proxy->response_timeout = $this->response_timeout;
		$proxy->http_encoding = $this->http_encoding;
		$proxy->persistentConnection = $this->persistentConnection;
		$proxy->requestHeaders = $this->requestHeaders;
		$proxy->soap_defencoding = $this->soap_defencoding;
		$proxy->endpoint = $this->endpoint;
		$proxy->forceEndpoint = $this->forceEndpoint;
		return $proxy;
	}
	function _getProxyClassCode($r) {
		if ($this->endpointType != 'wsdl') {
			$evalStr = 'A proxy can only be created for a WSDL client';
			$this->setError($evalStr);
			return $evalStr;
		}
		$evalStr = '';
		foreach ($this->operations as $operation => $opData) {
			if ($operation != '') {
				if (sizeof($opData['input']['parts']) > 0) {
					$paramStr = '';
					$paramArrayStr = '';
					$paramCommentStr = '';
					foreach ($opData['input']['parts'] as $name => $type) {
						$paramStr .= "\$$name, ";
						$paramArrayStr .= "'$name' => \$$name, ";
						$paramCommentStr .= "$type \$$name, ";
					}
					$paramStr = substr($paramStr, 0, strlen($paramStr)-2);
					$paramArrayStr = substr($paramArrayStr, 0, strlen($paramArrayStr)-2);
					$paramCommentStr = substr($paramCommentStr, 0, strlen($paramCommentStr)-2);
				} else {
					$paramStr = '';
					$paramCommentStr = 'void';
				}
				$opData['namespace'] = !isset($opData['namespace']) ? 'http:
				$evalStr .= "
	function " . str_replace('.', '__', $operation) . "($paramStr) {
		\$params = array($paramArrayStr);
		return \$this->call('$operation', \$params, '".$opData['namespace']."', '".(isset($opData['soapAction']) ? $opData['soapAction'] : '')."');
	}
	";
				unset($paramStr);
				unset($paramCommentStr);
			}
		}
		$evalStr = 'class soap_proxy_'.$r.' extends soap_client {
	'.$evalStr.'
}';
		return $evalStr;
	}
	function getProxyClassCode() {
		$r = rand();
		return $this->_getProxyClassCode($r);
	}
	function getHTTPBody($soapmsg) {
		return $soapmsg;
	}
	function getHTTPContentType() {
		return 'text/xml';
	}
	function getHTTPContentTypeCharset() {
		return $this->soap_defencoding;
	}
    function decodeUTF8($bool){
		$this->decode_utf8 = $bool;
		return true;
    }
	function setCookie($name, $value) {
		if (strlen($name) == 0) {
			return false;
		}
		$this->cookies[] = array('name' => $name, 'value' => $value);
		return true;
	}
	function getCookies() {
		return $this->cookies;
	}
	function checkCookies() {
		if (sizeof($this->cookies) == 0) {
			return true;
		}
		$this->debug('checkCookie: check ' . sizeof($this->cookies) . ' cookies');
		$curr_cookies = $this->cookies;
		$this->cookies = array();
		foreach ($curr_cookies as $cookie) {
			if (! is_array($cookie)) {
				$this->debug('Remove cookie that is not an array');
				continue;
			}
			if ((isset($cookie['expires'])) && (! empty($cookie['expires']))) {
				if (strtotime($cookie['expires']) > time()) {
					$this->cookies[] = $cookie;
				} else {
					$this->debug('Remove expired cookie ' . $cookie['name']);
				}
			} else {
				$this->cookies[] = $cookie;
			}
		}
		$this->debug('checkCookie: '.sizeof($this->cookies).' cookies left in array');
		return true;
	}
	function UpdateCookies($cookies) {
		if (sizeof($this->cookies) == 0) {
			if (sizeof($cookies) > 0) {
				$this->debug('Setting new cookie(s)');
				$this->cookies = $cookies;
			}
			return true;
		}
		if (sizeof($cookies) == 0) {
			return true;
		}
		foreach ($cookies as $newCookie) {
			if (!is_array($newCookie)) {
				continue;
			}
			if ((!isset($newCookie['name'])) || (!isset($newCookie['value']))) {
				continue;
			}
			$newName = $newCookie['name'];
			$found = false;
			for ($i = 0; $i < count($this->cookies); $i++) {
				$cookie = $this->cookies[$i];
				if (!is_array($cookie)) {
					continue;
				}
				if (!isset($cookie['name'])) {
					continue;
				}
				if ($newName != $cookie['name']) {
					continue;
				}
				$newDomain = isset($newCookie['domain']) ? $newCookie['domain'] : 'NODOMAIN';
				$domain = isset($cookie['domain']) ? $cookie['domain'] : 'NODOMAIN';
				if ($newDomain != $domain) {
					continue;
				}
				$newPath = isset($newCookie['path']) ? $newCookie['path'] : 'NOPATH';
				$path = isset($cookie['path']) ? $cookie['path'] : 'NOPATH';
				if ($newPath != $path) {
					continue;
				}
				$this->cookies[$i] = $newCookie;
				$found = true;
				$this->debug('Update cookie ' . $newName . '=' . $newCookie['value']);
				break;
			}
			if (! $found) {
				$this->debug('Add cookie ' . $newName . '=' . $newCookie['value']);
				$this->cookies[] = $newCookie;
			}
		}
		return true;
	}
}
?>

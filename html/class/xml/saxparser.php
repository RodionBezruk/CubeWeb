<?php
class SaxParser
{
    var $level;
    var $parser;
    var $isCaseFolding;
    var $targetEncoding;
    var $tagHandlers = array();
    var $tags = array();
    var $xmlInput;
    var $errors = array();
    function SaxParser(&$input)
    {
        $this->level = 0;
        $this->parser = xml_parser_create('UTF-8');
        xml_set_object($this->parser, $this);
        $this->input =& $input;
        $this->setCaseFolding(false);
        $this->useUtfEncoding();
        xml_set_element_handler($this->parser, 'handleBeginElement','handleEndElement');
        xml_set_character_data_handler($this->parser, 'handleCharacterData');
        xml_set_processing_instruction_handler($this->parser, 'handleProcessingInstruction');
        xml_set_default_handler($this->parser, 'handleDefault');
        xml_set_unparsed_entity_decl_handler($this->parser, 'handleUnparsedEntityDecl');
        xml_set_notation_decl_handler($this->parser, 'handleNotationDecl');
        xml_set_external_entity_ref_handler($this->parser, 'handleExternalEntityRef');
    }
    function getCurrentLevel()
    {
        return $this->level;
    }
    function setCaseFolding($isCaseFolding)
    {
        assert(is_bool($isCaseFolding));
        $this->isCaseFolding = $isCaseFolding;
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, $this->isCaseFolding);
    }
    function useIsoEncoding()
    {
        $this->targetEncoding = 'ISO-8859-1';
        xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->targetEncoding);
    }
    function useAsciiEncoding()
    {
        $this->targetEncoding = 'US-ASCII';
        xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->targetEncoding);
    }
    function useUtfEncoding()
    {
        $this->targetEncoding = 'UTF-8';
        xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->targetEncoding);
    }
    function getCurrentTag()
    {
        return $this->tags[count($this->tags) - 1];
    }
    function getParentTag()
    {
        if (isset($this->tags[count($this->tags) - 2])) {
            return $this->tags[count($this->tags) - 2];
        }
        return false;
    }
    function parse()
    {
        if (!is_resource($this->input)) {
            if (!xml_parse($this->parser, $this->input)) {
                $this->setErrors($this->getXmlError());
                return false;
            }
        } else {
            while ($data = fread($this->input, 4096)) {
                if (!xml_parse($this->parser, str_replace("'", "&apos;", $data), feof($this->input))) {
                    $this->setErrors($this->getXmlError());
                    fclose($this->input);
                    return false;
                }
            }
            fclose($this->input);
        }
        return true;
    }
    function free()
    {
        xml_parser_free($this->parser);
        if (!method_exists($this, '__destruct')) {
             unset($this);
        } else {
            $this->__destruct();
        }
    }
    function getXmlError()
    {
        return sprintf("XmlParse error: %s at line %d", xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser));
    }
    function addTagHandler(&$tagHandler)
    {
        $name = $tagHandler->getName();
        if (is_array($name)) {
            foreach ($name as $n) {
                $this->tagHandlers[$n] =& $tagHandler;
            }
        } else {
            $this->tagHandlers[$name] =& $tagHandler;
        }
    }
    function handleBeginElement($parser, $tagName, $attributesArray)
    {
        array_push($this->tags, $tagName);
        $this->level++;
        if (isset($this->tagHandlers[$tagName]) && is_subclass_of($this->tagHandlers[$tagName], 'xmltaghandler')) {
            $this->tagHandlers[$tagName]->handleBeginElement($this, $attributesArray);
        } else {
            $this->handleBeginElementDefault($parser, $tagName, $attributesArray);
        }
    }
    function handleEndElement($parser, $tagName)
    {
        array_pop($this->tags);
        if (isset($this->tagHandlers[$tagName]) && is_subclass_of($this->tagHandlers[$tagName], 'xmltaghandler')) {
            $this->tagHandlers[$tagName]->handleEndElement($this);
        } else {
            $this->handleEndElementDefault($parser, $tagName);
        }
        $this->level--;
    }
    function handleCharacterData($parser, $data)
    {
        $tagHandler =& $this->tagHandlers[$this->getCurrentTag()];
        if (isset($tagHandler) && is_subclass_of($tagHandler, 'xmltaghandler')) {
            $tagHandler->handleCharacterData($this, $data);
        } else {
            $this->handleCharacterDataDefault($parser, $data);
        }
    }
    function handleProcessingInstruction($parser, &$target, &$data)
    {
    }
    function handleDefault($parser, $data)
    {
    }
    function handleUnparsedEntityDecl($parser, $entityName, $base, $systemId, $publicId, $notationName)
    {
    }
    function handleNotationDecl($parser, $notationName, $base, $systemId, $publicId)
    {
    }
    function handleExternalEntityRef($parser, $openEntityNames, $base, $systemId, $publicId)
    {
    }
    function handleBeginElementDefault($parser, $tagName, $attributesArray)
    {
    }
    function handleEndElementDefault($parser, $tagName)
    {
    }
    function handleCharacterDataDefault($parser, $data)
    {
    }
    function setErrors($error)
    {
        $this->errors[] = trim($error);
    }
    function &getErrors($ashtml = true)
    {
        if (!$ashtml) {
            return $this->errors;
        } else {
            $ret = '';
            if (count($this->errors) > 0) {
                foreach ($this->errors as $error) {
                    $ret .= $error.'<br />';
                }
            }
            return $ret;
        }
    }
}
?>

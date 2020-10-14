<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH.'/class/xml/saxparser.php';
require_once XOOPS_ROOT_PATH.'/class/xml/xmltaghandler.php';
class XoopsXmlRpcParser extends SaxParser
{
    var $_param;
    var $_methodName;
    var $_tempName;
    var $_tempValue;
    var $_tempMember;
    var $_tempStruct;
    var $_tempArray;
    var $_workingLevel = array();
    function XoopsXmlRpcParser(&$input)
    {
        $this->SaxParser($input);
        $this->addTagHandler(new RpcMethodNameHandler());
        $this->addTagHandler(new RpcIntHandler());
        $this->addTagHandler(new RpcDoubleHandler());
        $this->addTagHandler(new RpcBooleanHandler());
        $this->addTagHandler(new RpcStringHandler());
        $this->addTagHandler(new RpcDateTimeHandler());
        $this->addTagHandler(new RpcBase64Handler());
        $this->addTagHandler(new RpcNameHandler());
        $this->addTagHandler(new RpcValueHandler());
        $this->addTagHandler(new RpcMemberHandler());
        $this->addTagHandler(new RpcStructHandler());
        $this->addTagHandler(new RpcArrayHandler());
    }
    function setTempName($name)
    {
        $this->_tempName[$this->getWorkingLevel()] = $name;
    }
    function getTempName()
    {
        return $this->_tempName[$this->getWorkingLevel()];
    }
    function setTempValue($value)
    {
        if (is_array($value)) {
            settype($this->_tempValue, 'array');
            foreach ($value as $k => $v) {
                $this->_tempValue[$k] = $v;
            }
        } elseif (is_string($value)) {
            if (isset($this->_tempValue)) {
                if (is_string($this->_tempValue)) {
                    $this->_tempValue .= $value;
                }
            } else {
                $this->_tempValue = $value;
            }
        } else {
            $this->_tempValue = $value;
        }
    }
    function getTempValue()
    {
        return $this->_tempValue;
    }
    function resetTempValue()
    {
        unset($this->_tempValue);
    }
    function setTempMember($name, $value)
    {
        $this->_tempMember[$this->getWorkingLevel()][$name] = $value;
    }
    function getTempMember()
    {
        return $this->_tempMember[$this->getWorkingLevel()];
    }
    function resetTempMember()
    {
        $this->_tempMember[$this->getCurrentLevel()] = array();
    }
    function setWorkingLevel()
    {
        array_push($this->_workingLevel, $this->getCurrentLevel());
    }
    function getWorkingLevel()
    {
        return $this->_workingLevel[count($this->_workingLevel) - 1];
    }
    function releaseWorkingLevel()
    {
        array_pop($this->_workingLevel);
    }
    function setTempStruct($member)
    {
        $key = key($member);
        $this->_tempStruct[$this->getWorkingLevel()][$key] = $member[$key];
    }
    function getTempStruct()
    {
        return $this->_tempStruct[$this->getWorkingLevel()];
    }
    function resetTempStruct()
    {
        $this->_tempStruct[$this->getCurrentLevel()] = array();
    }
    function setTempArray($value)
    {
        $this->_tempArray[$this->getWorkingLevel()][] = $value;
    }
    function getTempArray()
    {
        return $this->_tempArray[$this->getWorkingLevel()];
    }
    function resetTempArray()
    {
        $this->_tempArray[$this->getCurrentLevel()] = array();
    }
    function setMethodName($methodName)
    {
        $this->_methodName = $methodName;
    }
    function getMethodName()
    {
        return $this->_methodName;
    }
    function setParam($value)
    {
        $this->_param[] = $value;
    }
    function &getParam()
    {
        return $this->_param;
    }
}
class RpcMethodNameHandler extends XmlTagHandler
{
    function getName()
    {
        return 'methodName';
    }
    function handleCharacterData(&$parser, &$data)
    {
        $parser->setMethodName($data);
    }
}
class RpcIntHandler extends XmlTagHandler
{
    function getName()
    {
        return array('int', 'i4');
    }
    function handleCharacterData(&$parser, &$data)
    {
        $parser->setTempValue(intval($data));
    }
}
class RpcDoubleHandler extends XmlTagHandler
{
    function getName()
    {
        return 'double';
    }
    function handleCharacterData(&$parser, &$data)
    {
        $data = (float)$data;
        $parser->setTempValue($data);
    }
}
class RpcBooleanHandler extends XmlTagHandler
{
    function getName()
    {
        return 'boolean';
    }
    function handleCharacterData(&$parser, &$data)
    {
        $data = (boolean)$data;
        $parser->setTempValue($data);
    }
}
class RpcStringHandler extends XmlTagHandler
{
    function getName()
    {
        return 'string';
    }
    function handleCharacterData(&$parser, &$data)
    {
        $parser->setTempValue(strval($data));
    }
}
class RpcDateTimeHandler extends XmlTagHandler
{
    function getName()
    {
        return 'dateTime.iso8601';
    }
    function handleCharacterData(&$parser, &$data)
    {
        $matches = array();
        if (!preg_match("/^([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $data, $matches)) {
            $parser->setTempValue(time());
        } else {
            $parser->setTempValue(gmmktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]));
        }
    }
}
class RpcBase64Handler extends XmlTagHandler
{
    function getName()
    {
        return 'base64';
    }
    function handleCharacterData(&$parser, &$data)
    {
        $parser->setTempValue(base64_decode($data));
    }
}
class RpcNameHandler extends XmlTagHandler
{
    function getName()
    {
        return 'name';
    }
    function handleCharacterData(&$parser, &$data)
    {
        switch ($parser->getParentTag()) {
        case 'member':
            $parser->setTempName($data);
            break;
        default:
            break;
        }
    }
}
class RpcValueHandler extends XmlTagHandler
{
    function getName()
    {
        return 'value';
    }
    function handleCharacterData(&$parser, &$data)
    {
        switch ($parser->getParentTag()) {
        case 'member':
            $parser->setTempValue($data);
            break;
        case 'data':
        case 'array':
            $parser->setTempValue($data);
            break;
        default:
            break;
        }
    }
    function handleBeginElement(&$parser, &$attributes)
    {
    }
    function handleEndElement(&$parser)
    {
        switch ($parser->getCurrentTag()) {
        case 'member':
            $parser->setTempMember($parser->getTempName(), $parser->getTempValue());
            break;
        case 'array':
        case 'data':
            $parser->setTempArray($parser->getTempValue());
            break;
        default:
            $parser->setParam($parser->getTempValue());
            break;
        }
        $parser->resetTempValue();
    }
}
class RpcMemberHandler extends XmlTagHandler
{
    function getName()
    {
        return 'member';
    }
    function handleBeginElement(&$parser, &$attributes)
    {
        $parser->setWorkingLevel();
        $parser->resetTempMember();
    }
    function handleEndElement(&$parser)
    {
        $member =& $parser->getTempMember();
        $parser->releaseWorkingLevel();
        $parser->setTempStruct($member);
    }
}
class RpcArrayHandler extends XmlTagHandler
{
    function getName()
    {
        return 'array';
    }
    function handleBeginElement(&$parser, &$attributes)
    {
        $parser->setWorkingLevel();
        $parser->resetTempArray();
    }
    function handleEndElement(&$parser)
    {
        $parser->setTempValue($parser->getTempArray());
        $parser->releaseWorkingLevel();
    }
}
class RpcStructHandler extends XmlTagHandler
{
    function getName()
    {
        return 'struct';
    }
    function handleBeginElement(&$parser, &$attributes)
    {
        $parser->setWorkingLevel();
        $parser->resetTempStruct();
    }
    function handleEndElement(&$parser)
    {
        $parser->setTempValue($parser->getTempStruct());
        $parser->releaseWorkingLevel();
    }
}
?>

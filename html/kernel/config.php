<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
require_once XOOPS_ROOT_PATH.'/kernel/configoption.php';
require_once XOOPS_ROOT_PATH.'/kernel/configitem.php';
class XoopsConfigHandler
{
    var $_cHandler;
    var $_oHandler;
	var $_cachedConfigs = array();
    function XoopsConfigHandler(&$db)
    {
        $this->_cHandler =& new XoopsConfigItemHandler($db);
        $this->_oHandler =& new XoopsConfigOptionHandler($db);
    }
    function &createConfig()
    {
        $ret =& $this->_cHandler->create();
        return $ret;
    }
    function &getConfig($id, $withoptions = false)
    {
        $config =& $this->_cHandler->get($id);
        if ($withoptions == true) {
            $config->setConfOptions($this->getConfigOptions(new Criteria('conf_id', $id)));
        }
        return $config;
    }
    function insertConfig(&$config)
    {
        if (!$this->_cHandler->insert($config)) {
            return false;
        }
        $options =& $config->getConfOptions();
        $count = count($options);
		$conf_id = $config->getVar('conf_id');
        for ($i = 0; $i < $count; $i++) {
            $options[$i]->setVar('conf_id', $conf_id);
            if (!$this->_oHandler->insert($options[$i])) {
				echo $options[$i]->getErrors();
			}
        }
		if (!empty($this->_cachedConfigs[$config->getVar('conf_modid')][$config->getVar('conf_catid')])) {
			unset ($this->_cachedConfigs[$config->getVar('conf_modid')][$config->getVar('conf_catid')]);
		}
        return true;
    }
    function deleteConfig(&$config)
    {
        if (!$this->_cHandler->delete($config)) {
            return false;
        }
        $options =& $config->getConfOptions();
        $count = count($options);
        if ($count == 0) {
            $options =& $this->getConfigOptions(new Criteria('conf_id', $config->getVar('conf_id')));
            $count = count($options);
        }
        if (is_array($options) && $count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $this->_oHandler->delete($options[$i]);
            }
        }
		if (!empty($this->_cachedConfigs[$config->getVar('conf_modid')][$config->getVar('conf_catid')])) {
			unset ($this->_cachedConfigs[$config->getVar('conf_modid')][$config->getVar('conf_catid')]);
		}
        return true;
    }
    function &getConfigs($criteria = null, $id_as_key = false, $with_options = false)
    {
        $config =& $this->_cHandler->getObjects($criteria, $id_as_key);
        return $config;
    }
    function getConfigCount($criteria = null)
    {
        return $this->_cHandler->getCount($criteria);
    }
    function &getConfigsByCat($category, $module = 0)
    {
        static $_cachedConfigs;
		if (!empty($_cachedConfigs[$module][$category])) {
			return $_cachedConfigs[$module][$category];
		} else {
        	$ret = array();
        	$criteria = new CriteriaCompo(new Criteria('conf_modid', intval($module)));
        	if (!empty($category)) {
            	$criteria->add(new Criteria('conf_catid', intval($category)));
        	}
        	$configs =& $this->getConfigs($criteria, true);
			if (is_array($configs)) {
            	foreach (array_keys($configs) as $i) {
                	$ret[$configs[$i]->getVar('conf_name')] = $configs[$i]->getConfValueForOutput();
            	}
        	}
			$_cachedConfigs[$module][$category] =& $ret;
        	return $ret;
		}
    }
	function &getConfigsByDirname($dirname, $category = 0)
	{
		$ret = null;;
		$handler =& xoops_gethandler('module');;
		$module =& $handler->getByDirname($dirname);
		if (!is_object($module)) {
			return $ret;
		}
		$ret =& $this->getConfigsByCat($category, $module->get('mid'));
		return $ret;
	}
    function &createConfigOption(){
        $ret =& $this->_oHandler->create();
        return $ret;
    }
    function &getConfigOption($id)
    {
        $ret =& $this->_oHandler->get($id);
        return $ret;
    }
    function &getConfigOptions($criteria = null, $id_as_key = false)
    {
        $ret =& $this->_oHandler->getObjects($criteria, $id_as_key);
        return $ret;
    }
    function getConfigOptionsCount($criteria = null)
    {
        return $this->_oHandler->getCount($criteria);
    }
    function &getConfigList($conf_modid, $conf_catid = 0)
    {
		if (!empty($this->_cachedConfigs[$conf_modid][$conf_catid])) {
			return $this->_cachedConfigs[$conf_modid][$conf_catid];
		} else {
        	$criteria = new CriteriaCompo(new Criteria('conf_modid', $conf_modid));
        	if (empty($conf_catid)) {
            	$criteria->add(new Criteria('conf_catid', $conf_catid));
        	}
        	$configs =& $this->_cHandler->getObjects($criteria);
        	$confcount = count($configs);
        	$ret = array();
        	for ($i = 0; $i < $confcount; $i++) {
            	$ret[$configs[$i]->getVar('conf_name')] = $configs[$i]->getConfValueForOutput();
        	}
			$this->_cachedConfigs[$conf_modid][$conf_catid] =& $ret;
        	return $ret;
		}
    }
}
?>

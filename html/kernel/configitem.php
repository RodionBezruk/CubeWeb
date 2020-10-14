<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
define('XOOPS_CONF', 1);
define('XOOPS_CONF_USER', 2);
define('XOOPS_CONF_METAFOOTER', 3);
define('XOOPS_CONF_CENSOR', 4);
define('XOOPS_CONF_SEARCH', 5);
define('XOOPS_CONF_MAILER', 6);
class XoopsConfigItem extends XoopsObject
{
    var $_confOptions = array();
    function XoopsConfigItem()
    {
        $this->initVar('conf_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('conf_modid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('conf_catid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('conf_name', XOBJ_DTYPE_OTHER);
        $this->initVar('conf_title', XOBJ_DTYPE_TXTBOX);
        $this->initVar('conf_value', XOBJ_DTYPE_TXTAREA);
        $this->initVar('conf_desc', XOBJ_DTYPE_OTHER);
        $this->initVar('conf_formtype', XOBJ_DTYPE_OTHER);
        $this->initVar('conf_valuetype', XOBJ_DTYPE_OTHER);
        $this->initVar('conf_order', XOBJ_DTYPE_INT);
    }
    function getTitle()
    {
		return defined($this->get('conf_title')) ? constant($this->get('conf_title')) : $this->get('conf_title');
	}
	function getDesc()
	{
		return defined($this->get('conf_desc')) ? constant($this->get('conf_desc')) : null;
	}
	function &getOptionItems()
	{
		$optionArr = array();
		$handler =& xoops_gethandler('config');
		$optionArr =& $handler->getConfigOptions(new Criteria('conf_id', $this->get('conf_id')));
		return $optionArr;
	}
    function &getConfValueForOutput()
    {
        $ret = null;
        switch ($this->getVar('conf_valuetype')) {
        case 'int':
            $ret = intval($this->getVar('conf_value', 'N'));
            break;
        case 'array':
            $ret = unserialize($this->getVar('conf_value', 'N'));
			break;
        case 'float':
            $value = $this->getVar('conf_value', 'N');
            $ret = floatval($value);
            break;
        case 'textarea':
            $ret = $this->getVar('conf_value');
            break;
        default:
            $ret = $this->getVar('conf_value', 'N');
            break;
        }
        return $ret;
    }
    function setConfValueForInput(&$value, $force_slash = false)
    {
        switch($this->getVar('conf_valuetype')) {
        case 'array':
            if (!is_array($value)) {
                $value = explode('|', trim($value));
            }
            $this->setVar('conf_value', serialize($value), $force_slash);
            break;
        case 'text':
            $this->setVar('conf_value', trim($value), $force_slash);
            break;
        default:
            $this->setVar('conf_value', $value, $force_slash);
            break;
        }
    }
    function setConfOptions($option)
    {
        if (is_array($option)) {
            $count = count($option);
            for ($i = 0; $i < $count; $i++) {
                $this->setConfOptions($option[$i]);
            }
        } else {
            if(is_object($option)) {
                $this->_confOptions[] =& $option;
            }
        }
    }
    function &getConfOptions()
    {
        return $this->_confOptions;
    }
	function isEqual(&$config)
	{
		$flag = true;
		$flag &= ($this->get('conf_modid') == $config->get('conf_modid'));
		$flag &= ($this->get('conf_catid') == $config->get('conf_catid'));
		$flag &= ($this->get('conf_name') == $config->get('conf_name'));
		$flag &= ($this->get('conf_title') == $config->get('conf_title'));
		$flag &= ($this->get('conf_desc') == $config->get('conf_desc'));
		$flag &= ($this->get('conf_formtype') == $config->get('conf_formtype'));
		$flag &= ($this->get('conf_valuetype') == $config->get('conf_valuetype'));
		$thisOptions =& $this->getOptionItems();
		$hisOptions =& $config->getConfOptions();
		if (count($thisOptions) == count($hisOptions)) {
			foreach (array_keys($thisOptions) as $t_thiskey) {
				$t_okFlag = false;
				foreach (array_keys($hisOptions) as $t_hiskey) {
					if ($thisOptions[$t_thiskey]->isEqual($hisOptions[$t_hiskey])) {
						$t_okFlag = true;
					}
				}
				if (!$t_okFlag) {
					$flag = false;
					break;
				}
			}
		}
		else {
			$flag = false;
		}
		return $flag;
	}
	function loadFromConfigInfo($mid, &$configInfo, $order = null)
	{
		$this->set('conf_modid', $mid);
		$this->set('conf_catid', 0);
		$this->set('conf_name', $configInfo['name']);
		$this->set('conf_title', $configInfo['title'], true);
		if (isset($configInfo['description'])) {
			$this->set('conf_desc', $configInfo['description'], true);
		}
		$this->set('conf_formtype', $configInfo['formtype'], true);
		$this->set('conf_valuetype', $configInfo['valuetype'], true);
		$this->setConfValueForInput($configInfo['default'], true);
		if (isset($configInfo['order'])) {
			$this->set('conf_order', $configInfo['order']);
		}
		else {
			$this->set('conf_order', $order);
		}
		if (isset($configInfo['options']) && is_array($configInfo['options'])) {
			$configHandler =& xoops_gethandler('config');
			foreach ($configInfo['options'] as $key => $value) {
				$configOption =& $configHandler->createConfigOption();
				$configOption->setVar('confop_name', $key, true);
				$configOption->setVar('confop_value', $value, true);
				$this->setConfOptions($configOption);
				unset($configOption);
			}
		}
	}
}
class XoopsConfigItemHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $config =& new XoopsConfigItem();
        if ($isNew) {
            $config->setNew();
        }
        return $config;
    }
    function &get($id)
    {
        $ret = false;
        $id = intval($id);
        if ($id > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('config').' WHERE conf_id='.$id;
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                    $myrow = $this->db->fetchArray($result);
                        $config =& new XoopsConfigItem();
                    $config->assignVars($myrow);
                        $ret =& $config;
                }
            }
        }
        return $ret;
    }
    function insert(&$config)
    {
        if (strtolower(get_class($config)) != 'xoopsconfigitem') {
            return false;
        }
        if (!$config->isDirty()) {
            return true;
        }
        if (!$config->cleanVars()) {
            return false;
        }
        foreach ($config->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($config->isNew()) {
            $conf_id = $this->db->genId('config_conf_id_seq');
            $sql = sprintf("INSERT INTO %s (conf_id, conf_modid, conf_catid, conf_name, conf_title, conf_value, conf_desc, conf_formtype, conf_valuetype, conf_order) VALUES (%u, %u, %u, %s, %s, %s, %s, %s, %s, %u)", $this->db->prefix('config'), $conf_id, $conf_modid, $conf_catid, $this->db->quoteString($conf_name), $this->db->quoteString($conf_title), $this->db->quoteString($conf_value), $this->db->quoteString($conf_desc), $this->db->quoteString($conf_formtype), $this->db->quoteString($conf_valuetype), $conf_order);
        } else {
            $sql = sprintf("UPDATE %s SET conf_modid = %u, conf_catid = %u, conf_name = %s, conf_title = %s, conf_value = %s, conf_desc = %s, conf_formtype = %s, conf_valuetype = %s, conf_order = %u WHERE conf_id = %u", $this->db->prefix('config'), $conf_modid, $conf_catid, $this->db->quoteString($conf_name), $this->db->quoteString($conf_title), $this->db->quoteString($conf_value), $this->db->quoteString($conf_desc), $this->db->quoteString($conf_formtype), $this->db->quoteString($conf_valuetype), $conf_order, $conf_id);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        if (empty($conf_id)) {
            $conf_id = $this->db->getInsertId();
        }
        $config->assignVar('conf_id', $conf_id);
        return true;
    }
    function delete(&$config)
    {
        if (strtolower(get_class($config)) != 'xoopsconfigitem') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE conf_id = %u", $this->db->prefix('config'), $config->getVar('conf_id'));
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('config');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            $sql .= ' ORDER BY conf_order ASC';
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $config =& new XoopsConfigItem();
            $config->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $config;
            } else {
                $ret[$myrow['conf_id']] =& $config;
            }
            unset($config);
        }
        return $ret;
    }
    function getCount($criteria = null)
    {
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('config');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        $result =& $this->db->query($sql);
        if (!$result) {
            return false;
        }
        list($count) = $this->db->fetchRow($result);
        return $count;
    }
}
?>

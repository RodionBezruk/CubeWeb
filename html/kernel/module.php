<?php
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}
class XoopsModule extends XoopsObject
{
    var $modinfo;
    var $adminmenu;
    function XoopsModule()
    {
        $this->XoopsObject();
        $this->initVar('mid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 150);
        $this->initVar('version', XOBJ_DTYPE_INT, 100, false);
        $this->initVar('last_update', XOBJ_DTYPE_INT, null, false);
        $this->initVar('weight', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('isactive', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('dirname', XOBJ_DTYPE_OTHER, null, true);
        $this->initVar('hasmain', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasadmin', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hassearch', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasconfig', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hascomments', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasnotification', XOBJ_DTYPE_INT, 0, false);
    }
    function loadInfoAsVar($dirname, $verbose = true)
    {
        if ( !isset($this->modinfo) ) {
            $this->loadInfo($dirname, $verbose);
        }
        $this->setVar('name', $this->modinfo['name'], true);
        $this->setVar('version', Legacy_Utils::convertVersionFromModinfoToInt($this->modinfo['version']));
        $this->setVar('dirname', $this->modinfo['dirname'], true);
        $hasmain = (isset($this->modinfo['hasMain']) && $this->modinfo['hasMain'] == 1) ? 1 : 0;
        $hasadmin = (isset($this->modinfo['hasAdmin']) && $this->modinfo['hasAdmin'] == 1) ? 1 : 0;
        $hassearch = (isset($this->modinfo['hasSearch']) && $this->modinfo['hasSearch'] == 1) ? 1 : 0;
        $hasconfig = ((isset($this->modinfo['config']) && is_array($this->modinfo['config'])) || !empty($this->modinfo['hasComments'])) ? 1 : 0;
        $hascomments = (isset($this->modinfo['hasComments']) && $this->modinfo['hasComments'] == 1) ? 1 : 0;
        $hasnotification = (isset($this->modinfo['hasNotification']) && $this->modinfo['hasNotification'] == 1) ? 1 : 0;
        $this->setVar('hasmain', $hasmain);
        $this->setVar('hasadmin', $hasadmin);
        $this->setVar('hassearch', $hassearch);
        $this->setVar('hasconfig', $hasconfig);
        $this->setVar('hascomments', $hascomments);
        $this->setVar('hasnotification', $hasnotification);
    }
    function &getInfo($name=null)
    {
        if ( !isset($this->modinfo) ) {
            $this->loadInfo($this->getVar('dirname'));
        }
        if ( isset($name) ) {
            if ( isset($this->modinfo[$name]) ) {
                return $this->modinfo[$name];
            }
            $ret = false;
            return $ret;
        }
        return $this->modinfo;
    }
    function mainLink()
    {
        if ( $this->getVar('hasmain') == 1 ) {
            $ret = '<a href="'.XOOPS_URL.'/modules/'.$this->getVar('dirname').'/">'.$this->getVar('name').'</a>';
            return $ret;
        }
        return false;
    }
    function &subLink()
    {
        $ret = array();
        if ( $this->getInfo('sub') && is_array($this->getInfo('sub')) ) {
            foreach ( $this->getInfo('sub') as $submenu ) {
                $ret[] = array('name' => $submenu['name'], 'url' => $submenu['url']);
            }
        }
        return $ret;
    }
    function loadAdminMenu()
    {
        if ($this->getInfo('adminmenu') && $this->getInfo('adminmenu') != '' && file_exists(XOOPS_ROOT_PATH.'/modules/'.$this->getVar('dirname').'/'.$this->getInfo('adminmenu'))) {
            include XOOPS_ROOT_PATH.'/modules/'.$this->getVar('dirname').'/'.$this->getInfo('adminmenu');
            $this->adminmenu =& $adminmenu;
        }
    }
    function &getAdminMenu()
    {
        if ( !isset($this->adminmenu) ) {
            $this->loadAdminMenu();
        }
        return $this->adminmenu;
    }
    function loadInfo($dirname, $verbose = true)
    {
        global $xoopsConfig;
		if (!empty($this->modinfo)) {
			return;
		}
		$root =& XCube_Root::getSingleton();
		$root->mLanguageManager->loadModinfoMessageCatalog($dirname);
        if (file_exists(XOOPS_ROOT_PATH.'/modules/'.$dirname.'/xoops_version.php')) {
            include XOOPS_ROOT_PATH.'/modules/'.$dirname.'/xoops_version.php';
        } else {
            if (false != $verbose) {
                echo "Module File for $dirname Not Found!";
            }
            return;
        }
        $this->modinfo =& $modversion;
		if (isset($this->modinfo['version'])) {
			$this->modinfo['version'] = floatval($this->modinfo['version']);
		} else {
		    $this->modinfo['version'] = 0;
		}
    }
    function &search($term = '', $andor = 'AND', $limit = 0, $offset = 0, $userid = 0)
    {
        $ret = false;
        if ($this->getVar('hassearch') != 1) {
            return $ret;
        }
        $search =& $this->getInfo('search');
        if ($this->getVar('hassearch') != 1 || !isset($search['file']) || !isset($search['func']) || $search['func'] == '' || $search['file'] == '') {
            return $ret;
        }
        if (file_exists(XOOPS_ROOT_PATH."/modules/".$this->getVar('dirname').'/'.$search['file'])) {
            include_once XOOPS_ROOT_PATH.'/modules/'.$this->getVar('dirname').'/'.$search['file'];
        } else {
            return $ret;
        }
        if (function_exists($search['func'])) {
            $func = $search['func'];
            $ret = $func($term, $andor, $limit, $offset, $userid);
		}
        return $ret;
	}
    function getRenderedVersion()
    {
		return sprintf("%01.2f", $this->get('version') / 100);
	}
	function hasHelp()
	{
		$info =& $this->getInfo();
		if (isset($info['cube_style']) && $info['cube_style'] != false && isset($info['help']) && strlen($info['help']) > 0) {
			return true;
		}
		return false;
	}
	function getHelp()
	{
		if ($this->hasHelp()) {
			return $this->modinfo['help'];
		}
		return null;
	}
	function hasNeedUpdate()
	{
		$info =& $this->getInfo();
		return ($this->get('version') < Legacy_Utils::convertVersionFromModinfoToInt($info['version']));
	}
    function mid()
    {
        return $this->getVar('mid');
    }
    function dirname()
    {
        return $this->getVar('dirname');
    }
    function name()
    {
        return $this->getVar('name');
    }
    function &getByDirName($dirname)
    {
        $modhandler =& xoops_gethandler('module');
        $ret =& $modhandler->getByDirname($dirname);
        return $ret;
    }
}
class XoopsModuleHandler extends XoopsObjectHandler
{
	var $_tmp;	
    var $_cachedModule_mid = array();
    var $_cachedModule_dirname = array();
    function &create($isNew = true)
    {
        $module =& new XoopsModule();
        if ($isNew) {
            $module->setNew();
        }
        return $module;
    }
    function &get($id)
    {
        $ret = false;
        $id = intval($id);
        if ($id > 0) {
            if (!empty($this->_cachedModule_mid[$id])) {
                return $this->_cachedModule_mid[$id];
            } else {
                $sql = 'SELECT * FROM '.$this->db->prefix('modules').' WHERE mid = '.$id;
                if ($result = $this->db->query($sql)) {
                    $numrows = $this->db->getRowsNum($result);
                    if ($numrows == 1) {
                        $module =& new XoopsModule();
                        $myrow = $this->db->fetchArray($result);
                        $module->assignVars($myrow);
                        $this->_cachedModule_mid[$id] =& $module;
                        $this->_cachedModule_dirname[$module->getVar('dirname')] =& $module;
                        $ret =& $module;
                    }
                }
            }
        }
        return $ret;
    }
    function &getByDirname($dirname)
    {
        $ret = false;
        $dirname =  trim($dirname);
        if (!empty($this->_cachedModule_dirname[$dirname])) {
            $ret = $this->_cachedModule_dirname[$dirname];
        } else {
            $sql = "SELECT * FROM ".$this->db->prefix('modules')." WHERE dirname = ".$this->db->quoteString($dirname);
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                    $module =& new XoopsModule();
                    $myrow = $this->db->fetchArray($result);
                    $module->assignVars($myrow);
                    $this->_cachedModule_dirname[$dirname] =& $module;
                    $this->_cachedModule_mid[$module->getVar('mid')] =& $module;
                    $ret =& $module;
                }
            }
        }
        return $ret;
    }
    function insert(&$module)
    {
        if (strtolower(get_class($module)) != 'xoopsmodule') {
            return false;
        }
        if (!$module->isDirty()) {
            return true;
        }
        if (!$module->cleanVars()) {
            return false;
        }
        foreach ($module->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($module->isNew()) {
            if (empty($mid)) { 
            	$mid = $this->db->genId('modules_mid_seq');
            }
            $sql = sprintf("INSERT INTO %s (mid, name, version, last_update, weight, isactive, dirname, hasmain, hasadmin, hassearch, hasconfig, hascomments, hasnotification) VALUES (%u, %s, %u, %u, %u, %u, %s, %u, %u, %u, %u, %u, %u)", $this->db->prefix('modules'), $mid, $this->db->quoteString($name), $version, time(), $weight, 1, $this->db->quoteString($dirname), $hasmain, $hasadmin, $hassearch, $hasconfig, $hascomments, $hasnotification);
        } else {
            $sql = sprintf("UPDATE %s SET name = %s, dirname = %s, version = %u, last_update = %u, weight = %u, isactive = %u, hasmain = %u, hasadmin = %u, hassearch = %u, hasconfig = %u, hascomments = %u, hasnotification = %u WHERE mid = %u", $this->db->prefix('modules'), $this->db->quoteString($name), $this->db->quoteString($dirname), $version, time(), $weight, $isactive, $hasmain, $hasadmin, $hassearch, $hasconfig, $hascomments, $hasnotification, $mid);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        $module->unsetNew();
        if (empty($mid)) {
            $mid = $this->db->getInsertId();
        }
        $module->assignVar('mid', $mid);
        if (!empty($this->_cachedModule_dirname[$dirname])) {
            unset ($this->_cachedModule_dirname[$dirname]);
        }
        if (!empty($this->_cachedModule_mid[$mid])) {
            unset ($this->_cachedModule_mid[$mid]);
        }
        $this->_cachedModule_dirname[$dirname] =& $module;
        $this->_cachedModule_mid[$mid] =& $module;
        return true;
    }
    function delete(&$module)
    {
        if (strtolower(get_class($module)) != 'xoopsmodule') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE mid = %u", $this->db->prefix('modules'), $module->getVar('mid'));
        if ( !$result = $this->db->query($sql) ) {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE gperm_name = 'module_admin' AND gperm_itemid = %u", $this->db->prefix('group_permission'), $module->getVar('mid'));
        $this->db->query($sql);
        $sql = sprintf("DELETE FROM %s WHERE gperm_name = 'module_read' AND gperm_itemid = %u", $this->db->prefix('group_permission'), $module->getVar('mid'));
        $this->db->query($sql);
        if ($module->getVar('mid')==1) {
            $sql = sprintf("DELETE FROM %s WHERE gperm_name = 'system_admin'", $this->db->prefix('group_permission'));
        } else {
            $sql = sprintf("DELETE FROM %s WHERE gperm_modid = %u", $this->db->prefix('group_permission'), $module->getVar('mid'));
        }
        $this->db->query($sql);
        $sql = sprintf("SELECT block_id FROM %s WHERE module_id = %u", $this->db->prefix('block_module_link'), $module->getVar('mid'));
        if ($result = $this->db->query($sql)) {
            $block_id_arr = array();
            while ($myrow = $this->db->fetchArray($result))
{
                array_push($block_id_arr, $myrow['block_id']);
            }
        }
        if (isset($block_id_arr)) {
            foreach ($block_id_arr as $i) {
                $sql = sprintf("SELECT block_id FROM %s WHERE module_id != %u AND block_id = %u", $this->db->prefix('block_module_link'), $module->getVar('mid'), $i);
                if ($result2 = $this->db->query($sql)) {
                    if (0 < $this->db->getRowsNum($result2)) {
                        $sql = sprintf("DELETE FROM %s WHERE (module_id = %u) AND (block_id = %u)", $this->db->prefix('block_module_link'), $module->getVar('mid'), $i);
                        $this->db->query($sql);
                    } else {
                        $sql = sprintf("UPDATE %s SET visible = 0 WHERE bid = %u", $this->db->prefix('newblocks'), $i);
                        $this->db->query($sql);
                        $sql = sprintf("UPDATE %s SET module_id = -1 WHERE module_id = %u", $this->db->prefix('block_module_link'), $module->getVar('mid'));
                        $this->db->query($sql);
                    }
                }
            }
        }
        if (!empty($this->_cachedModule_dirname[$module->getVar('dirname')])) {
            unset ($this->_cachedModule_dirname[$module->getVar('dirname')]);
        }
        if (!empty($this->_cachedModule_mid[$module->getVar('mid')])) {
            unset ($this->_cachedModule_mid[$module->getVar('mid')]);
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('modules');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
			if($criteria->getSort()!=null) {
	            $sql .= ' ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
			}
			else {
	            $sql .= ' ORDER BY weight '.$criteria->getOrder().', mid ASC';
			}
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $module =& new XoopsModule();
            $module->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $module;
            } else {
                $ret[$myrow['mid']] =& $module;
            }
            unset($module);
        }
        return $ret;
    }
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('modules');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result =& $this->db->query($sql)) {
            return 0;
        }
        list($count) = $this->db->fetchRow($result);
        return $count;
    }
    function &getList($criteria = null, $dirname_as_key = false)
    {
        $ret = array();
        $modules =& $this->getObjects($criteria, true);
        foreach (array_keys($modules) as $i) {
            if (!$dirname_as_key) {
                $ret[$i] =& $modules[$i]->getVar('name');
            } else {
                $ret[$modules[$i]->getVar('dirname')] =& $modules[$i]->getVar('name');
            }
        }
        return $ret;
    }
}
?>

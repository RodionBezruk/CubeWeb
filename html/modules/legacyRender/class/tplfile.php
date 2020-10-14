<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class LegacyRenderTplfileObject extends XoopsSimpleObject
{
	var $Source = null;
	var $mOverride = null;
	function LegacyRenderTplfileObject()
	{
		$this->initVar('tpl_id', XOBJ_DTYPE_INT, '', true);
		$this->initVar('tpl_refid', XOBJ_DTYPE_INT, '0', true);
		$this->initVar('tpl_module', XOBJ_DTYPE_STRING, '', true, 25);
		$this->initVar('tpl_tplset', XOBJ_DTYPE_STRING, '', true, 50);
		$this->initVar('tpl_file', XOBJ_DTYPE_STRING, '', true, 50);
		$this->initVar('tpl_desc', XOBJ_DTYPE_STRING, '', true, 255);
		$this->initVar('tpl_lastmodified', XOBJ_DTYPE_INT, '0', true);
		$this->initVar('tpl_lastimported', XOBJ_DTYPE_INT, '0', true);
		$this->initVar('tpl_type', XOBJ_DTYPE_STRING, '', true, 20);
	}
	function loadSource()
	{
		if (!is_object($this->Source)) {
			$handler =& xoops_getmodulehandler('tplsource', 'legacyRender');
			$this->Source =& $handler->get($this->get('tpl_id'));
			if (!is_object($this->Source)) {
				$this->Source =& $handler->create();
			}
		}
	}
	function &createClone($tplsetName)
	{
		$this->loadSource();
		$obj =& new LegacyRenderTplfileObject();
		$obj->set('tpl_refid', $this->get('tpl_refid'));
		$obj->set('tpl_module', $this->get('tpl_module'));
		$obj->set('tpl_tplset', $tplsetName);
		$obj->set('tpl_file', $this->get('tpl_file'));
		$obj->set('tpl_desc', $this->get('tpl_desc'));
		$obj->set('tpl_lastmodified', $this->get('tpl_lastmodified'));
		$obj->set('tpl_lastimported', $this->get('tpl_lastimported'));
		$obj->set('tpl_type', $this->get('tpl_type'));
		$handler =& xoops_getmodulehandler('tplsource', 'legacyRender');
		$obj->Source =& $handler->create();
		$obj->Source->set('tpl_source', $this->Source->get('tpl_source'));
		return $obj;
	}
	function loadOverride($tplset)
	{
		if ($tplset == 'default' || $this->mOverride != null) {
			return;
		}
		$handler =& xoops_getmodulehandler('tplfile', 'legacyRender');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('tpl_tplset', $tplset));
		$criteria->add(new Criteria('tpl_file', $this->get('tpl_file')));
		$objs =& $handler->getObjects($criteria);
		if (count($objs) > 0) {
			$this->mOverride =& $objs[0];
		}
	}
}
class LegacyRenderTplfileHandler extends XoopsObjectGenericHandler
{
	var $mTable = "tplfile";
	var $mPrimary = "tpl_id";
	var $mClass = "LegacyRenderTplfileObject";
	function insert(&$obj, $force = false)
	{
		if (!parent::insert($obj, $force)) {
			return false;
		}
		$obj->loadSource();
		if (!is_object($obj->Source)) {
			return true;
		}
		else {
			$handler =& xoops_getmodulehandler('tplsource', 'legacyRender');
			if ($obj->Source->isNew()) {
				$obj->Source->set('tpl_id', $obj->get('tpl_id'));
			}
			return $handler->insert($obj->Source, $force);
		}
	}
	function &getObjectsWithOverride($criteria, $tplset)
	{
		$objs =& $this->getObjects($criteria);
		$ret = array();
		$i = 0;
		foreach (array_keys($objs) as $srckey) {
			if ($objs[$srckey]->get('tpl_tplset') == 'default') {
				$ret[$i] =& $objs[$srckey];
				foreach (array_keys($objs) as $destkey) {
					if ($objs[$srckey]->get('tpl_file') == $objs[$destkey]->get('tpl_file') && $objs[$destkey]->get('tpl_tplset') == $tplset) {
						$ret[$i]->mOverride =& $objs[$destkey];
					}
				}
				$i++;
			}
		}
		return $ret;
	}
	function delete(&$obj, $force = false)
	{
		$obj->loadSource();
		if (is_object($obj->Source)) {
			$handler =& xoops_getmodulehandler('tplsource', 'legacyRender');
			if (!$handler->delete($obj->Source, $force)) {
				return false;
			}
		}
		return parent::delete($obj, $force);
	}
	function &getRecentModifyFile($limit = 10)
	{
		$criteria = new Criteria('tpl_id', 0, '>');
		$criteria->setLimit($limit);
		$criteria->setSort('tpl_lastmodified');
		$criteria->setOrder('DESC');
		$objs =& $this->getObjects($criteria);
		return $objs;
	}
	function &find($tplsetName, $type = null, $refId = null, $module = null, $file = null) {
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('tpl_tplset', $tplsetName));
		if ($type != null) {
			$criteria->add(new Criteria('tpl_type', $type));
		}
		if ($refId != null) {
			$criteria->add(new Criteria('tpl_refid', $refId));
		}
		if ($module != null) {
			$criteria->add(new Criteria('tpl_module', $module));
		}
		if ($file != null) {
			$criteria->add(new Criteria('tpl_file', $file));
		}
		$objs =& $this->getObjects($criteria);
		return $objs;
	}
}
?>

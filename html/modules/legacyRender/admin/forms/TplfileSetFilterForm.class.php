<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacyRender/admin/forms/TplfileFilterForm.class.php";
class LegacyRender_TplfileSetFilterForm extends LegacyRender_TplfileFilterForm
{
	function additionalFetch()
	{
		if (isset($_REQUEST['tpl_module'])) {
			$this->mNavi->addExtra('tpl_module', xoops_getrequest('tpl_module'));
			$this->_mCriteria->add(new Criteria('tpl_module', array(XOBJ_DTYPE_STRING, xoops_getrequest('tpl_module'))));
			$handler =& xoops_gethandler('module');
			$this->mModule =& $handler->getByDirname(xoops_getrequest('tpl_module'));
		}
		if (isset($_REQUEST['tpl_tplset'])) {
			$this->mNavi->addExtra('tpl_tplset', xoops_getrequest('tpl_tplset'));
			$subCriteria =& new CriteriaCompo();
			$subCriteria->add(new Criteria('tpl_tplset', 'default'), 'OR');
			$handler =& xoops_getmodulehandler('tplset');
			$tplsets =& $handler->getObjects(new Criteria('tplset_name', xoops_getrequest('tpl_tplset')));
			if (count($tplsets) > 0) {
				$subCriteria->add(new Criteria('tpl_tplset', xoops_getrequest('tpl_tplset')));
				$this->mTplset =& $tplsets[0];
			}
		}
		if (isset($_REQUEST['tpl_type'])) {
			$this->mNavi->addExtra('tpl_type', xoops_getrequest('tpl_type'));
			$this->_mCriteria->add(new Criteria('tpl_type', array(XOBJ_DTYPE_STRING, xoops_getrequest('tpl_type'))));
		}
		if (isset($_REQUEST['tpl_file'])) {
			$this->mNavi->addExtra('tpl_file', xoops_getrequest('tpl_file'));
			$this->_mCriteria->add(new Criteria('tpl_file', '%' . xoops_getrequest('tpl_file') . '%', 'LIKE'));
		}
		if ($this->mModule != null && $this->mTplset != null) {
			$this->mNavi->setPerpage(0);
			$this->mNavi->freezePerpage();
		}
		$this->_mCriteria->addSort($this->getSort(), $this->getOrder());
	}
}
?>

<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
class Legacy_SearchResultsForm extends XCube_ActionForm
{
	var $mQueries = array();
	var $_mKeywordMin = 0;
	function Legacy_SearchResultsForm($keywordMin)
	{
		parent::XCube_ActionForm();
		$this->_mKeywordMin = intval($keywordMin);
	}
	function prepare()
	{
		$this->mFormProperties['mids'] =& new XCube_IntArrayProperty('mids');
		$this->mFormProperties['andor'] =& new XCube_StringProperty('andor');
		$this->mFormProperties['query'] =& new XCube_StringProperty('query');
		$this->mFieldProperties['andor'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['andor']->setDependsByArray(array('mask'));
		$this->mFieldProperties['andor']->addMessage('mask', _MD_LEGACY_ERROR_MASK, _MD_LEGACY_LANG_ANDOR);
		$this->mFieldProperties['andor']->addVar('mask', '/^(AND|OR|exact)$/i');
	}
	function fetch()
	{
		parent::fetch();
		$t_queries = array();
		$myts =& MyTextSanitizer::getInstance();
		if ($this->get('andor') == 'exact' && strlen($this->get('query')) >= $this->_mKeywordMin) {
			$this->mQueries[] = $myts->addSlashes($this->get('query'));
		}
		else {
			$query = $this->get('query');
			if (defined('XOOPS_USE_MULTIBYTES')) {
				$query = xoops_trim($query);
			}
			$separator = '/[\s,]+/';
			if (defined('_MD_LEGACY_FORMAT_SEARCH_SEPARATOR')) {
				$separator = _MD_LEGACY_FORMAT_SEARCH_SEPARATOR;
			}
			$tmpArr = preg_split($separator, $query);
			foreach ($tmpArr as $tmp) {
				if (strlen($tmp) >= $this->_mKeywordMin) {
					$this->mQueries[] = $myts->addSlashes($tmp);
				}
			}
		}
		$this->set('query', implode(" ", $this->mQueries));
	}
	function fetchAndor()
	{
		if ($this->get('andor') == "") {
			$this->set('andor', 'AND');
		}
	}
	function validate()
	{
		parent::validate();
		if (!count($this->mQueries)) {
			$this->addErrorMessage(_MD_LEGACY_ERROR_SEARCH_QUERY_REQUIRED);
		}
	}
	function update(&$params)
	{
		$mids = $this->get('mids');
		if (count($mids) > 0) {
			$params['mids'] = $mids;
		}
		$params['queries'] = $this->mQueries;
		$params['andor'] = $this->get('andor');
		$params['maxhit'] = LEGACY_SEARCH_RESULT_MAXHIT;
	}
}
?>

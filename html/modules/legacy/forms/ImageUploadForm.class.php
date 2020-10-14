<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
class Legacy_ImageUploadForm extends XCube_ActionForm
{
	var $mOldFileName = null;
	var $_mIsNew = null;
	var $mFormFile = null;
	function getTokenName()
	{
		return "module.legacy.ImageUploadForm.TOKEN" . $this->get('imgcat_id');
	}
	function prepare()
	{
		$this->mFormProperties['image_name'] =& new XCube_ImageFileProperty('image_name');
		$this->mFormProperties['image_nicename'] =& new XCube_StringProperty('image_nicename');
		$this->mFormProperties['imgcat_id'] =& new XCube_IntProperty('imgcat_id');
		$this->mFieldProperties['image_name'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['image_name']->setDependsByArray(array('extension'));
		$this->mFieldProperties['image_name']->addVar('extension', 'jpg,gif,png');
		$this->mFieldProperties['image_nicename'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['image_nicename']->setDependsByArray(array('required'));
		$this->mFieldProperties['image_nicename']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _MD_LEGACY_LANG_IMAGE_NICENAME);
		$this->mFieldProperties['imgcat_id'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['imgcat_id']->setDependsByArray(array('required','objectExist'));
		$this->mFieldProperties['imgcat_id']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _MD_LEGACY_LANG_IMGCAT_ID);
		$this->mFieldProperties['imgcat_id']->addMessage('objectExist', _MD_LEGACY_ERROR_OBJECTEXIST, _MD_LEGACY_LANG_IMGCAT_ID);
		$this->mFieldProperties['imgcat_id']->addVar('handler', 'imagecategory');
		$this->mFieldProperties['imgcat_id']->addVar('module', 'legacy');
		$root =& XCube_Root::getSingleton();
		$root->mLanguageManager->loadModuleAdminMessageCatalog('legacy');
	}
	function validateImgcat_id()
	{
		$imgcat_id = $this->get('imgcat_id');
		if ($imgcat_id != null) {
			$root =& XCube_Root::getSingleton();
			$xoopsUser =& $root->mController->mRoot->mContext->mXoopsUser;
			$groups = array();
			if (is_object($xoopsUser)) {
				$groups =& $xoopsUser->getGroups();
			}
			else {
				$groups = array(XOOPS_GROUP_ANONYMOUS);
			}
			$handler =& xoops_getmodulehandler('imagecategory', 'legacy');
			$imgcat =& $handler->get($imgcat_id);
			if (is_object($imgcat) && !$imgcat->hasUploadPerm($groups)) {
				$this->addErrorMessage(_MD_LEGACY_ERROR_PERMISSION);
			}
		}
	}
	function validateImage_name()
	{
		$formFile = $this->get('image_name');
		if ($formFile == null && $this->_mIsNew ) {
			$this->addErrorMessage(_MD_LEGACY_ERROR_YOU_MUST_UPLOAD);
		}
	}
	function validate()
	{
		parent::validate();
		$handler =& xoops_getmodulehandler('imagecategory', 'legacy');
		$category =& $handler->get($this->get('imgcat_id'));
		$formFile = $this->get('image_name');
		if ($formFile != null && is_object($category)) {
			if ($formFile->getWidth() > $category->get('imgcat_maxwidth') || $formFile->getHeight() > $category->get('imgcat_maxheight')) {
				$this->addErrorMessage(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_IMG_SIZE, $category->get('imgcat_maxwidth'), $category->get('imgcat_maxheight')));
			}
			if ($formFile->getFilesize() > $category->get('imgcat_maxsize')) {
				$this->addErrorMessage(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_IMG_FILESIZE, $category->get('imgcat_maxsize')));
			}
		}
	}
	function load(&$obj)
	{
		$this->set('image_nicename', $obj->get('image_nicename'));
		$this->set('imgcat_id', $obj->get('imgcat_id'));
		$this->_mIsNew = $obj->isNew();
		$this->mOldFileName = $obj->get('image_name');
	}
	function update(&$obj)
	{
		$obj->set('image_nicename', $this->get('image_nicename'));
		$obj->set('image_display', true);
		$obj->set('imgcat_id', $this->get('imgcat_id'));
		$handler =& xoops_getmodulehandler('imagecategory', 'legacy');
		$category =& $handler->get($this->get('imgcat_id'));
		$this->mFormFile = $this->get('image_name');
		if ($this->mFormFile != null) {
			$this->mFormFile->setRandomToBodyName('img');
			$filename = $this->mFormFile->getBodyName();
			$this->mFormFile->setBodyName(substr($filename, 0, 24));
			$obj->set('image_name', $this->mFormFile->getFileName());
			$obj->set('image_mimetype', $this->mFormFile->getContentType());
			if ($category->get('imgcat_storetype') == 'db') {
				$obj->loadImageBody();
				if (!is_object($obj->mImageBody)) {
					$obj->mImageBody =& $obj->createImageBody();
				}
				$obj->mImageBody->set('image_body', file_get_contents($this->mFormFile->_mTmpFileName));
			}
		}
	}
}
?>

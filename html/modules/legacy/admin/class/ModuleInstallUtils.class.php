<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleInstallInformation.class.php";
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleInstaller.class.php";
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleUpdater.class.php";
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleUninstaller.class.php";
require_once XOOPS_ROOT_PATH."/class/template.php";
define("MODINSTALL_LOGTYPE_REPORT", "report");
define("MODINSTALL_LOGTYPE_WARNING", "warning");
define("MODINSTALL_LOGTYPE_ERROR", "error");
class Legacy_ModuleInstallLog
{
	var $mFetalErrorFlag = false;
	var $mMessages = array();
	function add($msg)
	{
		$this->mMessages[] = array('type' => MODINSTALL_LOGTYPE_REPORT, 'message' => $msg);
	}
	function addReport($msg)
	{
		$this->add($msg);
	}
	function addWarning($msg)
	{
		$this->mMessages[] = array('type' => MODINSTALL_LOGTYPE_WARNING, 'message' => $msg);
	}
	function addError($msg)
	{
		$this->mMessages[] = array('type' => MODINSTALL_LOGTYPE_ERROR, 'message' => $msg);
		$this->mFetalErrorFlag = true;
	}
	function hasError()
	{
		return $this->mFetalErrorFlag;
	}
}
class Legacy_ModuleInstallUtils
{
	function &createInstaller($dirname)
	{
		$installer =& Legacy_ModuleInstallUtils::_createInstaller($dirname, 'installer', 'Legacy_ModuleInstaller');
        return $installer;
	}
	function &createUpdater($dirname)
	{
		$updater =& Legacy_ModuleInstallUtils::_createInstaller($dirname, 'updater', 'Legacy_ModulePhasedUpgrader');
		return $updater;
	}
	function &createUninstaller($dirname)
	{
		$uninstaller =& Legacy_ModuleInstallUtils::_createInstaller($dirname, 'uninstaller', 'Legacy_ModuleUninstaller');
		return $uninstaller;
	}
	function &_createInstaller($dirname, $mode, $defaultClassName)
	{
		$info = array();
		$filepath = XOOPS_MODULE_PATH . "/${dirname}/xoops_version.php";
		if (file_exists($filepath)) {
			@include $filepath;
			$info = $modversion;
		}
		if (isset($info['legacy_installer']) && is_array($info['legacy_installer']) && isset($info['legacy_installer'][$mode])) {
			$updateInfo = $info['legacy_installer'][$mode];
			$className = $updateInfo['class'];
			$filePath = isset($updateInfo['filepath']) ? $updateInfo['filepath'] : XOOPS_MODULE_PATH . "/${dirname}/admin/class/${className}.class.php";
			$namespace = isset($updateInfo['namespace']) ? $updateInfo['namespace'] : ucfirst($dirname);
			if ($namespace != null) {
				$className = "${namespace}_${className}";
			}
			if (!XC_CLASS_EXISTS($className) && file_exists($filePath)) {
				require_once $filePath;
			}
			if (XC_CLASS_EXISTS($className)) {
				$installer =& new $className();
				return $installer;
			}
		}
		$installer =& new $defaultClassName();
		return $installer;
	}
	function installSQLAutomatically(&$module, &$log)
	{
		$sqlfileInfo =& $module->getInfo('sqlfile');
		$dirname = $module->getVar('dirname');
		if (!isset($sqlfileInfo[XOOPS_DB_TYPE])) {
			return;
		}
		$sqlfile = $sqlfileInfo[XOOPS_DB_TYPE];
		$sqlfilepath = XOOPS_MODULE_PATH . "/${dirname}/${sqlfile}";
		if (isset($module->modinfo['cube_style']) && $module->modinfo['cube_style'] == true) {
			require_once XOOPS_MODULE_PATH . "/legacy/admin/class/Legacy_SQLScanner.class.php";
			$scanner =& new Legacy_SQLScanner();
			$scanner->setDB_PREFIX(XOOPS_DB_PREFIX);
			$scanner->setDirname($module->get('dirname'));
			if (!$scanner->loadFile($sqlfilepath)) {
				$log->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_SQL_FILE_NOT_FOUND, $sqlfile));
				return false;
			}
			$scanner->parse();
			$sqls = $scanner->getSQL();
			$root =& XCube_Root::getSingleton();
			$db =& $root->mController->getDB();
			foreach ($sqls as $sql) {
				if (!$db->query($sql)) {
					$log->addError($db->error());
					return;
				}
			}
			$log->addReport(_AD_LEGACY_MESSAGE_DATABASE_SETUP_FINISHED);
		}
		else {
			require_once XOOPS_ROOT_PATH.'/class/database/sqlutility.php';
			$reservedTables = array('avatar', 'avatar_users_link', 'block_module_link', 'xoopscomments', 'config', 'configcategory', 'configoption', 'image', 'imagebody', 'imagecategory', 'imgset', 'imgset_tplset_link', 'imgsetimg', 'groups','groups_users_link','group_permission', 'online', 'bannerclient', 'banner', 'bannerfinish', 'priv_msgs', 'ranks', 'session', 'smiles', 'users', 'newblocks', 'modules', 'tplfile', 'tplset', 'tplsource', 'xoopsnotifications', 'banner', 'bannerclient', 'bannerfinish');
			$root =& XCube_Root::getSingleton();
			$db =& $root->mController->mDB;
			$sql_query = fread(fopen($sqlfilepath, 'r'), filesize($sqlfilepath));
			$sql_query = trim($sql_query);
			SqlUtility::splitMySqlFile($pieces, $sql_query);
			$created_tables = array();
			foreach ($pieces as $piece) {
				$prefixed_query = SqlUtility::prefixQuery($piece, $db->prefix());
				if (!$prefixed_query) {
					$log->addError("${piece} is not a valid SQL!");
					return;
				}
				if (!in_array($prefixed_query[4], $reservedTables)) {
					if (!$db->query($prefixed_query[0])) {
							$log->addError($db->error());
						return;
					}
					else {
						if (!in_array($prefixed_query[4], $created_tables)) {
							$log->addReport('  Table ' . $db->prefix($prefixed_query[4]) . ' created.');
							$created_tables[] = $prefixed_query[4];
						}
						else {
							$log->addReport('  Data inserted to table ' . $db->prefix($prefixed_query[4]));
						}
					}
				}
				else {
					$log->addError($prefixed_query[4] . " is a reserved table!");
					return;
				}
			}
		}
	}
	function installAllOfModuleTemplates(&$module, &$log)
	{
        $templates = $module->getInfo('templates');
        if ($templates != false) {
            foreach ($templates as $template) {
                Legacy_ModuleInstallUtils::installModuleTemplate($module, $template, $log);
            }
        }
	}
	function installModuleTemplate($module, $template, &$log)
	{
		$tplHandler =& xoops_gethandler('tplfile');
		$fileName = trim($template['file']);
		$tpldata = Legacy_ModuleInstallUtils::readTemplateFile($module->get('dirname'), $fileName);
		if ($tpldata == false)
			return false;
		$tplfile =& $tplHandler->create();
		$tplfile->setVar('tpl_refid', $module->getVar('mid'));
		$tplfile->setVar('tpl_lastimported', 0);
		$tplfile->setVar('tpl_lastmodified', time());
		if (preg_match("/\.css$/i", $fileName)) {
			$tplfile->setVar('tpl_type', 'css');
		}
		else {
			$tplfile->setVar('tpl_type', 'module');
		}
		$tplfile->setVar('tpl_source', $tpldata, true);
		$tplfile->setVar('tpl_module', $module->getVar('dirname'));
		$tplfile->setVar('tpl_tplset', 'default');
		$tplfile->setVar('tpl_file', $fileName, true);
		$description = isset($template['description']) ? $template['description'] : '';
		$tplfile->setVar('tpl_desc', $description, true);
		if ($tplHandler->insert($tplfile)) {
			$log->addReport(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_TEMPLATE_INSTALLED, $fileName));
		}
		else {
			$log->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_COULD_NOT_INSTALL_TEMPLATE, $fileName));
			return false;
		}
	}
	function _uninstallAllOfModuleTemplates(&$module, $tplset, &$log)
	{
		$tplHandler =& xoops_gethandler('tplfile');
		$delTemplates = null;
		$delTemplates =& $tplHandler->find($tplset, 'module', $module->get('mid'));
		if (is_array($delTemplates) && count($delTemplates) > 0) {
			$xoopsTpl =& new XoopsTpl();
			$xoopsTpl->clear_cache(null, "mod_" . $module->get('dirname'));
			foreach ($delTemplates as $tpl) {
				if (!$tplHandler->delete($tpl)) {
					$log->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_TEMPLATE_UNINSTALLED, $tpl->get('tpl_file')));
				}
			}
		}
	}
	function uninstallAllOfModuleTemplates(&$module, &$log)
	{
		Legacy_ModuleInstallUtils::_uninstallAllOfModuleTemplates($module, null, $log);
	}
	function clearAllOfModuleTemplatesForUpdate(&$module, &$log)
	{
		Legacy_ModuleInstallUtils::_uninstallAllOfModuleTemplates($module, 'default', $log);
	}
	function installAllOfBlocks(&$module, &$log)
	{
		$definedBlocks = $module->getInfo('blocks');
		if($definedBlocks == false) {
			return true;
		}
		$func_num = 0;
		foreach ($definedBlocks as $block) {
			$successFlag = true;
			$updateblocks = array();
			foreach ($definedBlocks as $idx => $block) {
				if (isset($block['func_num'])) {
					$updateblocks[$idx] = $block;
				} else {
					$successFlag = false;
					break;
				}
			}
			if ($successFlag == false) {
				$successFlag = true;
				$updateblocks = array();
				foreach ($definedBlocks as $idx => $block) {
					if (is_int($idx)) {
						$block['func_num'] = $idx;
						$updateblocks[$idx] = $block;
					} else {
						$successFlag = false;
						break;
					}
				}
			}
			if ($successFlag == false) {
				$successFlag = true;
				$updateblocks = array();
				$func_num = 0;
				foreach ($definedBlocks as $block) {
					$block['func_num'] = $func_num;
					$updateblocks[] = $block;
				}
			}
		}
		foreach ($updateblocks as $block) {
			$newBlock =& Legacy_ModuleInstallUtils::createBlockByInfo($module, $block, $block['func_num']);
			Legacy_ModuleInstallUtils::installBlock($module, $newBlock, $block, $log);
		}
	}
	function uninstallAllOfBlocks(&$module, &$log)
	{
		$handler =& xoops_gethandler('block');
		$criteria = new Criteria('mid', $module->get('mid'));
		$blockArr =& $handler->getObjectsDirectly($criteria);
		$successFlag = true;
		foreach (array_keys($blockArr) as $idx) {
			$successFlag &= Legacy_ModuleInstallUtils::uninstallBlock($blockArr[$idx], $log);
		}
		return $successFlag;
	}
	function &createBlockByInfo(&$module, $block, $func_num)
	{
		$options = isset($block['options']) ? $block['options'] : null;
		$edit_func = isset($block['edit_func']) ? $block['edit_func'] : null;
		$template = isset($block['template']) ? $block['template'] : null;
		$visible = isset($block['visible']) ? $block['visible'] : (isset($block['visible_any']) ? $block['visible_any']: 0);
		$blockHandler =& xoops_gethandler('block');
		$blockObj =& $blockHandler->create();
		$blockObj->set('mid', $module->getVar('mid'));
		$blockObj->set('options', $options);
		$blockObj->set('name', $block['name']);
		$blockObj->set('title', $block['name']);
		$blockObj->set('block_type', 'M');
		$blockObj->set('c_type', 1);
		$blockObj->set('isactive', 1);
		$blockObj->set('dirname', $module->getVar('dirname'));
		$blockObj->set('func_file', $block['file']);
		$show_func = "";
		if (isset($block['class'])) {
			$show_func = "cl::" . $block['class'];
		}
		else {
			$show_func = $block['show_func'];
		}
		$blockObj->set('show_func', $show_func);
		$blockObj->set('edit_func', $edit_func);
		$blockObj->set('template', $template);
		$blockObj->set('last_modified', time());
		$blockObj->set('visible', $visible);
		$func_num = isset($block['func_num']) ? intval($block['func_num']) : $func_num;
		$blockObj->set('func_num', $func_num);
		return $blockObj;
	}
	function installBlock(&$module, &$blockObj, &$block, &$log)
	{
		$isNew = $blockObj->isNew();
		$blockHandler =& xoops_gethandler('block');
        if (!empty($block['show_all_module'])) {
            $autolink = false;
        } else {
            $autolink = true;
        }
		if (!$blockHandler->insert($blockObj, $autolink)) {
			$log->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_COULD_NOT_INSTALL_BLOCK, $blockObj->getVar('name')));
			return false;
		}
		else {
			$log->addReport(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_BLOCK_INSTALLED, $blockObj->getVar('name')));
			$tplHandler =& xoops_gethandler('tplfile');
			Legacy_ModuleInstallUtils::installBlockTemplate($blockObj, $module, $log);
			if ($isNew) {
                if (!empty($block['show_all_module'])) {
        			$link_sql = "INSERT INTO " . $blockHandler->db->prefix('block_module_link') . " (block_id, module_id) VALUES (".$blockObj->getVar('bid').", 0)";
		        	if (!$blockHandler->db->query($link_sql)) {
       					$log->addWarning(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_COULD_NOT_SET_LINK, $blockObj->getVar('name')));
		        	}
    			}
   				$gpermHandler =& xoops_gethandler('groupperm');
   				$bperm =& $gpermHandler->create();
				$bperm->setVar('gperm_itemid', $blockObj->getVar('bid'));
				$bperm->setVar('gperm_name', 'block_read');
				$bperm->setVar('gperm_modid', 1);
				if (!empty($block['visible_any'])) {
    				$memberHandler =& xoops_gethandler('member');
    				$groupObjects =& $memberHandler->getGroups();
    				foreach($groupObjects as $group) {
        				$bperm->setVar('gperm_groupid', $group->getVar('groupid'));
        				$bperm->setNew();
        				if (!$gpermHandler->insert($bperm)) {
        					$log->addWarning(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_COULD_NOT_SET_BLOCK_PERMISSION, $blockObj->getVar('name')));
        				}
        			}
				} else {
				    $root =& XCube_Root::getSingleton();
                    $groups = $root->mContext->mXoopsUser->getGroups(true);
                    foreach ($groups as $mygroup) {
        				$bperm->setVar('gperm_groupid', $mygroup);
        				$bperm->setNew();
        				if (!$gpermHandler->insert($bperm)) {
        					$log->addWarning(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_COULD_NOT_SET_BLOCK_PERMISSION, $blockObj->getVar('name')));
    				    }
    				}
				}
			}
			return true;
		}
	}
	function uninstallBlock(&$block, &$log)
	{
		$blockHandler =& xoops_gethandler('block');
		$blockHandler->delete($block);
		$log->addReport(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_UNINSTALLATION_BLOCK_SUCCESSFUL, $block->get('name')));
		$gpermHandler =& xoops_gethandler('groupperm');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('gperm_name', 'block_read'));
		$criteria->add(new Criteria('gperm_itemid', $block->get('bid')));
		$criteria->add(new Criteria('gperm_modid', 1));
		$gpermHandler->deleteAll($criteria);
    }
	function installBlockTemplate(&$block, &$module, &$log)
	{
		if ($block->get('template') == null) {
			return true;
		}
		$tplHandler =& xoops_gethandler('tplfile');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('tpl_type', 'block'));
		$criteria->add(new Criteria('tpl_tplset', 'default'));
		$criteria->add(new Criteria('tpl_module', $module->get('dirname')));
		$criteria->add(new Criteria('tpl_file', $block->get('template')));
		$tplfiles =& $tplHandler->getObjects($criteria);
		if (count($tplfiles) > 0) {
			$tplfile =& $tplfiles[0];
		}
		else {
			$tplfile =& $tplHandler->create();
			$tplfile->set('tpl_refid', $block->get('bid'));
			$tplfile->set('tpl_tplset', 'default');
			$tplfile->set('tpl_file', $block->get('template'));
			$tplfile->set('tpl_module', $module->get('dirname'));
			$tplfile->set('tpl_type', 'block');
			$tplfile->set('tpl_lastimported', 0);
		}
		$tplSource = Legacy_ModuleInstallUtils::readTemplateFile($module->get('dirname'), $block->get('template'), true);
		$tplfile->set('tpl_source', $tplSource);
		$tplfile->set('tpl_lastmodified', time());
		if ($tplHandler->insert($tplfile)) {
		    $log->addReport(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_BLOCK_TEMPLATE_INSTALLED, $block->get('template')));
			return true;
		}
		else {
			$log->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_BLOCK_TEMPLATE_INSTALL, $block->get('name')));
			return false;
		}
	}
	function readTemplateFile($dirname, $fileName, $isblock = false)
	{
		if ($isblock) {
			$filePath = XOOPS_MODULE_PATH . "/" . $dirname . "/templates/blocks/" . $fileName;
		}
		else {
			$filePath = XOOPS_MODULE_PATH . "/" . $dirname . "/templates/" . $fileName;
		}
		if (!file_exists($filePath)) {
			return false;
		}
		$lines = file($filePath);
		if ($lines == false) {
			return false;
		}
		$tpldata = "";
		foreach ($lines as $line) {
			$tpldata .= str_replace("\n", "\r\n", str_replace("\r\n", "\n", $line));
		}
		return $tpldata;
	}
	function installAllOfConfigs(&$module, &$log)
	{
		$dirname = $module->get('dirname');
		$fileReader =& new Legacy_ModinfoX2FileReader($dirname);
		$preferences =& $fileReader->loadPreferenceInformations();
		foreach (array_keys($preferences->mPreferences) as $idx) {
			Legacy_ModuleInstallUtils::installPreferenceByInfo($preferences->mPreferences[$idx], $module, $log);
		}
		foreach (array_keys($preferences->mComments) as $idx) {
			Legacy_ModuleInstallUtils::installPreferenceByInfo($preferences->mComments[$idx], $module, $log);
		}
		foreach (array_keys($preferences->mNotifications) as $idx) {
			Legacy_ModuleInstallUtils::installPreferenceByInfo($preferences->mNotifications[$idx], $module, $log);
		}
	}
	function installPreferenceByInfo(&$info, &$module, &$log)
	{
		$handler =& xoops_gethandler('config');
		$config =& $handler->createConfig();
		$config->set('conf_modid', $module->get('mid'));
		$config->set('conf_catid', 0);
		$config->set('conf_name', $info->mName);
		$config->set('conf_title', $info->mTitle);
		$config->set('conf_desc', $info->mDescription);
		$config->set('conf_formtype', $info->mFormType);
		$config->set('conf_valuetype', $info->mValueType);
		$config->setConfValueForInput($info->mDefault);
		$config->set('conf_order', $info->mOrder);
		if (count($info->mOption->mOptions) > 0) {
			foreach (array_keys($info->mOption->mOptions) as $idx) {
				$option =& $handler->createConfigOption();
				$option->set('confop_name', $info->mOption->mOptions[$idx]->mName);
				$option->set('confop_value', $info->mOption->mOptions[$idx]->mValue);
				$config->setConfOptions($option);
				unset($option);
			}
		}
		if ($handler->insertConfig($config)) {
			$log->addReport(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_INSERT_CONFIG, $config->get('conf_name')));
		}
		else {
			$log->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_COULD_NOT_INSERT_CONFIG, $config->get('conf_name')));
		}
	}
	function &getConfigInfosFromManifesto(&$module)
	{
		$configInfos = $module->getInfo('config');
		if ($module->getVar('hascomments') !=0 ) {
			require_once XOOPS_ROOT_PATH . "/include/comment_constants.php";
			$configInfos[] = array('name' => 'com_rule',
			                         'title' => '_CM_COMRULES',
			                         'description' => '',
			                         'formtype' => 'select',
			                         'valuetype' => 'int',
			                         'default' => 1,
			                         'options' => array('_CM_COMNOCOM' => XOOPS_COMMENT_APPROVENONE, '_CM_COMAPPROVEALL' => XOOPS_COMMENT_APPROVEALL, '_CM_COMAPPROVEUSER' => XOOPS_COMMENT_APPROVEUSER, '_CM_COMAPPROVEADMIN' => XOOPS_COMMENT_APPROVEADMIN)
			                   );
			$configInfos[] = array('name' => 'com_anonpost',
			                         'title' => '_CM_COMANONPOST',
			                         'description' => '',
			                         'formtype' => 'yesno',
			                         'valuetype' => 'int',
			                         'default' => 0
			                   );
		}
		if ($module->get('hasnotification') != 0) {
			require_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
			require_once XOOPS_ROOT_PATH . '/include/notification_functions.php';
			$t_options = array();
			$t_options['_NOT_CONFIG_DISABLE'] = XOOPS_NOTIFICATION_DISABLE;
			$t_options['_NOT_CONFIG_ENABLEBLOCK'] = XOOPS_NOTIFICATION_ENABLEBLOCK;
			$t_options['_NOT_CONFIG_ENABLEINLINE'] = XOOPS_NOTIFICATION_ENABLEINLINE;
			$t_options['_NOT_CONFIG_ENABLEBOTH'] = XOOPS_NOTIFICATION_ENABLEBOTH;
			$configInfos[] = array(
				'name' => 'notification_enabled',
				'title' => '_NOT_CONFIG_ENABLE',
				'description' => '_NOT_CONFIG_ENABLEDSC',
				'formtype' => 'select',
				'valuetype' => 'int',
				'default' => XOOPS_NOTIFICATION_ENABLEBOTH,
				'options' => $t_options
			);
			unset ($t_options);
			$t_options = array();
			$t_categoryArr =& notificationCategoryInfo('', $module->get('mid'));
			foreach ($t_categoryArr as $t_category) {
				$t_eventArr =& notificationEvents($t_category['name'], false, $module->get('mid'));
				foreach ($t_eventArr as $t_event) {
					if (!empty($t_event['invisible'])) {
						continue;
					}
					$t_optionName = $t_category['title'] . ' : ' . $t_event['title'];
					$t_options[$t_optionName] = $t_category['name'] . '-' . $t_event['name'];
				}
			}
			$configInfos[] = array(
				'name' => 'notification_events',
				'title' => '_NOT_CONFIG_EVENTS',
				'description' => '_NOT_CONFIG_EVENTSDSC',
				'formtype' => 'select_multi',
				'valuetype' => 'array',
				'default' => array_values($t_options),
				'options' => $t_options
			);
		}
		return $configInfos;
	}
	function uninstallAllOfConfigs(&$module, &$log)
	{
		if ($module->get('hasconfig') == 0) {
			return;
		}
		$configHandler =& xoops_gethandler('config');
		$configs =& $configHandler->getConfigs(new Criteria('conf_modid', $module->get('mid')));
		if (count($configs) == 0) {
			return;
		}
		foreach ($configs as $config) {
			$configHandler->deleteConfig($config);
		}
	}
	function smartUpdateAllOfBlocks(&$module, &$log)
	{
		$dirname = $module->get('dirname');
		$fileReader =& new Legacy_ModinfoX2FileReader($dirname);
		$latestBlocks =& $fileReader->loadBlockInformations();
		$dbReader =& new Legacy_ModinfoX2DBReader($dirname);
		$currentBlocks =& $dbReader->loadBlockInformations();
		$currentBlocks->update($latestBlocks);
		foreach (array_keys($currentBlocks->mBlocks) as $idx) {
			switch ($currentBlocks->mBlocks[$idx]->mStatus) {
				case LEGACY_INSTALLINFO_STATUS_LOADED:
					Legacy_ModuleInstallUtils::updateBlockTemplateByInfo($currentBlocks->mBlocks[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_UPDATED:
					Legacy_ModuleInstallUtils::updateBlockByInfo($currentBlocks->mBlocks[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_NEW:
					Legacy_ModuleInstallUtils::installBlockByInfo($currentBlocks->mBlocks[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_DELETED:
					Legacy_ModuleInstallUtils::uninstallBlockByFuncNum($currentBlocks->mBlocks[$idx]->mFuncNum, $module, $log);
					break;
			}
		}
	}
	function smartUpdateAllOfPreferences(&$module, &$log)
	{
		$dirname = $module->get('dirname');
		$fileReader =& new Legacy_ModinfoX2FileReader($dirname);
		$latestPreferences =& $fileReader->loadPreferenceInformations();
		$dbReader =& new Legacy_ModinfoX2DBReader($dirname);
		$currentPreferences =& $dbReader->loadPreferenceInformations();
		$currentPreferences->update($latestPreferences);
		foreach (array_keys($currentPreferences->mPreferences) as $idx) {
			switch ($currentPreferences->mPreferences[$idx]->mStatus) {
				case LEGACY_INSTALLINFO_STATUS_UPDATED:
					Legacy_ModuleInstallUtils::updatePreferenceByInfo($currentPreferences->mPreferences[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_ORDER_UPDATED:
					Legacy_ModuleInstallUtils::updatePreferenceOrderByInfo($currentPreferences->mPreferences[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_NEW:
					Legacy_ModuleInstallUtils::installPreferenceByInfo($currentPreferences->mPreferences[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_DELETED:
					Legacy_ModuleInstallUtils::uninstallPreferenceByOrder($currentPreferences->mPreferences[$idx]->mOrder, $module, $log);
					break;
			}
		}
		foreach (array_keys($currentPreferences->mComments) as $idx) {
			switch ($currentPreferences->mComments[$idx]->mStatus) {
				case LEGACY_INSTALLINFO_STATUS_UPDATED:
					Legacy_ModuleInstallUtils::updatePreferenceByInfo($currentPreferences->mComments[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_ORDER_UPDATED:
					Legacy_ModuleInstallUtils::updatePreferenceOrderByInfo($currentPreferences->mComments[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_NEW:
					Legacy_ModuleInstallUtils::installPreferenceByInfo($currentPreferences->mComments[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_DELETED:
					Legacy_ModuleInstallUtils::uninstallPreferenceByOrder($currentPreferences->mComments[$idx]->mOrder, $module, $log);
					break;
			}
		}
		foreach (array_keys($currentPreferences->mNotifications) as $idx) {
			switch ($currentPreferences->mNotifications[$idx]->mStatus) {
				case LEGACY_INSTALLINFO_STATUS_UPDATED:
					Legacy_ModuleInstallUtils::updatePreferenceByInfo($currentPreferences->mNotifications[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_ORDER_UPDATED:
					Legacy_ModuleInstallUtils::updatePreferenceOrderByInfo($currentPreferences->mNotifications[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_NEW:
					Legacy_ModuleInstallUtils::installPreferenceByInfo($currentPreferences->mNotifications[$idx], $module, $log);
					break;
				case LEGACY_INSTALLINFO_STATUS_DELETED:
					Legacy_ModuleInstallUtils::uninstallPreferenceByOrder($currentPreferences->mNotifications[$idx]->mOrder, $module, $log);
					break;
			}
		}
	}
	function updateBlockTemplateByInfo(&$info, &$module, &$log)
	{
		$handler =& xoops_getmodulehandler('newblocks', 'legacy');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('dirname', $module->get('dirname')));
		$criteria->add(new Criteria('func_num', $info->mFuncNum));
		$blockArr =& $handler->getObjects($criteria);
		foreach (array_keys($blockArr) as $idx) {
			Legacy_ModuleInstallUtils::clearBlockTemplateForUpdate($blockArr[$idx], $module, $log);
			Legacy_ModuleInstallUtils::installBlockTemplate($blockArr[$idx], $module, $log);
		}
	}
	function updateBlockByInfo(&$info, &$module, &$log)
	{
		$handler =& xoops_getmodulehandler('newblocks', 'legacy');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('dirname', $module->get('dirname')));
		$criteria->add(new Criteria('func_num', $info->mFuncNum));
		$blockArr =& $handler->getObjects($criteria);
		foreach (array_keys($blockArr) as $idx) {
			$blockArr[$idx]->set('options', $info->mOptions);
			$blockArr[$idx]->set('name', $info->mName);
			$blockArr[$idx]->set('func_file', $info->mFuncFile);
			$blockArr[$idx]->set('show_func', $info->mShowFunc);
			$blockArr[$idx]->set('edit_func', $info->mEditFunc);
			$blockArr[$idx]->set('template', $info->mTemplate);
			if ($handler->insert($blockArr[$idx])) {
				$log->addReport(XCube_Utils::formatMessage('Update {0} block successfully.', $blockArr[$idx]->get('name')));
			}
			else {
				$log->addError(XCube_Utils::formatMessage('Could not update {0} block.', $blockArr[$idx]->get('name')));
			}
			Legacy_ModuleInstallUtils::clearBlockTemplateForUpdate($blockArr[$idx], $module, $log);
			Legacy_ModuleInstallUtils::installBlockTemplate($blockArr[$idx], $module, $log);
		}
	}
	function updatePreferenceByInfo(&$info, &$module, &$log)
	{
		$handler =& xoops_gethandler('config');
		$criteria =& new CriteriaCompo();		
		$criteria->add(new Criteria('conf_modid', $module->get('mid')));
		$criteria->add(new Criteria('conf_catid', 0));
		$criteria->add(new Criteria('conf_name', $info->mName));
		$configArr =& $handler->getConfigs($criteria);
		if (!(count($configArr) > 0 && is_object($configArr[0]))) {
			$log->addError('Execption Error: Could not find config.');
			return;
		}
		$config =& $configArr[0];
		$config->set('conf_title', $info->mTitle);
		$config->set('conf_desc', $info->mDescription);
		if ($config->get('conf_formtype') != $info->mFormType && $config->get('conf_valuetype') != $info->mValueType) {
			$config->set('conf_formtype', $info->mFormType);
			$config->set('conf_valuetype', $info->mValueType);
			$config->setConfValueForInput($info->mDefault);
		}
		else {
			$config->set('conf_formtype', $info->mFormType);
			$config->set('conf_valuetype', $info->mValueType);
		}
		$config->set('conf_order', $info->mOrder);
		$optionArr =& $handler->getConfigOptions(new Criteria('conf_id', $config->get('conf_id')));
		if (is_array($optionArr)) {
			foreach (array_keys($optionArr) as $idx) {
				$handler->_oHandler->delete($optionArr[$idx]);
			}
		}
		if (count($info->mOption->mOptions) > 0) {
			foreach (array_keys($info->mOption->mOptions) as $idx) {
				$option =& $handler->createConfigOption();
				$option->set('confop_name', $info->mOption->mOptions[$idx]->mName);
				$option->set('confop_value', $info->mOption->mOptions[$idx]->mValue);
				$option->set('conf_id', $option->get('conf_id'));
				$config->setConfOptions($option);
				unset($option);
			}
		}
		if ($handler->insertConfig($config)) {
			$log->addReport(XCube_Utils::formatMessage("Preference '{0}' is updateded.", $config->get('conf_name')));
		}
		else {
			$log->addError(XCube_Utils::formatMessage("Could not update preference '{0}'.", $config->get('conf_name')));
		}
	}
	function updatePreferenceOrderByInfo(&$info, &$module, &$log)
	{
		$handler =& xoops_gethandler('config');
		$criteria =& new CriteriaCompo();		
		$criteria->add(new Criteria('conf_modid', $module->get('mid')));
		$criteria->add(new Criteria('conf_catid', 0));
		$criteria->add(new Criteria('conf_name', $info->mName));
		$configArr =& $handler->getConfigs($criteria);
		if (!(count($configArr) > 0 && is_object($configArr[0]))) {
			$log->addError('Execption Error: Could not find config.');
			return;
		}
		$config =& $configArr[0];
		$config->set('conf_order', $info->mOrder);
		if (!$handler->insertConfig($config)) {
			$log->addError(XCube_Utils::formatMessage("Could not update the order of preference '{0}'.", $config->get('conf_name')));
		}
	}
    function installBlockByInfo(&$info, &$module, &$log)
    {
        $handler =& xoops_gethandler('block');
        $block =& $handler->create();
        $block->set('mid', $module->get('mid'));
        $block->set('func_num', $info->mFuncNum);
        $block->set('options', $info->mOptions);
        $block->set('name', $info->mName);
        $block->set('title', $info->mName);
        $block->set('dirname', $module->get('dirname'));
        $block->set('func_file', $info->mFuncFile);
        $block->set('show_func', $info->mShowFunc);
        $block->set('edit_func', $info->mEditFunc);
        $block->set('template', $info->mTemplate);
        $block->set('block_type', 'M');
        $block->set('c_type', 1);
        if (!$handler->insert($block)) {
            $log->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_COULD_NOT_INSTALL_BLOCK, $block->get('name')));
            return false;
        }
        else {
            $log->addReport(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_BLOCK_INSTALLED, $block->get('name')));
            Legacy_ModuleInstallUtils::installBlockTemplate($block, $module, $log);
            return true;
        }
    }
	function uninstallBlockByFuncNum($func_num, &$module, &$log)
	{
		$handler =& xoops_getmodulehandler('newblocks', 'legacy');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('dirname', $module->get('dirname')));
		$criteria->add(new Criteria('func_num', $func_num));
		$blockArr =& $handler->getObjects($criteria);
		foreach (array_keys($blockArr) as $idx) {
			if ($handler->delete($blockArr[$idx])) {
				$log->addReport(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_UNINSTALLATION_BLOCK_SUCCESSFUL, $blockArr[$idx]->get('name')));
			}
			else {
			}
			Legacy_ModuleInstallUtils::uninstallBlockTemplate($blockArr[$idx], $module, $log);
		}
	}
	function _uninstallBlockTemplate(&$block, &$module, $tplset, &$log)
	{
		$handler =& xoops_gethandler('tplfile');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('tpl_refid', $block->get('bid')));
		$criteria->add(new Criteria('tpl_file', $block->get('template')));
		$criteria->add(new Criteria('tpl_module', $module->get('dirname')));
		$criteria->add(new Criteria('tpl_type', 'block'));
		if ($tplset != null) {
			$criteria->add(new Criteria('tpl_tplset', $tplset));
		}
		$handler->deleteAll($criteria);
	}
	function uninstallBlockTemplate(&$block, &$module, &$log)
	{
		Legacy_ModuleInstallUtils::_uninstallBlockTemplate($block, $module, null, $log);
	}
	function clearBlockTemplateForUpdate(&$block, &$module, &$log)
	{
		Legacy_ModuleInstallUtils::_uninstallBlockTemplate($block, $module, 'default', $log);
	}
	function uninstallPreferenceByOrder($order, &$module, &$log)
	{
		$handler =& xoops_gethandler('config');
		$criteria =& new CriteriaCompo();		
		$criteria->add(new Criteria('conf_modid', $module->get('mid')));
		$criteria->add(new Criteria('conf_catid', 0));
		$criteria->add(new Criteria('conf_order', $order));
		$configArr =& $handler->getConfigs($criteria);
		foreach (array_keys($configArr) as $idx) {
			if ($handler->deleteConfig($configArr[$idx])) {
				$log->addReport(XCube_Utils::formatMessage("Delete preference '{0}'.", $configArr[$idx]->get('conf_name')));
			}
			else {
				$log->addError(XCube_Utils::formatMessage("Could not delete preference '{0}'.", $configArr[$idx]->get('conf_name')));
			}
		}
	}
	function DBquery($query, &$module, $log)
	{
		require_once XOOPS_MODULE_PATH . "/legacy/admin/class/Legacy_SQLScanner.class.php";
		$successFlag = true;
		$scanner =& new Legacy_SQLScanner();
		$scanner->setDB_PREFIX(XOOPS_DB_PREFIX);
		$scanner->setDirname($module->get('dirname'));
		$scanner->setBuffer($query);
		$scanner->parse();
		$sqlArr = $scanner->getSQL();
		$root =& XCube_Root::getSingleton();
		foreach ($sqlArr as $sql) {		
			if ($root->mController->mDB->query($sql)) {
				$log->addReport("Success: ${sql}");
				$successFlag &= true;
			}
			else {
				$log->addError("Failure: ${sql}");
				$successFlag = false;
			}
		}
		return $successFlag;
	}
	function deleteAllOfNotifications(&$module, &$log)
	{
		$handler =& xoops_gethandler('notification');
		$criteria =& new Criteria('not_modid', $module->get('mid'));
		$handler->deleteAll($criteria);
	}
	function deleteAllOfComments(&$module, &$log)
	{
		$handler =& xoops_gethandler('comment');
		$criteria =& new Criteria('com_modid', $module->get('mid'));
		$handler->deleteAll($criteria);
	}
}
?>

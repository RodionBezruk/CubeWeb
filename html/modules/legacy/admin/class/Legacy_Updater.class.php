<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_LEGACY_PATH . '/admin/class/ModuleUpdater.class.php';
class Legacy_ModuleUpdater extends Legacy_ModulePhasedUpgrader
{
    var $_mMilestone = array(
        '106' => 'update106'
    );
    function update106()
    {
        $this->mLog->addReport(_AD_LEGACY_MESSAGE_UPDATE_STARTED);
        $this->_setUniqueToGroupUserLink();
        $this->_recoverXoopsGroupPermission();
        if (!$this->_mForceMode && $this->mLog->hasError())
        {
            $this->_processReport();
            return false;
        }
        $this->_updateModuleTemplates();
        if (!$this->_mForceMode && $this->mLog->hasError())
        {
            $this->_processReport();
            return false;
        }
        $this->_updateBlocks();
        if (!$this->_mForceMode && $this->mLog->hasError())
        {
            $this->_processReport();
            return false;
        }
        $this->_updatePreferences();
        if (!$this->_mForceMode && $this->mLog->hasError())
        {
            $this->_processReport();
            return false;
        }
        $this->saveXoopsModule($this->_mTargetXoopsModule);
        if (!$this->_mForceMode && $this->mLog->hasError())
        {
            $this->_processReport();
            return false;
        }
        $this->_processScript();
        if (!$this->_mForceMode && $this->mLog->hasError())
        {
            $this->_processReport();
            return false;
        }
        $this->_processReport();
        return true;
    }
    function _setUniqueToGroupUserLink()
    {
        $root =& XCube_Root::getSingleton();
        $db =& $root->mController->getDB();
        $table = $db->prefix('groups_users_link');
        $sql = 'SELECT `uid`,`groupid`,COUNT(*) AS c FROM `' . $table . '` GROUP BY `uid`,`groupid` HAVING `c` > 1';
        if ($res = $db->query($sql))
        {
            while ($row = $db->fetchArray($res))
            {
                $sql = sprintf('DELETE FROM `%s` WHERE `uid` = %d AND `groupid` = %d ORDER BY `linkid` DESC', $table, $row['uid'], $row['groupid']);
                if (!$db->query($sql, $row['c'] - 1))
                {
                    $this->mLog->addError(XCube_Utils::formatString(_AD_LEGACY_ERROR_COULD_NOT_DELETE_DUPLICATE_DATA, $table));
                    return;
                }
            }
        }
        $sql = 'ALTER TABLE `' . $table . '` DROP INDEX `groupid_uid`, ADD UNIQUE `uid_groupid` (`uid`,`groupid`)';
        if ($db->query($sql))
        {
            $this->mLog->addReport(XCube_Utils::formatString(_AD_LEGACY_MESSAGE_SET_UNIQUE_KEY_SUCCESSFUL, $table));
        }
        else
        {
            $this->mLog->addError(XCube_Utils::formatString(_AD_LEGACY_ERROR_COULD_NOT_SET_UNIQUE_KEY, $table));
        }
    }
    function _recoverXoopsGroupPermission()
    {
        $root =& XCube_Root::getSingleton();
        $db =& $root->mController->getDB();
        $permTable = $db->prefix('group_permission');
        $groupTable = $db->prefix('groups');
		$sql = sprintf("SELECT DISTINCT `gperm_groupid` FROM `%s` LEFT JOIN `%s` ON `%s`.`gperm_groupid`=`%s`.`groupid`" .
		               " WHERE `gperm_modid`=1 AND `groupid` IS NULL",
		               $permTable, $groupTable, $permTable, $groupTable);
		$result = $db->query($sql);
		if (!$result) {
			return false;
		}
		$gids = array();
		while ($myrow = $db->fetchArray($result)) {
			$gids[] = $myrow['gperm_groupid'];
		}
		$db->freeRecordSet( $result );
		if (count( $gids ) != 0) {
			$sql = sprintf('DELETE FROM `%s` WHERE `gperm_groupid` IN (%s) AND `gperm_modid`=1',
			               $permTable, implode(',', $gids));
			$result = $xoopsDB->query($sql);
			if (!$result) {
				return false;
			}
		}
		return true;
    }
}
?>

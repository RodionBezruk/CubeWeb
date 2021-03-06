<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class LegacyGroup_permissionObject extends XoopsSimpleObject
{
	function LegacyGroup_permissionObject()
	{
		$this->initVar('gperm_id', XOBJ_DTYPE_INT, '', true);
		$this->initVar('gperm_groupid', XOBJ_DTYPE_INT, '0', true);
		$this->initVar('gperm_itemid', XOBJ_DTYPE_INT, '0', true);
		$this->initVar('gperm_modid', XOBJ_DTYPE_INT, '0', true);
		$this->initVar('gperm_name', XOBJ_DTYPE_STRING, '', true, 50);
	}
}
class LegacyGroup_permissionHandler extends XoopsObjectGenericHandler
{
	var $mTable = "group_permission";
	var $mPrimary = "gperm_id";
	var $mClass = "LegacyGroup_permissionObject";
	function getRolesByModule($mid, $groups)
	{
		$retRoles = array();
		$sql = "SELECT gperm_name FROM " . $this->mTable . " WHERE gperm_modid=" . intval($mid) . " AND gperm_itemid=0 AND ";
		$groupSql = array();
		foreach ($groups as $gid) {
			$groupSql[] = "gperm_groupid=" . intval($gid);
		}
		$sql .= "(" . implode(' OR ', $groupSql) . ")";
		$result = $this->db->query($sql);
		if (!$result) {
			return $retRoles;
		}
		while ($row = $this->db->fetchArray($result)) {
			$retRoles[] = $row['gperm_name'];
		}
		return $retRoles;
	}
}
?>

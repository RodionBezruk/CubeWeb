<?php
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}
if (!defined('SHOW_SIDEBLOCK_LEFT')) {
    define ('SHOW_SIDEBLOCK_LEFT',     1);
    define ('SHOW_SIDEBLOCK_RIGHT',    2);
    define ('SHOW_CENTERBLOCK_LEFT',   4);
    define ('SHOW_CENTERBLOCK_RIGHT',  8);
    define ('SHOW_CENTERBLOCK_CENTER', 16);
    define ('SHOW_BLOCK_ALL',          31);
}
class XoopsBlock extends XoopsObject
{
	var $mBlockFlagMapping = array();
    function XoopsBlock($id = null)
    {
        $this->initVar('bid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('mid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('func_num', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('options', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 150);
        $this->initVar('title', XOBJ_DTYPE_TXTBOX, null, false, 150);
        $this->initVar('content', XOBJ_DTYPE_TXTAREA, null, false);
        $this->initVar('side', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('weight', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('visible', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('block_type', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('c_type', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('isactive', XOBJ_DTYPE_INT, null, false);
        $this->initVar('dirname', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('func_file', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('show_func', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('edit_func', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('template', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('bcachetime', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('last_modified', XOBJ_DTYPE_INT, time(), false);
        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            } else {
                $this->load($id);
            }
        }
		$this->mBlockFlagMapping = array(
			0 => false,
			SHOW_SIDEBLOCK_LEFT => 0,
			SHOW_SIDEBLOCK_RIGHT => 1,
			SHOW_CENTERBLOCK_LEFT => 3,
			SHOW_CENTERBLOCK_RIGHT => 4,
			SHOW_CENTERBLOCK_CENTER => 5
		);
    }
    function &getContent($format = 'S', $c_type = 'T')
    {
		$ret = null;
        switch ( $format ) {
        case 'S':
            if ( $c_type == 'H' ) {
                $ret = str_replace('{X_SITEURL}', XOOPS_URL.'/', $this->getVar('content', 'N'));
            } elseif ( $c_type == 'P' ) {
                ob_start();
                echo eval($this->getVar('content', 'N'));
                $content = ob_get_contents();
                ob_end_clean();
                $ret = str_replace('{X_SITEURL}', XOOPS_URL.'/', $content);
            } elseif ( $c_type == 'S' ) {
                $myts =& MyTextSanitizer::getInstance();
                $ret = str_replace('{X_SITEURL}', XOOPS_URL.'/', $myts->displayTarea($this->getVar('content', 'N'), 1, 1));
            } else {
                $myts =& MyTextSanitizer::getInstance();
                $ret = str_replace('{X_SITEURL}', XOOPS_URL.'/', $myts->displayTarea($this->getVar('content', 'N'), 1, 0));
            }
            break;
        case 'E':
            $ret = $this->getVar('content', 'E');
            break;
        default:
            $ret = $this->getVar('content', 'N');
            break;
        }
		return $ret;
    }
    function &buildBlock()
    {
        $ret = false;
        $block = array();
        if ( $this->getVar('block_type') != 'C' ) {
            $show_func = $this->getVar('show_func');
            if ( !$show_func ) {
                return $ret;
            }
            if ( file_exists(XOOPS_ROOT_PATH.'/modules/'.$this->getVar('dirname').'/blocks/'.$this->getVar('func_file')) ) {
                $root=&XCube_Root::getSingleton();
                $root->mLanguageManager->loadBlockMessageCatalog($this->getVar('dirname'));
                require_once XOOPS_ROOT_PATH.'/modules/'.$this->getVar('dirname').'/blocks/'.$this->getVar('func_file');
                $options = explode('|', $this->getVar('options'));
                if ( function_exists($show_func) ) {
                    $block = $show_func($options);
                    if ( !$block ) {
                        return $ret;
                    }
                } else {
                    return $ret;
                }
            } else {
                return $ret;
            }
        } else {
            $block['content'] = $this->getContent('S',$this->getVar('c_type'));
            if (empty($block['content'])) {
                return $ret;
            }
        }
        return $block;
    }
    function &buildContent($position,$content="",$contentdb="")
    {
        if ( $position == 0 ) {
            $ret = $contentdb.$content;
        } elseif ( $position == 1 ) {
            $ret = $content.$contentdb;
        }
        return $ret;
    }
    function &buildTitle($originaltitle, $newtitle="")
    {
        if ($newtitle != "") {
            $ret = $newtitle;
        } else {
            $ret = $originaltitle;
        }
        return $ret;
    }
    function isCustom()
    {
        if ( $this->getVar('block_type') == 'C' ) {
            return true;
        }
        return false;
    }
    function getOptions()
    {
        if ($this->getVar('block_type') != 'C') {
            $edit_func = $this->getVar('edit_func');
            if (!$edit_func) {
                return false;
            }
            if (file_exists(XOOPS_ROOT_PATH.'/modules/'.$this->getVar('dirname').'/blocks/'.$this->getVar('func_file'))) {
				$root =& XCube_Root::getSingleton();
				$root->mLanguageManager->loadBlockMessageCatalog($this->getVar('dirname'));
                include_once XOOPS_ROOT_PATH.'/modules/'.$this->getVar('dirname').'/blocks/'.$this->getVar('func_file');
                $options = explode('|', $this->getVar('options'));
                $edit_form = $edit_func($options);
                if (!$edit_form) {
                    return false;
                }
                return $edit_form;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    function load($id) 
    {
        $handler =& xoops_gethandler('block');
        if ($obj =& $handler->get($id)) {
            foreach ($obj->vars as $k => $v) {
                $this->assignVar($k, $v['value']);
            }
        }
    }
    function store()
    {
        $handler =& xoops_gethandler('block');
        if($handler->insert($this)) {
            return $this->getVar('bid');
        } else {
            return false;
        }
    }
    function delete()
    {
        $handler =& xoops_gethandler('block');
        return $handler->delete($this);
    }
    function &getAllBlocksByGroup($groupid, $asobject=true, $side=null, $visible=null, $orderby="b.weight,b.bid", $isactive=1)
    {
        $handler =& xoops_gethandler('block');
        $ret =& $handler->getAllBlocksByGroup($groupid, $asobject, $side, $visible, $orderby, $isactive);
        return $ret;
    }
    function &getAllBlocks($rettype="object", $side=null, $visible=null, $orderby="side,weight,bid", $isactive=1)
    {
        $handler =& xoops_gethandler('block');
        $ret =& $handler->getAllBlocks($rettype, $side, $visible, $orderby, $isactive);
        return $ret;
    }
    function &getByModule($moduleid, $asobject=true)
    {
        $handler =& xoops_gethandler('block');
        $ret =& $handler->getByModule($moduleid, $asobject);
        return $ret;
    }
    function &getAllByGroupModule($groupid, $module_id=0, $toponlyblock=false, $visible=null, $orderby='b.weight,b.bid', $isactive=1)
    {
        $handler =& xoops_gethandler('block');
        $ret =& $handler->getAllByGroupModule($groupid, $module_id, $toponlyblock, $visible, $orderby, $isactive);
        return $ret;
    }
	function &getBlocks($groupid, $mid=false, $blockFlag=SHOW_BLOCK_ALL, $orderby='b.weight,b.bid')
    {
        $handler =& xoops_gethandler('block');
        $ret =& $handler->getBlocks($groupid, $mid, $blockFlag, $orderby);
        return $ret;
    }
    function &getNonGroupedBlocks($module_id=0, $toponlyblock=false, $visible=null, $orderby='b.weight,b.bid', $isactive=1)
    {
        $handler =& xoops_gethandler('block');
        $ret =& $handler->getNonGroupedBlocks($module_id, $toponlyblock, $visible, $orderby, $isactive);
        return $ret;
    }
    function countSimilarBlocks($moduleId, $funcNum, $showFunc = null)
    {
        $handler =& xoops_gethandler('block');
        $ret =& $handler->countSimilarBlocks($moduleId, $funcNum, $showFunc);
        return $ret;
    }
}
class XoopsBlockHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $block = new XoopsBlock();
        if ($isNew) {
            $block->setNew();
        }
        return $block;
    }
	function &createByInfo($info)
	{
		$block =& $this->create();
		$options=isset($info['options']) ? $info['options'] : null;
		$edit_func=isset($info['edit_func']) ? $info['edit_func'] : null;
		$block->setVar('options',$options);
		$block->setVar('name',$info['name']);
		$block->setVar('title',$info['name']);
		$block->setVar('block_type','M');
		$block->setVar('c_type',1);
		$block->setVar('func_file',$info['file']);
		$block->setVar('show_func',$info['show_func']);
		$block->setVar('edit_func',$edit_func);
		$block->setVar('template',$info['template']);
		$block->setVar('last_modified',time());
		return $block;
	}
    function &get($id)
    {
        $id = intval($id);
        if ($id > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('newblocks').' WHERE bid='.$id;
            if (!$result = $this->db->query($sql)) {
				$ret = false;	
				return $ret;
            }
            $numrows = $this->db->getRowsNum($result);
            if ($numrows == 1) {
                $block = new XoopsBlock();
                $block->assignVars($this->db->fetchArray($result));
                return $block;
            }
        }
		$ret = false;	
        return $ret;
    }
    function insert(&$block, $autolink=false)
    {
        if (strtolower(get_class($block)) != 'xoopsblock') {
            return false;
        }
        if (!$block->isDirty()) {
            return true;
        }
        if (!$block->cleanVars()) {
            return false;
        }
        foreach ($block->cleanVars as $k => $v) {
            ${$k} = $v;
        }
		$isNew = false;
        if ($block->isNew()) {
			$isNew = true;
            $bid = $this->db->genId('newblocks_bid_seq');
            $sql = sprintf("INSERT INTO %s (bid, mid, func_num, options, name, title, content, side, weight, visible, block_type, c_type, isactive, dirname, func_file, show_func, edit_func, template, bcachetime, last_modified) VALUES (%u, %u, %u, %s, %s, %s, %s, %u, %u, %u, %s, %s, %u, %s, %s, %s, %s, %s, %u, %u)", $this->db->prefix('newblocks'), $bid, $mid, $func_num, $this->db->quoteString($options), $this->db->quoteString($name), $this->db->quoteString($title), $this->db->quoteString($content), $side, $weight, $visible, $this->db->quoteString($block_type), $this->db->quoteString($c_type), 1, $this->db->quoteString($dirname), $this->db->quoteString($func_file), $this->db->quoteString($show_func), $this->db->quoteString($edit_func), $this->db->quoteString($template), $bcachetime, time());
        } else {
            $sql = sprintf("UPDATE %s SET func_num = %u, options = %s, name = %s, title = %s, content = %s, side = %u, weight = %u, visible = %u, c_type = %s, isactive = %u, func_file = %s, show_func = %s, edit_func = %s, template = %s, bcachetime = %u, last_modified = %u WHERE bid = %u", $this->db->prefix('newblocks'), $func_num, $this->db->quoteString($options), $this->db->quoteString($name), $this->db->quoteString($title), $this->db->quoteString($content), $side, $weight, $visible, $this->db->quoteString($c_type), $isactive, $this->db->quoteString($func_file), $this->db->quoteString($show_func), $this->db->quoteString($edit_func), $this->db->quoteString($template), $bcachetime, time(), $bid);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        if (empty($bid)) {
            $bid = $this->db->getInsertId();
        }
        $block->assignVar('bid', $bid);
		if ($isNew && $autolink) {
			$link_sql = "INSERT INTO " . $this->db->prefix('block_module_link') . " (block_id, module_id) VALUES (${bid}, -1)";
			return $this->db->query($link_sql);
		}
        return true;
    }
    function delete(&$block)
    {
        if (strtolower(get_class($block)) != 'xoopsblock') {
            return false;
        }
        $id = $block->getVar('bid');
        $sql = sprintf("DELETE FROM %s WHERE bid = %u", $this->db->prefix('newblocks'), $id);
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE block_id = %u", $this->db->prefix('block_module_link'), $id);
        $this->db->query($sql);
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT DISTINCT(b.*) FROM '.$this->db->prefix('newblocks').' b LEFT JOIN '.$this->db->prefix('block_module_link').' l ON b.bid=l.block_id';
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $block =& $this->create(false);
            $block->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $block;
            } else {
                $ret[$myrow['bid']] =& $block;
            }
            unset($block);
        }
        return $ret;
    }
	function &getObjectsDirectly($criteria = null)
	{
		$ret = array();
		$limit = 0;
		$start = 0;
		$sql = "SELECT * FROM " . $this->db->prefix('newblocks');
		if ($criteria)
			$sql .= " " . $criteria->renderWhere();
		$result = $this->db->query($sql);
		if (!$result) {
			return $ret;
		}
		while ($row = $this->db->fetchArray($result)) {
			$block =& $this->create(false);
			$block->assignVars($row);
			$ret[] =& $block;
			unset($block);
		}
		return $ret;
	}
    function &getList($criteria = null)
    {
        $blocks =& $this->getObjects($criteria, true);
        $ret = array();
        foreach (array_keys($blocks) as $i) {
            $name = ($blocks[$i]->getVar('block_type') != 'C') ? $blocks[$i]->getVar('name') : $blocks[$i]->getVar('title');
            $ret[$i] = $name;
        }
        return $ret;
    }
    function &getAllBlocksByGroup($groupid, $asobject=true, $side=null, $visible=null, $orderby="b.weight,b.bid", $isactive=1)
    {
        $ret = array();
        if ( !$asobject ) {
            $sql = "SELECT b.bid ";
        } else {
            $sql = "SELECT b.* ";
        }
        $sql .= "FROM ".$this->db->prefix("newblocks")." b LEFT JOIN ".$this->db->prefix("group_permission")." l ON l.gperm_itemid=b.bid WHERE gperm_name = 'block_read' AND gperm_modid = 1";
        if ( is_array($groupid) ) {
            $sql .= " AND (l.gperm_groupid=".intval($groupid[0])."";
            $size = count($groupid);
            if ( $size  > 1 ) {
                for ( $i = 1; $i < $size; $i++ ) {
                    $sql .= " OR l.gperm_groupid=".intval($groupid[$i])."";
                }
            }
            $sql .= ")";
        } else {
            $sql .= " AND l.gperm_groupid=".intval($groupid)."";
        }
        $sql .= " AND b.isactive=".intval($isactive);
        if ( isset($side) ) {
            $side = intval($side);
            if ( $side == XOOPS_SIDEBLOCK_BOTH ) {
                $side = "(b.side=0 OR b.side=1)";
            } elseif ( $side == XOOPS_CENTERBLOCK_ALL ) {
                $side = "(b.side=3 OR b.side=4 OR b.side=5)";
            } else {
                $side = "b.side=".$side;
            }
            $sql .= " AND ".$side;
        }
        if ( isset($visible) ) {
            $sql .= " AND b.visible=".intval($visible);
        }
        $sql .= " ORDER BY ".addslashes($orderby);
        $result = $this->db->query($sql);
        $added = array();
        while ( $myrow = $this->db->fetchArray($result) ) {
            if ( !in_array($myrow['bid'], $added) ) {
                if (!$asobject) {
                    $ret[] = $myrow['bid'];
                } else {
                    $block =& $this->create(false);
                    $block->assignVars($myrow);
                    $ret[] =& $block;
                }
                array_push($added, $myrow['bid']);
            }
        }
        return $ret;
    }
    function &getAllBlocks($rettype="object", $side=null, $visible=null, $orderby="side,weight,bid", $isactive=1)
    {
        $ret = array();
        $where_query = " WHERE isactive=".intval($isactive);
        if ( isset($side) ) {
            $side = intval($side);
            if ( $side == 2 ) {
                $side = "(side=0 OR side=1)";
            } elseif ( $side == 6 ) {
                $side = "(side=3 OR side=4 OR side=5)";
            } else {
                $side = "side=".$side;
            }
            $where_query .= " AND ".$side;
        }
        if ( isset($visible) ) {
            $visible = intval($visible);
            $where_query .= " AND visible=$visible";
        }
        $where_query .= " ORDER BY ".addslashes($orderby);
        switch ($rettype) {
        case "object":
            $sql = "SELECT * FROM ".$this->db->prefix("newblocks")."".$where_query;
            $result = $this->db->query($sql);
            while ( $myrow = $this->db->fetchArray($result) ) {
                $block =& $this->create(false);
                $block->assignVars($myrow);
                $ret[] =& $block;
            }
            break;
        case "list":
            $sql = "SELECT * FROM ".$this->db->prefix("newblocks")."".$where_query;
            $result = $this->db->query($sql);
            while ( $myrow = $this->db->fetchArray($result) ) {
                $block =& $this->create(false);
                $block->assignVars($myrow);
                $name = ($block->getVar("block_type") != "C") ? $block->getVar("name") : $block->getVar("title");
                $ret[$block->getVar("bid")] = $name;
                unset($block);
            }
            break;
        case "id":
            $sql = "SELECT bid FROM ".$this->db->prefix("newblocks")."".$where_query;
            $result = $this->db->query($sql);
            while ( $myrow = $this->db->fetchArray($result) ) {
                $ret[] = $myrow['bid'];
            }
            break;
        }
        return $ret;
    }
    function &getByModule($moduleid, $asobject=true)
    {
        $moduleid = intval($moduleid);
        if ( $asobject == true ) {
            $sql = $sql = "SELECT * FROM ".$this->db->prefix("newblocks")." WHERE mid=".$moduleid."";
        } else {
            $sql = "SELECT bid FROM ".$this->db->prefix("newblocks")." WHERE mid=".$moduleid."";
        }
        $result = $this->db->query($sql);
        $ret = array();
        while( $myrow = $this->db->fetchArray($result) ) {
            if ( $asobject ) {
                $block =& $this->create(false);
                $block->assignVars($myrow);
                $ret[] =& $block;
            } else {
                $ret[] = $myrow['bid'];
            }
        }
        return $ret;
    }
    function &getAllByGroupModule($groupid, $module_id=0, $toponlyblock=false, $visible=null, $orderby='b.weight,b.bid', $isactive=1)
    {
        $ret = array();
        $sql = "SELECT DISTINCT gperm_itemid FROM ".$this->db->prefix('group_permission')." WHERE gperm_name = 'block_read' AND gperm_modid = 1";
        if ( is_array($groupid) ) {
            $sql .= ' AND gperm_groupid IN ('.addslashes(implode(',', array_map('intval', $groupid))).')';
        } else {
            if (intval($groupid) > 0) {
                $sql .= ' AND gperm_groupid='.intval($groupid);
            }
        }
        $result = $this->db->query($sql);
        $blockids = array();
        while ( $myrow = $this->db->fetchArray($result) ) {
            $blockids[] = $myrow['gperm_itemid'];
        }
        if (!empty($blockids)) {
            $sql = 'SELECT b.* FROM '.$this->db->prefix('newblocks').' b, '.$this->db->prefix('block_module_link').' m WHERE m.block_id=b.bid';
            $sql .= ' AND b.isactive='.$isactive;
            if (isset($visible)) {
                $sql .= ' AND b.visible='.intval($visible);
            }
            if ($module_id !== false) {
                $sql .= ' AND m.module_id IN (0,'.intval($module_id);
                if ($toponlyblock) {
                    $sql .= ',-1';
                }
                $sql .= ')';
            } else {
                if ($toponlyblock) {
                    $sql .= ' AND m.module_id IN (0,-1)';
                } else {
                    $sql .= ' AND m.module_id=0';
                }
            }
            $sql .= ' AND b.bid IN ('.implode(',', $blockids).')';
            $sql .= ' ORDER BY '.$orderby;
            $result = $this->db->query($sql);
            while ( $myrow = $this->db->fetchArray($result) ) {
                $block =& $this->create(false);
                $block->assignVars($myrow);
                $ret[$myrow['bid']] =& $block;
                unset($block);
            }
        }
        return $ret;
    }
	function &getBlocks($groupid, $mid=false, $blockFlag=SHOW_BLOCK_ALL, $orderby='b.weight,b.bid')
    {
        $root =& XCube_Root::getSingleton();
        $this->db =& $root->mController->getDB();
        $ret = array();
        $sql = "SELECT DISTINCT gperm_itemid FROM ".$this->db->prefix('group_permission')." WHERE gperm_name = 'block_read' AND gperm_modid = 1";
        if ( is_array($groupid) ) {
            $sql .= ' AND gperm_groupid IN ('.addslashes(implode(',', array_map('intval', $groupid))).')';
        } else {
            if (intval($groupid) > 0) {
                $sql .= ' AND gperm_groupid='.intval($groupid);
            }
        }
        $result = $this->db->query($sql);
        $blockids = array();
        while ( $myrow = $this->db->fetchArray($result) ) {
            $blockids[] = $myrow['gperm_itemid'];
        }
        if (!empty($blockids)) {
            $sql = 'SELECT b.* FROM '.$this->db->prefix('newblocks').' b, '.$this->db->prefix('block_module_link').' m WHERE m.block_id=b.bid';
            $sql .= ' AND b.isactive=1 AND b.visible=1';
            if ($mid !== false && $mid !== 0) {
                $sql .= ' AND m.module_id IN (0,'.intval($mid).')';
            } else {
                $sql .= ' AND m.module_id=0';
            }
            if ($blockFlag != SHOW_BLOCK_ALL) {
				$arr = array();
				if ($blockFlag & SHOW_SIDEBLOCK_LEFT) {
					$arr[] = "b.side=" . $this->mBlockFlagMapping[SHOW_SIDEBLOCK_LEFT];
				}
				if ($blockFlag & SHOW_SIDEBLOCK_RIGHT) {
					$arr[] = "b.side=" . $this->mBlockFlagMapping[SHOW_SIDEBLOCK_RIGHT];
				}
				if ($blockFlag & SHOW_CENTERBLOCK_LEFT) {
					$arr[] = "b.side=" . $this->mBlockFlagMapping[SHOW_CENTERBLOCK_LEFT];
				}
				if ($blockFlag & SHOW_CENTERBLOCK_CENTER) {
					$arr[] = "b.side=" . $this->mBlockFlagMapping[SHOW_CENTERBLOCK_CENTER];
				}
				if ($blockFlag & SHOW_CENTERBLOCK_RIGHT) {
					$arr[] = "b.side=" . $this->mBlockFlagMapping[SHOW_CENTERBLOCK_RIGHT];
				}
				$sql .= " AND (" . implode(" OR ", $arr) . ")";
			}
			$sql .= ' AND b.bid IN ('.implode(',', $blockids).')';
            $sql .= ' ORDER BY '.addslashes($orderby);
            $result = $this->db->query($sql);
            while ( $myrow = $this->db->fetchArray($result) ) {
                $block =& $this->create(false);
                $block->assignVars($myrow);
                $ret[$myrow['bid']] =& $block;
                unset($block);
            }
        }
        return $ret;
    }
    function &getNonGroupedBlocks($module_id=0, $toponlyblock=false, $visible=null, $orderby='b.weight,b.bid', $isactive=1)
    {
        $ret = array();
        $bids = array();
        $sql = "SELECT DISTINCT(bid) from ".$this->db->prefix('newblocks');
        if ($result = $this->db->query($sql)) {
            while ( $myrow = $this->db->fetchArray($result) ) {
                $bids[] = $myrow['bid'];
            }
        }
        $sql = "SELECT DISTINCT(p.gperm_itemid) from ".$this->db->prefix('group_permission')." p, ".$this->db->prefix('groups')." g WHERE g.groupid=p.gperm_groupid AND p.gperm_name='block_read'";
        $grouped = array();
        if ($result = $this->db->query($sql)) {
            while ( $myrow = $this->db->fetchArray($result) ) {
                $grouped[] = $myrow['gperm_itemid'];
            }
        }
        $non_grouped = array_diff($bids, $grouped);
        if (!empty($non_grouped)) {
            $sql = 'SELECT b.* FROM '.$this->db->prefix('newblocks').' b, '.$this->db->prefix('block_module_link').' m WHERE m.block_id=b.bid';
            $sql .= ' AND b.isactive='.intval($isactive);
            if (isset($visible)) {
                $sql .= ' AND b.visible='.intval($visible);
            }
            $module_id = intval($module_id);
            if (!empty($module_id)) {
                $sql .= ' AND m.module_id IN (0,'.$module_id;
                if ($toponlyblock) {
                    $sql .= ',-1';
                }
                $sql .= ')';
            } else {
                if ($toponlyblock) {
                    $sql .= ' AND m.module_id IN (0,-1)';
                } else {
                    $sql .= ' AND m.module_id=0';
                }
            }
            $sql .= ' AND b.bid IN ('.implode(',', $non_grouped).')';
            $sql .= ' ORDER BY '.addslashes($orderby);
            $result = $this->db->query($sql);
            while ( $myrow = $this->db->fetchArray($result) ) {
                $block =& $this->create(false);
                $block->assignVars($myrow);
                $ret[$myrow['bid']] =& $block;
                unset($block);
            }
        }
        return $ret;
    }
    function countSimilarBlocks($moduleId, $funcNum, $showFunc = null)
    {
        $funcNum = intval($funcNum);
        $moduleId = intval($moduleId);
        if ($funcNum < 1 || $moduleId < 1) {
            return 0;
        }
        if (isset($showFunc)) {
            $sql = sprintf("SELECT COUNT(*) FROM %s WHERE mid = %d AND func_num = %d AND show_func = %s", $this->db->prefix('newblocks'), $moduleId, $funcNum, $this->db->quoteString(trim($showFunc)));
        } else {
            $sql = sprintf("SELECT COUNT(*) FROM %s WHERE mid = %d AND func_num = %d", $this->db->prefix('newblocks'), $moduleId, $funcNum);
        }
        if (!$result = $this->db->query($sql)) {
            return 0;
        }
        list($count) = $this->db->fetchRow($result);
        return $count;
    }
    function syncIsActive($moduleId, $isActive, $force = false)
    {
    	$this->db->prepare("UPDATE " . $this->db->prefix('newblocks') . " SET isactive=? WHERE mid=?");
    	$this->db->bind_param("ii", $isActive, $moduleId);
    	if ($force) {
			$this->db->executeF();
    	}
    	else {
			$this->db->execute();
    	}
    }
}
?>

<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
class XoopsImage extends XoopsObject
{
	function XoopsImage()
	{
		$this->XoopsObject();
		$this->initVar('image_id', XOBJ_DTYPE_INT, null, false);
		$this->initVar('image_name', XOBJ_DTYPE_OTHER, null, false, 30);
		$this->initVar('image_nicename', XOBJ_DTYPE_TXTBOX, null, true, 100);
		$this->initVar('image_mimetype', XOBJ_DTYPE_OTHER, null, false);
		$this->initVar('image_created', XOBJ_DTYPE_INT, null, false);
		$this->initVar('image_display', XOBJ_DTYPE_INT, 1, false);
		$this->initVar('image_weight', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('image_body', XOBJ_DTYPE_SOURCE, null, true);
		$this->initVar('imgcat_id', XOBJ_DTYPE_INT, 0, false);
	}
}
class XoopsImageHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $image =& new XoopsImage();
        if ($isNew) {
            $image->setNew();
        }
        return $image;
    }
    function &get($id, $getbinary=true)
    {
        $ret = false;
        $id = intval($id);
        if ($id > 0) {
            $sql = 'SELECT i.*, b.image_body FROM '.$this->db->prefix('image').' i LEFT JOIN '.$this->db->prefix('imagebody').' b ON b.image_id=i.image_id WHERE i.image_id='.$id;
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                        $image =& new XoopsImage();
                    $image->assignVars($this->db->fetchArray($result));
                        $ret =& $image;
                }
            }
        }
        return $ret;
    }
    function insert(&$image)
    {
        if (strtolower(get_class($image)) != 'xoopsimage') {
            return false;
        }
        if (!$image->isDirty()) {
            return true;
        }
        if (!$image->cleanVars()) {
            return false;
        }
        foreach ($image->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($image->isNew()) {
            $image_id = $this->db->genId('image_image_id_seq');
            $sql = sprintf("INSERT INTO %s (image_id, image_name, image_nicename, image_mimetype, image_created, image_display, image_weight, imgcat_id) VALUES (%u, %s, %s, %s, %u, %u, %u, %u)", $this->db->prefix('image'), $image_id, $this->db->quoteString($image_name), $this->db->quoteString($image_nicename), $this->db->quoteString($image_mimetype), time(), $image_display, $image_weight, $imgcat_id);
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            if (empty($image_id)) {
                $image_id = $this->db->getInsertId();
            }
            if (isset($image_body) && $image_body != '') {
                $sql = sprintf("INSERT INTO %s (image_id, image_body) VALUES (%u, %s)", $this->db->prefix('imagebody'), $image_id, $this->db->quoteString($image_body));
                if (!$result = $this->db->query($sql)) {
                    $sql = sprintf("DELETE FROM %s WHERE image_id = %u", $this->db->prefix('image'), $image_id);
                    $this->db->query($sql);
                    return false;
                }
            }
            $image->assignVar('image_id', $image_id);
        } else {
            $sql = sprintf("UPDATE %s SET image_name = %s, image_nicename = %s, image_display = %u, image_weight = %u, imgcat_id = %u WHERE image_id = %u", $this->db->prefix('image'), $this->db->quoteString($image_name), $this->db->quoteString($image_nicename), $image_display, $image_weight, $imgcat_id, $image_id);
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            if (isset($image_body) && $image_body != '') {
                $sql = sprintf("UPDATE %s SET image_body = %s WHERE image_id = %u", $this->db->prefix('imagebody'), $this->db->quoteString($image_body), $image_id);
                if (!$result = $this->db->query($sql)) {
                    $this->db->query(sprintf("DELETE FROM %s WHERE image_id = %u", $this->db->prefix('image'), $image_id));
                    return false;
                }
            }
        }
        return true;
    }
    function delete(&$image)
    {
        if (strtolower(get_class($image)) != 'xoopsimage') {
            return false;
        }
        $id = $image->getVar('image_id');
        $sql = sprintf("DELETE FROM %s WHERE image_id = %u", $this->db->prefix('image'), $id);
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE image_id = %u", $this->db->prefix('imagebody'), $id);
        $this->db->query($sql);
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false, $getbinary = false)
    {
        $ret = array();
        $limit = $start = 0;
        if ($getbinary) {
            $sql = 'SELECT i.*, b.image_body FROM '.$this->db->prefix('image').' i LEFT JOIN '.$this->db->prefix('imagebody').' b ON b.image_id=i.image_id';
        } else {
            $sql = 'SELECT * FROM '.$this->db->prefix('image');
        }
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            $sort = !in_array($criteria->getSort(), array('image_id', 'image_created', 'image_mimetype', 'image_display', 'image_weight')) ? 'image_weight' : $criteria->getSort();
            $sql .= ' ORDER BY '.$sort.' '.$criteria->getOrder();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $image =& new XoopsImage();
            $image->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $image;
            } else {
                $ret[$myrow['image_id']] =& $image;
            }
            unset($image);
        }
        return $ret;
    }
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('image');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result =& $this->db->query($sql)) {
            return 0;
        }
        list($count) = $this->db->fetchRow($result);
        return $count;
    }
    function &getList($imgcat_id, $image_display = null)
    {
        $criteria = new CriteriaCompo(new Criteria('imgcat_id', intval($imgcat_id)));
        if (isset($image_display)) {
            $criteria->add(new Criteria('image_display', intval($image_display)));
        }
        $images =& $this->getObjects($criteria, false, true);
        $ret = array();
        foreach (array_keys($images) as $i) {
            $ret[$images[$i]->getVar('image_name')] = $images[$i]->getVar('image_nicename');
        }
        return $ret;
    }
}
?>

<?php
function smarty_function_xoops_pagenavi($params, &$smarty)
{
	$ret = "";
	if (isset($params['pagenavi']) && is_object($params['pagenavi'])) {
		$navi =& $params['pagenavi'];
		$perPage = $navi->getPerpage();
		$total = $navi->getTotalItems();
		$totalPages = $navi->getTotalPages();
		if ($totalPages == 0) {
			return;
		}
		$url = $navi->renderURLForPage();
		$current = $navi->getStart();
		$offset = isset($params['offset']) ? intval($params['offset']) : 4;
		if($navi->hasPrivPage()) {
			$ret .= @sprintf("<a href='%s'>&laquo;</a> ", $navi->renderURLForPage($navi->getPrivStart()));
		}
		$counter=1;
		$currentPage = $navi->getCurrentPage();
		while($counter<=$totalPages) {
			if($counter==$currentPage) {
				$ret.=@sprintf("<strong>(%d)</strong> ",$counter);
			}
			elseif(($counter>$currentPage-$offset && $counter<$currentPage+$offset) || $counter==1 || $counter==$totalPages) {
				if($counter==$totalPages && $currentPage<$totalPages-$offset) {
					$ret.="... ";
				}
				$ret .= @sprintf("<a href='%s'>%d</a> ",$navi->renderURLForPage(($counter-1)*$perPage),$counter);
				if($counter==1 && $currentPage>1 + $offset) {
					$ret.="... ";
				}
			}
			$counter++;
		}
		$next=$current + $perPage;
		if($navi->hasNextPage()) {
			$ret.=@sprintf("<a href='%s'>&raquo;</a>",$navi->renderURLForPage($navi->getNextStart()));
		}
	}
	print $ret;
}
?>

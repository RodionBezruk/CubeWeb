<?php
	$root =& XCube_Root::getSingleton();
    $user =& $root->mContext->mUser;
	if ($user->isInRole('Site.Administrator') ) {
$kfm_db_host     = XOOPS_DB_HOST; 
$kfm_db_name     = XOOPS_DB_NAME; 
$kfm_db_username = XOOPS_DB_USER; 
$kfm_db_password = XOOPS_DB_PASS; 
$kfm_userfiles_address = XOOPS_UPLOAD_PATH.'/fckeditor';
	}
	else {
		$root->mController->executeRedirect(XOOPS_URL, 1, "Access Denied!");
    	}	
?>

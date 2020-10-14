<?php
function b_legacy_notification_show()
{
    global $xoopsConfig, $xoopsUser, $xoopsModule;
    include_once XOOPS_ROOT_PATH . '/include/notification_functions.php';
	$root =& XCube_Root::getSingleton();
	$root->mLanguageManager->loadPageTypeMessageCatalog('notification');
    if (empty($xoopsUser) || !notificationEnabled('block')) {
        return false; 
    }
    $notification_handler =& xoops_gethandler('notification');
    $block = array();
    $categories =& notificationSubscribableCategoryInfo();
    if (empty($categories)) {
        return false;
    }
    foreach ($categories as $category) {
        $section['name'] = $category['name'];
        $section['title'] = $category['title'];
        $section['description'] = $category['description'];
        $section['itemid'] = $category['item_id'];
        $section['events'] = array();
        $subscribed_events =& $notification_handler->getSubscribedEvents ($category['name'], $category['item_id'], $xoopsModule->getVar('mid'), $xoopsUser->getVar('uid'));
        foreach (notificationEvents($category['name'], true) as $event) {
            if (!empty($event['admin_only']) && !$xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
                continue;
            }
            $subscribed = in_array($event['name'], $subscribed_events) ? 1 : 0;
            $section['events'][$event['name']] = array ('name'=>$event['name'], 'title'=>$event['title'], 'caption'=>$event['caption'], 'description'=>$event['description'], 'subscribed'=>$subscribed);
        }
        $block['categories'][$category['name']] = $section;
    }
    $block['target_page'] = "notification_update.php";
    $script_url = explode('/', xoops_getenv('PHP_SELF'));
    $script_name = $script_url[count($script_url)-1];
    $block['redirect_script'] = $script_name;
    $block['submit_button'] = _NOT_UPDATENOW;
    return $block;
}
?>

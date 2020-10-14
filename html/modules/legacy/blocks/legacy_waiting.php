<?php
function b_legacy_waiting_show() {
    $modules = array();
    XCube_DelegateUtils::call('Legacyblock.Wating.Show', new XCube_Ref($modules));
    $block['modules'] = $modules;
    return $block;
}
?>

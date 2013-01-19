<?php class_exists('JLoader') or die;

define('EE_PATH', JPATH_LIBRARIES . '/easel');

// Lookup path for EE* classes
JLoader::registerPrefix('EE', EE_PATH);

// Register the lookup path for overidden J* classes
JLoader::registerPrefix('J', EE_PATH . '/joverride/');

JForm::addFieldPath(EE_PATH . '/joverride/form/field');
JForm::addRulePath(EE_PATH . '/joverride/form/rule');

JLoader::register('JImage', EE_PATH . '/joverride/image/image.php');
<?php class_exists('JLoader') or die;

define('EE_PATH', dirname(__FILE__));
define('EE_JPATH', EE_PATH . '/joverride');

// Guarantee the EE_ENV var is set
isset($_SERVER['EE_ENV']) or $_SERVER['EE_ENV'] = 'production';

// define error bitmask
define('EE_NO_ERROR', 1);
define('EE_TABLE_ERROR', 0);
define('EE_FORM_VALIDATION_FAILED', -1);
define('EE_ERROR_UNRECOVERABLE', -998);
define('EE_ERROR_UNKNOWN', -999);

// Lookup path for EE* classes
JLoader::registerPrefix('EE', EE_PATH);

// Register the lookup path for overidden J* classes
JLoader::registerPrefix('J', EE_JPATH);

// Register field and rule paths for JForm
JForm::addFieldPath(EE_JPATH . '/form/field');
JForm::addRulePath(EE_JPATH . '/form/rule');

// Override jimported classes
JLoader::register('JUri', EE_JPATH . '/uri/uri.php');
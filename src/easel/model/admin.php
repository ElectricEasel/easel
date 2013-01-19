<?php defined('EE_PATH') or die;

jimport('joomla.application.component.modeladmin');

abstract class EEModelAdmin extends JModelAdmin
{
	public function __construct($config = array())
	{
		JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/model/forms');
		JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/model/fields');

		parent::__construct($config);
	}
}

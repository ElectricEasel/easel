<?php defined('EE_PATH') or die;

jimport('joomla.application.component.model');

abstract class EEModelForm extends JModelForm
{
	public function __construct($config = array())
	{
		JForm::addFormPath(JPATH_COMPONENT . '/model/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/model/fields');

		parent::__construct($config);
	}
}

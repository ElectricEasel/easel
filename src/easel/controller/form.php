<?php defined('EE_PATH') or die;

jimport('joomla.application.component.controllerform');

class EEControllerForm extends JControllerForm
{
	/**
	 * Run the component. This is syntactic sugar for the
	 * execute and redirect methods.
	 *
	 * @return  void
	 *
	 */
	public function run()
	{
		self::$instance->execute(JFactory::getApplication()->input->get('task'));
		self::$instance->redirect();
	}
}

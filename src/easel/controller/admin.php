<?php defined('EE_PATH') or die;

jimport('joomla.application.component.controlleradmin');

class EEControllerAdmin extends JControllerAdmin
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

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

}

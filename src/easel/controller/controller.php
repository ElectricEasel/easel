<?php defined('EE_PATH') or die;

class EEController extends JControllerLegacy
{
	/**
	 * Override the getInstance method.
	 *
	 * @param   string  $prefix  Prefix for the main component controller
	 * @param   array   $config  Configuration array
	 *
	 * @return  JController
	 */
	public static function getInstance($prefix, array $config = array())
	{
		if (!array_key_exists('model_path', $config))
		{
			$config['model_path'] = JPATH_COMPONENT . '/model';
		}

		return parent::getInstance($prefix, $config);
	}

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

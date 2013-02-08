<?php defined('EE_PATH') or die;

class EEViewBase extends JViewLegacy
{
	protected $state = null;

	public function __construct($config = array())
	{
		$this->state = new JRegistry;
		parent::__construct($config);
	}

	/**
	 * Here, we override the template_path $config to allow for
	 * properly named view files for the autoloader to work
	 *
	 *\/
	public function __construct($config = array())
	{
		if (!array_key_exists('template_path', $config))
		{
			$config['template_path'] = JPATH_COMPONENT . '/view/' . $this->getName() . '/tmpl';
		}

		parent::__construct($config);
	} */

	public function display($tpl = null)
	{
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}
		
		$this->prepareDocument();

		parent::display($tpl);
	}

	protected function prepareDocument()
	{
		return true;
	}

}

<?php defined('EE_PATH') or die;

class EEViewBase extends JViewLegacy
{
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

	protected function prepareDocument()
	{
		return true;
	}

}

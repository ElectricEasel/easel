<?php defined('EE_PATH') or die;

class EEViewEmail extends EEViewBase
{
	protected $_name = 'email';
	public $data = null;

	/**
	 * Syntatic sugar for the loadTemplate() method.
	 * This wording makes more sense.
	 *
	 * @return  string  The rendered email.
	 */
	public function render()
	{
		return $this->loadTemplate();
	}

}

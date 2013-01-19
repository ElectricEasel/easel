<?php defined('EE_PATH') or die;

class EEViewItem extends EEViewBase
{
	protected $item;
	protected $state;

	public function display($tpl = null)
	{
		$this->item = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
            throw new Exception(implode("\n", $errors));
		}
		
		$this->prepareDocument();

		parent::display($tpl);
	}

}

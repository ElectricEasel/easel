<?php defined('EE_PATH') or die;

class EEViewForm extends EEViewBase
{
	protected $form;

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');

		parent::display($tpl);
	}

}

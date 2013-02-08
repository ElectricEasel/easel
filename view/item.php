<?php defined('EE_PATH') or die;

class EEViewItem extends EEViewBase
{
	protected $item;

	public function display($tpl = null)
	{
		$this->item = $this->get('Item');

		parent::display($tpl);
	}

}

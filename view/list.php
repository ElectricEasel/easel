<?php defined('EE_PATH') or die;

class EEViewList extends EEViewBase
{
	protected $items;
	protected $pagination;

	public function display($tpl = null)
	{
		$this->items  = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		parent::display($tpl);
	}

}

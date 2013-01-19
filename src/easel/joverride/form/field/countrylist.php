<?php defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldCountrylist extends JFormFieldList
{
	protected $type = 'countrylist';
	
	public function getOptions()
	{
		$options = array();

		$countryList = array(
			'' => '-- Please Select --',
			'USA'=>"United States",
			'CAN'=>"Canada",
		);

		foreach ($countryList as $abbr => $full)
		{
			$options[] = JHtml::_('select.option', $abbr, $full, 'value', 'text', false);
		}

		return $options;
	}
}

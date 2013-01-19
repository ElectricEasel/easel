<?php
/**
 * @version     1.0.0
 * @package     Easel.form
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Don Gilbert <don@electriceasel.com> - http://www.electriceasel.com
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

JHtml::_('behavior.modal');

/**
 * Supports an HTML select list of categories
 */
class JFormFieldGallery extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Gallery';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$label= (string) $this->element['label'];
		$component = (string) $this->element['component'];
		$field = (string) $this->element['field'];
		$href = "index.php?option={$component}&amp;view=gallery&amp;layout=edit&amp;tmpl=component&amp;{$field}={{{$field}}}";

		$html = array();
		$html[] = '<div class="button2-left">';
		$html[] = '<div class="blank">';
		$html[] = '<a class="modal" title="'.JText::_('JLIB_FORM_BUTTON_SELECT').'"'.' href="'.$href.'"';
		$html[] = ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">'.$label.'</a>';
		$html[] = '</div>';
		$html[] = '</div>';

		return implode($html);
	}
}
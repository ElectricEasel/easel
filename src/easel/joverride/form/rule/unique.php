<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleUnique extends JFormRule
{
	/**
	 * Method to test if two values are equal. To use this rule, the form
	 * XML needs a validate attribute of equals and a field attribute
	 * that is equal to the field to test against.
	 *
	 * @param   SimpleXMLElement  &$element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value     The form field value to validate.
	 * @param   string            $group     The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   JRegistry         &$input    An optional JRegistry object with the entire data set to validate against the entire form.
	 * @param   object            &$form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 * @throws  JException on invalid rule.
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		// Initialize variables.
		$label = (string) $element['label'];
		$table = (string) $element['table'];
		$field = (string) $element['name'];
		$jform = JFactory::getApplication()->input->getVar('jform');
		$id = isset($jform['id']) ? $jform['id'] : null;

		// Check that a validation field is set.
		if (!$table)
		{
			return new JException(JText::sprintf('JLIB_FORM_INVALID_FORM_RULE', get_class($this)));
		}

		// Check that a valid JForm object is given for retrieving the validation field value.
		if (!($form instanceof JForm))
		{
			return new JException(JText::sprintf('JLIB_FORM_INVALID_FORM_OBJECT', get_class($this)));
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('id, title')
			->from($table)
			->where($field . '=' . $db->q($value));

		$result = $db->setQuery($query)->loadObject();

		if (is_null($result) || $result->id == $id)
		{
			return true;
		}

		return new JException(JText::sprintf('%s must be unique.', JText::_($label)));
	}
}

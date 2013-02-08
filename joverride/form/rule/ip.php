<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Rule class for the Joomla Platform.
 * Requires the value entered be one of the options in a field of type="list"
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleIp extends JFormRule
{
	/**
	 * Method to test the value.
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
		$db = JFactory::getDbo();
		$ip = EEHelper::getRealIp();
		$limit = (int) $element['limit'];
		$table = (string) $element['table'];

		if ($ip)
		{
			$q = 'SELECT COUNT(a.id)' .
				' FROM ' . $db->qn($db->escape($table)) . ' AS a' .
				' WHERE a.created_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)' .
				' AND a.ip = ' . $db->q($db->escape($ip));
	
			$result = (int) $db->setQuery($q)->loadResult();
	
			if ($result <= $limit)
			{
				return true;
			}
	
			$msg = 'Too many requests have been submitted from your IP address in the last 60 minutes. Please wait and try again in an hour.';
		}
		else
		{
			$msg = 'We could not determine your IP address. Please check your network connections and try again from a different computer.';
		}

		JError::raise(E_WARNING, ELS_FORM_VALIDATION_FAILED, $msg);
		return false;
	}
}

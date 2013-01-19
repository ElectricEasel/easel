<?php defined('EE_PATH') or die;

abstract class EEHelper
{
	protected static $locations = array();

	/**
	 * Method to format submitted form data in a nice way
	 * for emails.
	 *
	 * @param   array   $data  Array of data to format
	 * @param   object  $form  JForm associated with the data
	 *
	 * @return  string  Form data formatted in email friendly format
	 */
	public static function formatDataForEmail(array $data, JForm $form)
	{
		if (!is_array($data))
		{
			return false;
		}

		$msg = array();

		$msg[] = '<table width="99%" border="0" cellpadding="1" bgcolor="#EAEAEA"><tbody><tr><td>';
		$msg[] = '<table width="100%" border="0" cellpadding="5" bgcolor="#FFFFFF"><tbody>';

		unset($data['antispam']);
		unset($data['spamcheck']);

		foreach ($data as $key => $value)
		{
			// Get the element associated with this submitted field.
			$element = $form->getField($key)->element;

			if ($element)
			{
				$msg[] = '<tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:verdana;font-size:12px;"><strong>';
				$msg[] = $element->getAttribute('label');
				$msg[] = '</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20"></td><td><font style="font-family:verdana;font-size:12px;">';
				$msg[] = $value;
				$msg[] = '</font></td></tr>';
			}
		}

		$msg[] = '</tbody></td></tr></tbody></table>';

		return implode($msg);
	}

	/**
	 * Method to get an RSForm using the rsform content plugin.
	 * This requires the RSForm content plugin to be enabled.
	 *
	 * @param   integer  $id  The ID of the RSForm to display
	 *
	 * @return  mixed    HTML
	 *
	 */
	public static function getRsForm($id = null)
	{
		if ($id === null) return false;

		return JHtml::_('content.prepare', '{rsform ' . $id . '}');
	}

	/**
	 * Load a module position.
	 *
	 * @param   string  $pos  The position to load.
	 *
	 * @return  HTML for the loaded module position.
	 *
	 */
	public static function loadPosition($pos = null)
	{
		if ($pos === null) return false;

		if (JFactory::getDocument()->countModules($pos))
		{
			return JHtml::_('content.prepare', '{loadposition ' . $pos . '}');
		}
	}

	/**
	 * Method to format a text string to a specified character length.
	 * It strips tags, then truncates the remaining text string. Then,
	 * it removes the last word fragment and inserts an elipsis.
	 *
	 * @param   mixed    $content  The content to truncate
	 * @param   integer  $length   The desired content length
	 *
	 * @return  string   The truncated content
	 *
	 */
	public static function formatText($content, $length = 200)
	{
		// Strip tags, truncate to desired length and explode on spaces
		$str = explode(' ', substr(strip_tags($content), 0, $length));

		// Remove the last word, just in case it was truncated and no longer makes sense.
		array_pop($str);

		// Bring the string back together and add an elipsis.
		return nl2br(implode(' ', $str) . '...');
	}

	/**
	 * Take a string input and change the line breaks to new paragraphs.
	 *
	 * @param   string   $string       The text to manipulate
	 * @param   boolean  $line_breaks  Whether to break on 1 line, or 2
	 * @param   boolean  $xhtml        Is this for XHtml output?
	 *
	 * @return  string  Formatted text
	 */
	public static function nl2p($string, $line_breaks = false, $xhtml = true)
	{

		$string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

		// It is conceivable that people might still want single line-breaks
		// without breaking into a new paragraph.
		if ($line_breaks == true)
		{
			$string = preg_replace(
				array("/([\n]{2,})/i", "/([^>])\n([^<])/i"),
				array("</p>\n<p>", '$1<br'.($xhtml == true ? ' /' : '').'>$2'),
				trim($string)
			);
		}
		else
		{
			$string = preg_replace(
				array("/([\n]{2,})/i", "/([\r\n]{3,})/i", "/([^>])\n([^<])/i"),
				array("</p>\n<p>", "</p>\n<p>", '$1<br'.($xhtml == true ? ' /' : '').'>$2'),
				trim($string)
			);
		}

		return '<p>' . $string . '</p>';
	}

	/**
	 * Method to get body classes for template.
	 *
	 * @return  string  Class string for the <body> tag
	 *
	 */
	public static function getBodyClasses()
	{
		$app = JFactory::getApplication();
		$active = $app->getMenu()->getActive();
		$bodyClasses = array();

		$uriParts = explode('/', JUri::getInstance()->toString(array('path')));

		foreach ($uriParts as $part)
		{
			if (!empty($part) && $part !== 'index.php')
			{
				array_push($bodyClasses, strtolower(str_replace(array(' ', '.'), '-', $part)));
			}
		}

		if (!in_array($active->alias, $bodyClasses))
		{
			array_push($bodyClasses, $active->alias);
		}

		return trim(implode(' ', $bodyClasses));
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return JObject
	 * @since 1.6
	 */
	public static function getActions($component)
	{
		$user = JFactory::getUser();
		$result = new JObject;

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $component));
		}

		return $result;
	}

	/**
	 * Method to generate an alias from a title.
	 *
	 * @param   string  $title  The title to turn into an alias.
	 *
	 * @return  string  Sanitized alias for given title.
	 *
	 */
	public static function buildAlias($title)
	{
		return JFilterOutput::stringUrlSafe($title);
	}

	/**
	 * Geocode an address.
	 *
	 * @param   mixed  $params  Array or object containing address info or string address.
	 *
	 * @return  array  Array of longitude or latitude data.
	 */
	public static function getLatLng($params = null)
	{
		$store = md5(json_encode($params));

		if (!isset(self::$locations[$store]))
		{
			if (is_object($params))
			{
				$params = get_object_vars($params);
			}
	
			if (is_array($params))
			{	
				$tmp = array();
	
				$tmp[] = (isset($params['address1'])) ? $params['address1'] : $params['address'];
				$tmp[] = $params['city'];
				$tmp[] = $params['state'];
				$tmp[] = $params['zip'];
							
				$address = implode(',', $tmp);
			}
			else
			{
				$address = $params;
			}
			
			$address = urlencode($address);
			$geocode = file_get_contents("http://maps.google.com/maps/api/geocode/json?address={$address}&sensor=false");
	
			$output = json_decode($geocode);
	
			if ($geocode === false || $output === null || $output->status == 'ZERO_RESULTS')
			{
				$lat = 0;
				$lng = 0;
			}
			else
			{
				$lat = $output->results[0]->geometry->location->lat;
				$lng = $output->results[0]->geometry->location->lng;
			}
		
			self::$locations[$store] = array('latitude' => $lat, 'longitude' => $lng);
		}

		return self::$locations[$store];
	}
}
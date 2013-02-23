<?php defined('EE_PATH') or die;

abstract class EEHelper
{
	protected static $locations = array();
	protected static $ipKeys = array(
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR'
	);

	public function registerViewClasses($views, $prefix)
	{
		foreach ($views as $view)
		{
			$viewClass = ucfirst($prefix) . 'View' . ucfirst($view);
			JLoader::register($viewClass, JPATH_COMPONENT . '/views/' . $view . '/view.html.php');
		}
	}

	/**
	 * When on a dev site, you don't want you site being indexed.
	 *
	 * @return  string  NoIndex meta string if on dev server.
	 */
	public static function noindex()
	{
		$meta = '';

		if ($_SERVER['EE_ENV'] === 'development')
		{
			$meta = '<meta name="robots" content="noindex,nofollow" />';
		}

		return $meta;
	}
		
	public static function getErrorMsg($state)
	{
		switch ($state)
		{
			case EE_TABLE_ERROR:
				$msg = 'There was an error saving your form.';
				break;
			case EE_FORM_VALIDATION_FAILED:
				$msg = 'Your form didn\'t validate. Please fill out all required fields.';
				break;
			case EE_ERROR_UNRECOVERABLE:
				$msg = 'An unknown error occured. Please <a href="javascript:document.location.reload(true)">refresh the page</a> and try again.';
				break;
			default:
				$msg = 'Unknown Error';
				break;
		}
		
		return JText::_($msg);
	}

	/**
	 * Build a date from a time integer to the specified format
	 *
	 * @param   integer  $time    Time to format
	 * @param   string   $format  Format to transform to
	 *
	 * @return  string  Formatted time string.
	 *
	 */
	public static function getDate($time, $format = 'm/d/Y')
	{
		return date($format, $time);
	}

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

			if ($element && !empty($value))
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
	 * Parse email template and replace the text placeholders
	 * with information from the data array.
	 *
	 * @param   string  $msg   The message string to search for replacements in.
	 * @param   array   $data  Submitted form data array
	 *
	 * @return  string  Formatted message string.
	 *
	 */
	public static function formatEmail($msg, $data = array())
	{	
		preg_match_all('/{{([A-z_]*)}}/', $msg, $fields);

		foreach ($fields[1] as $fieldname)
		{
			if (!isset($data[$fieldname]))
			{
				$data[$fieldname] = '';
			}

			$msg = str_replace('{{'.$fieldname.'}}', $data[$fieldname], $msg);
		}

		return self::nl2p($msg);
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
	 * @param   string  $pos    The position to load.
	 * @param   strong  $style  The module style to apply.
	 *
	 * @return  HTML for the loaded module position.
	 *
	 */
	public static function loadPosition($pos = null, $style = null)
	{
		if ($pos === null)
		{
			return false;
		}

		$output = array();

		foreach (JModuleHelper::getModules($pos) as $mod)
		{
			$output[] = JModuleHelper::renderModule($mod, array('style' => $style));
		}

		return implode($output);
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
		$option = $app->input->getWord('option');
		$view = $app->input->getWord('view');
		$layout = $app->input->getWord('layout', 'default');

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

		array_push($bodyClasses, str_replace('com_', '', "{$option}-{$view}-{$layout}"));

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

	/**
	 * Get the real IP address of the user.
	 *
	 * @return  string  IPv4 Address
	 */
	public static function getRealIp()
	{
		foreach (self::$ipKeys as $key)
		{
			if (array_key_exists($key, $_SERVER))
			{
				$ips = explode(',', $_SERVER[$key]);

				foreach ($ips as $ip)
				{
					$ip = trim($ip);

					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
					{
						return $ip;
					}
				}
			}
		}

		return null;
	}

	/**
	 * An array of items to unset when updating the session.
	 *
	 * @return  array  The items to unset.
	 */
	public static function unsetInSession()
	{
		return array('antispam', 'check');
	}

	/**
	 * Update the session with this data.
	 *
	 * @param   array  $data  Data to use to update the session.
	 *
	 * @return  void
	 */
	public static function updateSessionData(array $data)
	{
		$session = JFactory::getSession();
		$current = $session->get('formdata', array(), 'userdata');

		foreach (self::unsetInSession() as $key)
		{
			// remove from submitted data
			unset($data[$key]);
			// remove from current session, just in case
			unset($current[$key]);
		}
		
		$session->set('formdata', array_merge($current, $data), 'userdata');
	}

	/**
	 * Get the current session data, remove sensitive data, and return.
	 *
	 * @return  array  An array of session data for use in forms.
	 */
	public static function getSessionData()
	{
		$current = JFactory::getSession()->get('formdata', array(), 'userdata');

		foreach (self::unsetInSession() as $key)
		{
			unset($current[$key]);
		}

		return $current;
	}
}
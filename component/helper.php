<?php defined('EE_PATH') or die;

abstract class EEComponentHelper extends JComponentHelper
{
	/**
	 * Method to fetch a single component param.
	 *
	 * @param   string  $option   The component option
	 * @param   string  $param    The param to fetch
	 * @param   mixed   $default  The default value to return if param is not set.
	 *
	 * @return  mixed   Value of specified param if found, otherwise false.
	 *
	 */
	public static function getParam($option, $param, $default = null)
	{
		$component = parent::getComponent($option, true);

		if ($component->enabled !== false)
		{
			return $component->params->get($param, $default);
		}

		return false;
	}
}
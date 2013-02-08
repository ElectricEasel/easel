<?php defined('EE_PATH') or die;

class EEViewAdminList extends EEViewBase
{
	protected $form;
	protected $items;
	protected $pagination;
	protected $option;
	protected $helperName;
	protected $componentName;
	protected $singleItemView;
	protected $useUniversalViews = false;

	public function __construct($config = array())
	{
		$this->getOption();
		$this->getHelperName();
		$this->getComponentName();

		parent::__construct($config);
	}

	/**
	 * Programatically determine the current option name
	 *
	 * @return  string  The determined option name
	 *
	 * @throws  Exception
	 */
	protected function getOption()
	{
		if (empty($this->option))
		{
			$classname = get_class($this);
			$viewpos = strpos($classname, 'View');

			if ($viewpos === false)
			{
				throw new Exception('Unable to determine active component.', 500);
			}

			$this->option = strtolower(substr($classname, 0, $viewpos));
		}

		return $this->option;
	}

	/**
	 * Programatically determine the current component helper name
	 *
	 * @return  string  The determined helper name
	 *
	 * @throws  Exception
	 */
	protected function getHelperName()
	{
		if (empty($this->helperName))
		{
			$this->helperName = ucfirst($this->getOption() . 'Helper');
		}

		return $this->helperName;
	}

	/**
	 * Programatically determine the current component name
	 *
	 * @return  string  The determined component name
	 *
	 * @throws  Exception
	 */
	protected function getComponentName()
	{
		if (empty($this->componentName))
		{
			$this->componentName = 'com_' . $this->getOption();
		}

		return $this->componentName;
	}

	/**
	 * Add the universal fallback view for simple edit forms.
	 */
	public function display($tpl = null)
	{
		$this->form   = $this->get('Form');
		$this->items  = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		if ($this->useUniversalViews && is_dir(JPATH_COMPONENT_ADMINISTRATOR . '/views/universal'))
		{
			$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/views/universal');
		}
		elseif ($this->useUniversalViews)
		{
			$this->addTemplatePath(EE_PATH . '/layouts/component/views');
		}

		$this->addToolbar();

		call_user_func_array(array($this->helperName, 'addSubmenu'), array(JFactory::getApplication()->input->getCmd('view', '')));

		parent::display($tpl);
	}

	protected function prepareDocument()
	{
		JHtml::_('behavior.tooltip');
		JHTML::_('script','system/multiselect.js',false,true);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = EEHelper::getActions($this->componentName);

		JToolBarHelper::title(JText::_($this->componentName . '_TITLE_' . $this->_name), 'optioncategories.png');

		//Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/' . $this->singleItemView;

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew($this->singleItemView . '.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList($this->singleItemView . '.edit', 'JTOOLBAR_EDIT');
			}

		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom($this->_name . '.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom($this->_name . '.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', $this->_name . '.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::archiveList($this->_name . '.archive', 'JTOOLBAR_ARCHIVE');
			}

			if (isset($this->items[0]->checked_out))
			{
				JToolBarHelper::custom($this->_name . '.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		//Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', $this->_name . '.delete', 'JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			}
			elseif ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::trash($this->_name . '.trash', 'JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences($this->componentName);
		}
	}
}

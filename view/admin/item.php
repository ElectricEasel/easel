<?php defined('EE_PATH') or die;

class EEViewAdminItem extends EEViewForm
{
	protected $form;
	protected $item;
	protected $items;
	protected $componentName;
	protected $useUniversalViews = false;

	public function __construct($config = array())
	{
		$this->getComponentName();

		parent::__construct($config);
	}

	/**
	 * Programatically determine the current component name
	 *
	 * @return  string  The determined option name
	 *
	 * @throws  Exception
	 */
	protected function getComponentName()
	{
		if (empty($this->componentName))
		{
			$classname = get_class($this);
			$viewpos = strpos($classname, 'View');

			if ($viewpos === false)
			{
				throw new Exception('Unable to determine active component.', 500);
			}

			$this->componentName = 'com_' . strtolower(substr($classname, 0, $viewpos));
		}

		return $this->componentName;
	}

	/**
	 * Add the universal fallback view for simple edit forms.
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->items = $this->get('Items');

		if ($this->useUniversalViews && is_dir(JPATH_COMPONENT_ADMINISTRATOR . '/views/universal'))
		{
			$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/views/universal');
		}
		elseif($this->useUniversalViews)
		{
			$this->addTemplatePath(EE_PATH . '/layouts/component/views');
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user  = JFactory::getUser();
		$isNew  = ($this->item->id == 0);

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}
		$canDo  = EEHelper::getActions($this->componentName);

		JToolBarHelper::title(JText::_($this->componentName . '_TITLE_' . $this->_name), 'option.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{

			JToolBarHelper::apply($this->_name . '.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save($this->_name . '.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom($this->_name . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolBarHelper::custom($this->_name . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		if (empty($this->item->id))
		{
			JToolBarHelper::cancel($this->_name . '.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel($this->_name . '.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}

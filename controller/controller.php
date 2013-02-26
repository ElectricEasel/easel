<?php defined('EE_PATH') or die;

class EEController implements JController
{
	/**
	 * The base path of the controller
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _basePath.
	 */
	protected $basePath;

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $default_view;

	/**
	 * The mapped task that was performed.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _doTask.
	 */
	protected $doTask;

	/**
	 * Redirect message.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _message.
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _messageType.
	 */
	protected $messageType;

	/**
	 * Array of class methods
	 *
	 * @var    array
	 * @since  12.2
	 * @note   Replaces _methods.
	 */
	protected $methods;

	/**
	 * The name of the controller
	 *
	 * @var    array
	 * @since  12.2
	 * @note   Replaces _name.
	 */
	protected $name;

	/**
	 * The prefix of the models
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $model_prefix;

	/**
	 * The set of search directories for resources (views).
	 *
	 * @var    array
	 * @since  12.2
	 * @note   Replaces _path.
	 */
	protected $paths;

	/**
	 * URL for redirection.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _redirect.
	 */
	protected $redirect;

	/**
	 * Current or most recently performed task.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _task.
	 */
	protected $task;

	/**
	 * Array of class methods to call for a given task.
	 *
	 * @var    array
	 * @since  12.2
	 * @note   Replaces _taskMap.
	 */
	protected $taskMap;

	/**
	 * Hold a JInput object for easier access to the input variables.
	 *
	 * @var    JInput
	 * @since  12.2
	 */
	protected $input;

	/**
	 * Hold a JApplication object for easier access to the app variables.
	 *
	 * @var    JApplication
	 * @since  12.2
	 */
	protected $app;

	/**
	 * Instance container.
	 *
	 * @var    JControllerLegacy
	 * @since  12.2
	 */
	protected static $instance;

	/**
	 * Run the component. This is syntactic sugar for the
	 * execute and redirect methods.
	 *
	 * @return  void
	 *
	 */
	public function run($prefix = null)
	{
		if (!is_object(self::$instance))
		{
			self::getInstance($prefix);
		}

		self::$instance->execute(JFactory::getApplication()->input->get('task'));
		self::$instance->redirect();
	}

	/**
	 * Method to get a singleton controller instance.
	 *
	 * @param   string  $prefix  The prefix for the controller.
	 * @param   array   $config  An array of optional constructor options.
	 *
	 * @return  JControllerLegacy
	 *
	 * @since   12.2
	 * @throws  Exception if the controller cannot be loaded.
	 */
	public static function getInstance($prefix, $config = array())
	{
		if (!is_object(self::$instance))
		{
			$app     = JFactory::getApplication();
			$format  = $app->input->getWord('format');
			$command = $app->input->get('task', 'display');
			$type    = null;

			// Check for a controller.task command.
			if (strpos($command, '.') !== false)
			{
				list ($type, $task) = explode('.', $command);

				// Reset the task without the controller context.
				$app->input->set('task', $task);
			}

			// Get the controller class name.
			$class = ucfirst($prefix) . 'Controller' . ucfirst($type);

			// Check for a format specific class, otherwise, fallback to base class.
			if (!empty($format))
			{
				$formatClass = $class . ucfirst(strtolower($format));

				if (class_exists($formatClass))
				{
					$class = $formatClass;
				}
			}
			else
			{
				// We only deal with autoloadable classes
				if (!class_exists($class))
				{
					throw new InvalidArgumentException(JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER', $type, $format));
				}
			}

			self::$instance = new $class($config);
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		$this->methods = array();
		$this->message = null;
		$this->messageType = 'message';
		$this->paths = array();
		$this->redirect = null;
		$this->taskMap = array();

		if (defined('JDEBUG') && JDEBUG)
		{
			JLog::addLogger(array('text_file' => 'jcontroller.log.php'), JLog::ALL, array('controller'));
		}

		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;

		// Determine the methods to exclude from the base class.
		$xMethods = get_class_methods('JControllerLegacy');

		// Get the public methods in this class using reflection.
		$r = new ReflectionClass($this);
		$rMethods = $r->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($rMethods as $rMethod)
		{
			$mName = $rMethod->getName();

			// Add default display method if not explicitly declared.
			if (!in_array($mName, $xMethods) || $mName == 'display')
			{
				$this->methods[] = strtolower($mName);

				// Auto register the methods as tasks.
				$this->taskMap[strtolower($mName)] = $mName;
			}
		}

		// Set the view name
		if (empty($this->name))
		{
			if (array_key_exists('name', $config))
			{
				$this->name = $config['name'];
			}
			else
			{
				$this->name = $this->getName();
			}
		}

		// Set a base path for use by the controller
		if (array_key_exists('base_path', $config))
		{
			$this->basePath = $config['base_path'];
		}
		else
		{
			$this->basePath = JPATH_COMPONENT;
		}

		// If the default task is set, register it as such
		if (array_key_exists('default_task', $config))
		{
			$this->registerDefaultTask($config['default_task']);
		}
		else
		{
			$this->registerDefaultTask('display');
		}

		// Set the models prefix
		if (empty($this->model_prefix))
		{
			if (array_key_exists('model_prefix', $config))
			{
				// User-defined prefix
				$this->model_prefix = $config['model_prefix'];
			}
			else
			{
				$this->model_prefix = $this->name . 'Model';
			}
		}

		// Set the default view.
		if (array_key_exists('default_view', $config))
		{
			$this->default_view = $config['default_view'];
		}
		elseif (empty($this->default_view))
		{
			$this->default_view = $this->getName();
		}

	}

	/**
	 * Adds to the search path for templates and resources.
	 *
	 * @param   string  $type  The path type (e.g. 'model', 'view').
	 * @param   mixed   $path  The directory string  or stream array to search.
	 *
	 * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
	 *
	 * @since   12.2
	 * @note    Replaces _addPath.
	 */
	protected function addPath($type, $path)
	{
		// Just force path to array
		settype($path, 'array');

		if (!isset($this->paths[$type]))
		{
			$this->paths[$type] = array();
		}

		// Loop through the path directories
		foreach ($path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = rtrim(JPath::check($dir, '/'), '/') . '/';

			// Add to the top of the search dirs
			array_unshift($this->paths[$type], $dir);
		}

		return $this;
	}

	/**
	 * Authorisation check
	 *
	 * @param   string  $task  The ACO Section Value to check access on.
	 *
	 * @return  boolean  True if authorised
	 *
	 * @since   12.2
	 * @deprecated  13.3  Use JAccess instead.
	 */
	public function authorise($task)
	{
		JLog::add(__METHOD__ . ' is deprecated. Use JAccess instead.', JLog::WARNING, 'deprecated');

		return true;
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  boolean  True if the ID is in the edit list.
	 *
	 * @since   12.2
	 */
	protected function checkEditId($context, $id)
	{
		if ($id)
		{
			$values = (array) $this->app->getUserState($context . '.id');

			$result = in_array((int) $id, $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				JLog::add(
					sprintf(
						'Checking edit ID %s.%s: %d %s',
						$context,
						$id,
						(int) $result,
						str_replace("\n", ' ', print_r($values, 1))
					),
					JLog::INFO,
					'controller'
				);
			}

			return $result;
		}
		else
		{
			// No id for a new item.
			return true;
		}
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  Optional model prefix.
	 * @param   array   $config  Configuration array for the model. Optional.
	 *
	 * @return  mixed   Model object on success; otherwise null failure.
	 *
	 * @since   12.2
	 * @note    Replaces _createModel.
	 */
	protected function createModel($name, $prefix = '', $config = array())
	{
		// Clean the model name
		$modelName = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		$result = JModelLegacy::getInstance($modelName, $classPrefix, $config);

		return $result;
	}

	/**
	 * Method to load and return a view object. This method first looks in the
	 * current template directory for a match and, failing that, uses a default
	 * set path to load the view class file.
	 *
	 * Note the "name, prefix, type" order of parameters, which differs from the
	 * "name, type, prefix" order used in related public methods.
	 *
	 * @param   string  $name    The name of the view.
	 * @param   string  $prefix  Optional prefix for the view class name.
	 * @param   string  $type    The type of view.
	 * @param   array   $config  Configuration array for the view. Optional.
	 *
	 * @return  mixed  View object on success; null or error result on failure.
	 *
	 * @since   12.2
	 * @note    Replaces _createView.
	 * @throws  Exception
	 */
	protected function createView($name, $prefix = '', $type = '', $config = array())
	{
		// Clean the view name
		$viewName = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);
		$viewType = preg_replace('/[^A-Z0-9_]/i', '', $type);

		// Build the view class name
		$viewClass = $classPrefix . $viewName;

		if (!class_exists($viewClass))
		{
			jimport('joomla.filesystem.path');
			$path = JPath::find($this->paths['view'], $this->createFileName('view', array('name' => $viewName, 'type' => $viewType)));

			if ($path)
			{
				require_once $path;

				if (!class_exists($viewClass))
				{
					throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_CLASS_NOT_FOUND', $viewClass, $path), 500);
				}
			}
			else
			{
				return null;
			}
		}

		return new $viewClass($config);
	}

	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
	 *
	 * @since   12.2
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$document   = JFactory::getDocument();
		$viewType   = $document->getType();
		$viewName   = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default');

		$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

		// Get/Create the model
		if ($model = $this->getModel($viewName))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->document = $document;

		$conf = JFactory::getConfig();

		// Display the view
		if ($cachable && $viewType != 'feed' && $conf->get('caching') >= 1)
		{
			$option = $this->input->get('option');
			$cache = JFactory::getCache($option, 'view');

			if (is_array($urlparams))
			{
				if (!empty($this->app->registeredurlparams))
				{
					$registeredurlparams = $this->app->registeredurlparams;
				}
				else
				{
					$registeredurlparams = new stdClass;
				}

				foreach ($urlparams as $key => $value)
				{
					// Add your safe url parameters with variable type as value {@see JFilterInput::clean()}.
					$registeredurlparams->$key = $value;
				}

				$this->app->registeredurlparams = $registeredurlparams;
			}

			$cache->get($view, 'display');
		}
		else
		{
			$view->display();
		}

		return $this;
	}

	/**
	 * Execute a task by triggering a method in the derived class.
	 *
	 * @param   string  $task  The task to perform. If no matching task is found, the '__default' task is executed, if defined.
	 *
	 * @return  mixed   The value returned by the called method, false in error case.
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function execute($task)
	{
		$this->task = $task;

		$task = strtolower($task);

		if (isset($this->taskMap[$task]))
		{
			$doTask = $this->taskMap[$task];
		}
		elseif (isset($this->taskMap['__default']))
		{
			$doTask = $this->taskMap['__default'];
		}
		else
		{
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $task), 404);
		}

		// Record the actual task being fired
		$this->doTask = $doTask;

		return $this->$doTask();
	}

	/**
	 * Get the application object.
	 *
	 * @return  JApplication  The application object.
	 *
	 * @since   12.1
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Get the input object.
	 *
	 * @return  JInput  The input object.
	 *
	 * @since   12.1
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   12.2
	 */
	public function getModel($name = '', $prefix = '', $config = array())
	{
		if (empty($name))
		{
			$name = $this->getName();
		}

		if (empty($prefix))
		{
			$prefix = $this->model_prefix;
		}

		if ($model = $this->createModel($name, $prefix, $config))
		{
			// Task is a reserved state
			$model->setState('task', $this->task);

			// Let's get the application object and set menu information if it's available
			$menu = $this->app->getMenu();

			if (is_object($menu))
			{
				if ($item = $menu->getActive())
				{
					$params = $menu->getParams($item->id);

					// Set default state data
					$model->setState('parameters.menu', $params);
				}
			}
		}
		return $model;
	}

	/**
	 * Method to get the controller name
	 *
	 * The dispatcher name is set by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the dispatcher
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (!preg_match('/(.*)Controller/i', get_class($this), $r))
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_CONTROLLER_GET_NAME'), 500);
			}
			$this->name = strtolower($r[1]);
		}

		return $this->name;
	}

	/**
	 * Get the last task that is being performed or was most recently performed.
	 *
	 * @return  string  The task that is being performed or was most recently performed.
	 *
	 * @since   12.2
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Gets the available tasks in the controller.
	 *
	 * @return  array  Array[i] of task names.
	 *
	 * @since   12.2
	 */
	public function getTasks()
	{
		return $this->methods;
	}

	/**
	 * Method to get a reference to the current view and load it if necessary.
	 *
	 * @param   string  $name    The view name. Optional, defaults to the controller name.
	 * @param   string  $type    The view type. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for view. Optional.
	 *
	 * @return  JViewLegacy  Reference to the view or an error.
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getView($name = '', $type = '', $prefix = '', $config = array())
	{
		static $views;

		if (!isset($views))
		{
			$views = array();
		}

		if (empty($name))
		{
			$name = $this->getName();
		}

		if (empty($prefix))
		{
			$prefix = $this->getName() . 'View';
		}

		if (empty($views[$name]))
		{
			if ($view = $this->createView($name, $prefix, $type, $config))
			{
				$views[$name] = & $view;
			}
			else
			{
				throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_NOT_FOUND', $name, $type, $prefix), 500);
			}
		}

		return $views[$name];
	}

	/**
	 * Method to add a record ID to the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function holdEditId($context, $id)
	{
		$values = (array) $this->app->getUserState($context . '.id');

		// Add the id to the list if non-zero.
		if (!empty($id))
		{
			array_push($values, (int) $id);
			$values = array_unique($values);
			$this->app->setUserState($context . '.id', $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				JLog::add(
					sprintf(
						'Holding edit ID %s.%s %s',
						$context,
						$id,
						str_replace("\n", ' ', print_r($values, 1))
					),
					JLog::INFO,
					'controller'
				);
			}
		}
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 *
	 * @since   12.2
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			$this->app->redirect($this->redirect, $this->message, $this->messageType);
		}

		return false;
	}

	/**
	 * Register the default task to perform if a mapping is not found.
	 *
	 * @param   string  $method  The name of the method in the derived class to perform if a named task is not found.
	 *
	 * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
	 *
	 * @since   12.2
	 */
	public function registerDefaultTask($method)
	{
		$this->registerTask('__default', $method);

		return $this;
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param   string  $task    The task.
	 * @param   string  $method  The name of the method in the derived class to perform for this task.
	 *
	 * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
	 *
	 * @since   12.2
	 */
	public function registerTask($task, $method)
	{
		if (in_array(strtolower($method), $this->methods))
		{
			$this->taskMap[strtolower($task)] = $method;
		}

		return $this;
	}

	/**
	 * Unregister (unmap) a task in the class.
	 *
	 * @param   string  $task  The task.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   12.2
	 */
	public function unregisterTask($task)
	{
		unset($this->taskMap[strtolower($task)]);

		return $this;
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function releaseEditId($context, $id)
	{
		$values = (array) $this->app->getUserState($context . '.id');

		// Do a strict search of the edit list values.
		$index = array_search((int) $id, $values, true);

		if (is_int($index))
		{
			unset($values[$index]);
			$this->app->setUserState($context . '.id', $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				JLog::add(
					sprintf(
						'Releasing edit ID %s.%s %s',
						$context,
						$id,
						str_replace("\n", ' ', print_r($values, 1))
					),
					JLog::INFO,
					'controller'
				);
			}
		}
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @param   string  $text  Message to display on redirect.
	 * @param   string  $type  Message type. Optional, defaults to 'message'.
	 *
	 * @return  string  Previous message
	 *
	 * @since   12.2
	 */
	public function setMessage($text, $type = 'message')
	{
		$previous = $this->message;
		$this->message = $text;
		$this->messageType = $type;

		return $previous;
	}

	/**
	 * Sets an entire array of search paths for resources.
	 *
	 * @param   string  $type  The type of path to set, typically 'view' or 'model'.
	 * @param   string  $path  The new set of search paths. If null or false, resets to the current directory only.
	 *
	 * @return  void
	 *
	 * @note    Replaces _setPath.
	 * @since   12.2
	 */
	protected function setPath($type, $path)
	{
		// Clear out the prior search dirs
		$this->paths[$type] = array();

		// Actually add the user-specified directories
		$this->addPath($type, $path);
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string  $url   URL to redirect to.
	 * @param   string  $msg   Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param   string  $type  Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   12.2
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		$this->redirect = $url;

		if ($msg !== null)
		{
			// Controller may have set this directly
			$this->message = $msg;
		}

		// Ensure the type is not overwritten by a previous call to setMessage.
		if (empty($type))
		{
			if (empty($this->messageType))
			{
				$this->messageType = 'message';
			}
		}
		// If the type is explicitly set, set it.
		else
		{
			$this->messageType = $type;
		}

		return $this;
	}
}


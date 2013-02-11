<?php defined('EE_PATH') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

JFactory::getDocument()
	->addStyleSheet("
	// <![CDATA
	Joomla.submitbutton = function(task)
	{
		var taskParts = task.split('.');

		if (taskParts[1] == 'cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else
		{
			alert(" . $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "');
		}
	}
	// ]]>
	");
?>
<form action="index.php" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<div class="width-60 fltlft">
		<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
		<fieldset class="adminform fieldset_<?php echo $fieldset->name; ?>">
			<legend><?php echo JText::_($fieldset->label); ?></legend>
			<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
				<li><?php echo $field->label, $field->input; ?></li>
			<?php endforeach; ?>
            </ul>
		</fieldset>
		<?php endforeach; ?>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->form->getValue('id'); ?>" />
	<input type="hidden" name="option" value="<?php echo $this->componentName; ?>" />
	<input type="hidden" name="layout" value="edit" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>

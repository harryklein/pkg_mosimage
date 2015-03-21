<?php
/**
 * @version 2.0 $Id: form.php,v 1.5 2015/02/05 22:11:06 harry Exp $
 * @package Joomla.Administrator
 * @subpackage com_mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$css = 'img.preview {
	max-width : 150px;
	max-height: 150px;
}';

$document = JFactory::getDocument();
$document->addStyleDeclaration($css);
?>

<script type="text/javascript">	

	var JOOMLA_ROOT = '<?php echo JURI::root();?>';
	var folderimages = new Array;
<?php
$i = 0;
foreach ($this->allAvailableImages as $k => $items) {
    foreach ($items as $v) {
        echo "folderimages[" . $i ++ . "] = new Array( '$k','" . addslashes($v->value) . "','" . addslashes($v->text) . "' );\t";
    }
}
?>

Joomla.submitbutton = function(task) {
	if (task == 'options.cancel') {

		Mosimage.revertChanges();
		return;
	}
	// assemble the images back into one field
	var temp = new Array;
	var srcList = document.getElementById('jform_imageslist');
	for (var i=0, n=srcList.options.length; i < n; i++) {
		temp[i] = srcList.options[i].value;
	}

	json = '[' + temp.join( ',' ) + ']';
	element = document.getElementById('jform_images');
	element.value = json;
	
	Joomla.submitform(task);
}

</script>
<div class="modal-header">
	<div class="row-fluid">
	
<?php
JHtml::_('bootstrap.tooltip');

$app = JFactory::getApplication();
if ($app->isAdmin()) {
    ?><div class="span8"><?php echo JToolbar::getInstance('toolbar')->render();?></div><?php
} else {
    ?>	


<div class="pull-right">
			<button class="btn" type="button"
				onclick="Joomla.submitbutton('options.apply');"><?php echo JText::_('JTOOLBAR_APPLY') ?></button>
			<button class="btn btn-primary" type="button"
				onclick="Joomla.submitbutton('options.save');"><?php echo JText::_('JTOOLBAR_SAVE') ?></button>
			<button class="btn" type="button"
				onclick="Joomla.submitbutton('options.cancel');"><?php echo JText::_('JCANCEL') ?></button>
		</div>

		<div class="clearfix"></div>

<?php } ?>

	</div>
</div>
<div class="modal-form">
	<form
		action="<?php echo JRoute::_('index.php?option=com_mosimage&layout=formt&tmpl=component&content_id=' . (int) $this->item->content_id);?>"
		method="post" name="adminForm" id="adminForm">

		<!-- Titel und Botton Save/Cancel	  -->
		<div class="row">
			<div class="span8 control-group">
				<fieldset>
					<legend><?php echo JText::_('COM_MOSIMAGE_MOSIMAGE_CONTROL');?></legend>
				</fieldset>
			</div>


			<div class="span3 control-group">
				<fieldset>
					<legend><?php echo JText::_('COM_MOSIMAGE_OPTION_LABEL'); ?></legend>
				</fieldset>
			</div>
		</div>


		<div class="row">
			<div class="span8">
				<!-- Bildauswahl und Anzeige -->
				<div class="row">
					<div class="span3">
						<?php echo $this->form->getControlGroup('imagefiles')?>
					</div>

					<!-- Add/Remove Image -->
					<div class="span1 control-group">
						<div class="controls">
							<?php echo $this->form->getInput('add'); ?><br />
							<?php echo $this->form->getInput('remove'); ?>
						</div>
					</div>
					<div class="span4">
						<?php echo $this->form->getControlGroup('imageslist');?>
					</div>
				</div>

				<!-- Subfolder | leer | Down/Up -->
				<div class="row">
					<div class="span3">
						<?php echo $this->form->getControlGroup('folder')?>
					</div>

					<div class="span1">&nbsp;</div>

					<div class="span4 control-group">
						<div class="control-label">
							<label for="botton_move">&nbsp;</label>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('pos1'); ?>
							<?php echo $this->form->getInput('up'); ?>
							<?php echo $this->form->getInput('down'); ?>
							<?php echo $this->form->getInput('end'); ?>
						</div>
					</div>
				</div>

				<div class="row" style="height: 190px;">
					<div class="span3">
						<?php echo $this->form->getControlGroup('view_imagefiles'); ?>
					</div>
					<div class="span1 control-group">
						<div class="controls">&nbsp;</div>
					</div>
					<div class="span4">
						<?php echo $this->form->getControlGroup('view_imagelist'); ?>
					</div>
				</div>
			</div>

			<div class="span3">
				<fieldset>
					<?php echo $this->form->getControlGroup('_source')?>
					<?php echo $this->form->getControlGroup('accesslevel')?>
					<?php echo $this->form->getControlGroup('_align')?>
					<?php echo $this->form->getControlGroup('_alt')?>
					<?php echo $this->form->getControlGroup('_border')?>
					<?php echo $this->form->getControlGroup('_caption')?>
					<?php echo $this->form->getControlGroup('_caption_position')?>
					<?php echo $this->form->getLabel('buttonspacer');?>
					<div class="pull-left">
						<?php echo $this->form->getInput('applay'); ?>	
						<?php echo $this->form->getInput('reset'); ?>
					</div>
					<!-- hidden fields -->
					<?php echo $this->form->getInput('content_id');?>
					<?php echo $this->form->getInput('images');?>
				</fieldset>
			</div>
		</div>
		<input type="hidden" name="_caption_align" value="right" /> <input
			type="hidden" name="_width" value="" /> <input type="hidden"
			name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?> 		
</form>
</div>
<script type="text/javascript">
	Mosimage.changeDynaList2( 'jform_imagefiles', folderimages, 'jform_folder' , 0, 0);

	var messages = new Array("<?php echo JText::_('COM_MOSIMAGE_APLAY_CHANGED_VALUES');?>");
	Mosimage.initShowImageProps( JOOMLA_ROOT + 'images/', messages );
	Mosimage.previewImage('jform_imagefiles', 'jform_view_imagefiles', JOOMLA_ROOT + 'images/');
</script>
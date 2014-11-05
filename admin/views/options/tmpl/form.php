<?php

defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: form.php,v 1.4 2014-10-30 21:55:27 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010-2012 Harry Klein - www.joomla-hklein.de
 * @license GNU/GPL, see LICENSE.php
 * 
 * H2N Mosimage Component is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * H2N Mosimage Component is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

?>
<?php
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
	foreach ($this->allAvailableImages as $k=>$items) {
		foreach ($items as $v) {
			echo "folderimages[".$i++."] = new Array( '$k','".addslashes( $v->value )."','".addslashes( $v->text )."' );\t";
		}
	}
?>

Joomla.submitbutton = function(task) {
	if (task == 'options.cancel') {
		window.parent.SqueezeBox.close();
		return;
	}
	// assemble the images back into one field
	var temp = new Array;
	var srcList = document.getElementById('jform_imageslist');
	for (var i=0, n=srcList.options.length; i < n; i++) {
		temp[i] = srcList.options[i].value;
	}
	document.getElementById('jform_images').value = temp.join( '\n' );
	Joomla.submitform(task);
}

</script>
<div class="modal-header">
<div class="row-fluid">
	<div class="span8">
<?php 
	JHtml::_('bootstrap.tooltip');
	echo JToolbar::getInstance('toolbar')->render();
?>	
	</div>
	</div>
	</div>
	<div class="modal-form">
	<form action="<?php echo JRoute::_('index.php?option=com_mosimage&layout=formt&tmpl=component&content_id=' . (int) $this->item->content_id);?>" method="post" name="adminForm" id="adminForm">
	
		<!-- Titel und Botton Save/Cancel	  -->
		<div class="row">
			<div class="span8 control-group">
				<fieldset>
					<legend><?php echo JText::_('COM_MOSIMAGE_MOSIMAGE_CONTROL');?></legend>
				</fieldset>
			</div>
			
			
			<div class="span3 control-group">
				<fieldset>
					<legend><?php echo JText::_('COM_MOSIMAGE_EDIT_THE_IMAGE_SELECTED'); ?></legend>
				</fieldset>
			</div>
		</div>
		
		
		<div class="row">
			<div class="span8">
				<!-- Bildauswahl und Anzeige -->
				<div class="row">
					<div class="span3">
						<?php echo $this->form->getControlGroup('imagefiles') ?>
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
					
					<div class="span1">
						&nbsp;
					</div>
					
					<div class="span4 control-group">
						<div class="control-label"><label for="botton_move">&nbsp;</label></div>
						<div class="controls">
							<?php echo $this->form->getInput('pos1'); ?>
							<?php echo $this->form->getInput('up'); ?>
							<?php echo $this->form->getInput('down'); ?>
							<?php echo $this->form->getInput('end'); ?>
						</div>
					</div>
				</div>
				
				<div class="row" style="height:190px;">
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
					<?php echo $this->form->getControlGroup('_align')?>
					<?php echo $this->form->getControlGroup('_alt')?>
					<?php echo $this->form->getControlGroup('_border')?>
					<?php echo $this->form->getControlGroup('_caption')?>
					<?php echo $this->form->getControlGroup('_caption_position')?>
					<?php echo $this->form->getControlGroup('applay'); ?>
					<!-- hidden fields -->
					<?php echo $this->form->getInput('content_id');?>
					<?php echo $this->form->getInput('images');?>
				</fieldset>
			</div>
		</div>
	<input type="hidden" name="_caption_align" value="right" />
	<input type="hidden" name="_width" value=""/>
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?> 		
</form>
</div>
<script type="text/javascript">
	Mosimage.changeDynaList2( 'jform_imagefiles', folderimages, 'jform_folder' , 0, 0);

	var messages = new Array("<?php echo JText::_('COM_MOSIMAGE_APLAY_CHANGED_VALUES');?>");
	Mosimage.initShowImageProps( JOOMLA_ROOT + 'images/', messages );
	Mosimage.previewImage('jform_imagefiles', 'jform_view_imagefiles', JOOMLA_ROOT + 'images/');


	
</script>
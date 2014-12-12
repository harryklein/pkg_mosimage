<?php
/**
 * @version 2.0 $Id: default.php,v 1.3 2014-10-19 19:27:29 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

?>

<script type="text/javascript">
	submitbutton = function(pressbutton) {
		var form = document.getElementById('adminForm');
		form.submit();
	}
</script>


<form
	action="<?php echo JRoute::_('index.php?option=com_mosimage&view=info');?>"
	method="post" name="adminForm" id="adminForm">

	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<fieldset>
				<label><?php echo JText::_('COM_MOSIMAGE_AMOUNT_IMAGE_CACHE_FILE_DESC') . ": " . $this->amountCacheFile;?>&nbsp;</label>
				<input class="button" type="button"
					value="<?php echo JText::_('COM_MOSIMAGE_CLEAR_IMAGE_CACHE'); ?>"
					onclick="submitbutton()" />
			</fieldset>
			<fieldset>
				<legend><?php echo JText::_('COM_MOSIMAGE_CACHE_FILE_LIST'); ?></legend>
			</fieldset>


			<div class="clearfix"></div>
		<?php
if ($this->moreAsMaxFilesExist) {
    echo JText::sprintf("COM_MOSIMAGE_FIRST_N_FILES", $this->moreAsMaxFilesExist);
}
?>
       <ul>
        <?php foreach ($this->cacheFileList as $i) {?>
            <li><a
					href="<?php echo JURI::root();?>/cache/mosimage-cache/<?php echo $i;?>"><?php echo $i;?></a></li>
        <?php } ?>
        </ul>
			<input type="hidden" name="task" value="info.clearcache" />
		</div>
	<?php echo JHtml::_('form.token'); ?>	




</form>
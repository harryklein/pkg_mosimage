<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: default.php,v 1.3 2014-10-19 19:27:29 harry Exp $
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

<script type="text/javascript">
	submitbutton = function(pressbutton) {
		var form = document.getElementById('adminForm');
		form.submit();
	}
</script>


<form
	action="<?php echo JRoute::_('index.php?option=com_mosimage&view=info');?>"
	method="post" name="adminForm" id="adminForm">

	<div id="j-main-container" class="span10">
	
		<fieldset>
            <label><?php echo JText::_('COM_MOSIMAGE_AMOUNT_IMAGE_CACHE_FILE_DESC') . ": " . $this->amountCacheFile;?>&nbsp;</label>
			<input class="button" type="button"
				value="<?php echo JText::_('COM_MOSIMAGE_CLEAR_IMAGE_CACHE'); ?>"
				onclick="submitbutton()" />
		</fieldset>
		<fieldset>
			<legend>
			<?php echo JText::_('COM_MOSIMAGE_CACHE_FILE_LIST'); ?>
			</legend>
		</fieldset>	
		
	
	<div class="clearfix"> </div>
		<?php 
		if ($this->moreAsMaxFilesExist){
 			echo JText::sprintf("COM_MOSIMAGE_FIRST_N_FILES",$this->moreAsMaxFilesExist);
		}		
		?>
		<ul>
			<?php foreach( $this->cacheFileList as $i){
				?><li><a href="<?php echo JURI::root();?>/cache/mosimage-cache/<?php echo $i;?>"><?php echo $i;?></a></li><?php 
			}?>
		</ul>
		<input type="hidden" name="task" value="clearcache" />
	</div>
	<?php echo JHtml::_('form.token'); ?>	
</form>
	


		



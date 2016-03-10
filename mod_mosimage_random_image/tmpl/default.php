<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mosimage_random_image
 *
 * @copyright (C) 2015-2016 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;



?>
<div class="random-image<?php echo $moduleclass_sfx ?>">
	<?php 
	echo $article->text; 
	
    if ($params->get('view_link')): 
        $url = 'index.php?option=com_content&view=article&id=' . $article->id . '&Itemid=' . JRequest::getInt('Itemid', 0);?>
    	<p><a title="<?php echo $article->title;?>"  href="<?php echo JRoute::_($url); ?>"><?php echo JText::_('MOD_MOSIMAGE_RANDOM_IMAGE_READMORE')?></a>
    <?php 
    endif;?>
</div>

<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \HKlein\Component\Mosimage\Administrator\View\Info\HtmlView $this */
?>
<form action="<?php echo Route::_('index.php?option=com_mosimage&view=info'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<div class="card mb-3">
			<div class="card-body">
				<p class="card-text">
					<?php echo Text::_('COM_MOSIMAGE_AMOUNT_IMAGE_CACHE_FILE_DESC'); ?>: <strong><?php echo (int) $this->amountCacheFile; ?></strong>
				</p>
			</div>
		</div>

		<h2 class="h4"><?php echo Text::_('COM_MOSIMAGE_CACHE_FILE_LIST'); ?></h2>

		<?php if ($this->moreAsMaxFilesExist) : ?>
			<div class="alert alert-info"><?php echo Text::sprintf('COM_MOSIMAGE_FIRST_N_FILES', $this->moreAsMaxFilesExist); ?></div>
		<?php endif; ?>

		<?php if ($this->cacheFileList) : ?>
			<ul class="list-unstyled">
				<?php foreach ($this->cacheFileList as $file) : ?>
					<li>
						<a href="<?php echo Uri::root() . 'cache/mosimage-cache/' . rawurlencode($file); ?>" target="_blank" rel="noopener">
							<?php echo $this->escape($file); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

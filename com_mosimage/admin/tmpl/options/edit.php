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

/** @var \HKlein\Component\Mosimage\Administrator\View\Options\HtmlView $this */
$action = Route::_('index.php?option=com_mosimage&layout=edit&tmpl=component&content_id=' . (int) $this->item->content_id);
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate mosimage-edit p-3">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
		<h1 class="h3 m-0"><?php echo Text::_('COM_MOSIMAGE_MOSIMAGE_CONTROL'); ?></h1>
		<div class="d-flex gap-2">
			<button type="button" class="btn btn-success" data-mosimage-task="options.apply">
				<span class="icon-check" aria-hidden="true"></span> <?php echo Text::_('JTOOLBAR_APPLY'); ?>
			</button>
			<button type="button" class="btn btn-primary" data-mosimage-task="options.save">
				<span class="icon-save" aria-hidden="true"></span> <?php echo Text::_('JTOOLBAR_SAVE'); ?>
			</button>
			<button type="button" class="btn btn-danger" data-mosimage-task="options.cancel">
				<span class="icon-times" aria-hidden="true"></span> <?php echo Text::_('JTOOLBAR_CLOSE'); ?>
			</button>
		</div>
	</div>

	<div class="row g-4">
		<div class="col-lg-8">
			<div class="row g-3">
				<!-- Bildauswahl -->
				<div class="col-sm-5">
					<?php echo $this->form->renderField('folder'); ?>
					<?php echo $this->form->renderField('imagefiles'); ?>
				</div>

				<!-- Add/Remove Image -->
				<div class="col-sm-2 d-flex flex-sm-column justify-content-center align-items-center gap-2">
					<?php echo $this->form->getInput('add'); ?>
					<?php echo $this->form->getInput('remove'); ?>
				</div>

				<!-- Bilder im Beitrag -->
				<div class="col-sm-5">
					<?php echo $this->form->renderField('imageslist'); ?>
					<div class="d-flex gap-2 mosimage-move-buttons">
						<?php echo $this->form->getInput('pos1'); ?>
						<?php echo $this->form->getInput('up'); ?>
						<?php echo $this->form->getInput('down'); ?>
						<?php echo $this->form->getInput('end'); ?>
					</div>
				</div>
			</div>

			<div class="row g-3 mt-2">
				<div class="col-sm-5">
					<?php echo $this->form->renderField('view_imagefiles'); ?>
				</div>
				<div class="col-sm-2"></div>
				<div class="col-sm-5">
					<?php echo $this->form->renderField('view_imagelist'); ?>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<fieldset class="options-form">
				<legend><?php echo Text::_('COM_MOSIMAGE_OPTION_LABEL'); ?></legend>
				<?php echo $this->form->renderField('_source'); ?>
				<?php echo $this->form->renderField('accesslevel'); ?>
				<?php echo $this->form->renderField('_align'); ?>
				<?php echo $this->form->renderField('_alt'); ?>
				<?php echo $this->form->renderField('_border'); ?>
				<?php echo $this->form->renderField('_caption'); ?>
				<?php echo $this->form->renderField('_caption_position'); ?>
				<div class="d-flex gap-2">
					<?php echo $this->form->getInput('applay'); ?>
					<?php echo $this->form->getInput('reset'); ?>
				</div>
			</fieldset>
		</div>
	</div>

	<?php echo $this->form->getInput('content_id'); ?>
	<?php echo $this->form->getInput('images'); ?>
	<input type="hidden" name="_caption_align" id="mosimage_caption_align" value="right">
	<input type="hidden" name="_width" id="mosimage_width" value="">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

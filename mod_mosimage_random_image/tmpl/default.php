<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_mosimage_random_image
 * @copyright   (C) 2015-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \stdClass|null $article */
/** @var \Joomla\Registry\Registry $params */

if (!$article) {
    return;
}
?>
<div class="random-image<?php echo htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8'); ?>">
	<?php echo $article->text; ?>
	<?php if ($params->get('view_link')) : ?>
		<p>
			<a title="<?php echo htmlspecialchars($article->title, ENT_COMPAT, 'UTF-8'); ?>" href="<?php echo $article->link; ?>">
				<?php echo Text::_('MOD_MOSIMAGE_RANDOM_IMAGE_READMORE'); ?>
			</a>
		</p>
	<?php endif; ?>
</div>

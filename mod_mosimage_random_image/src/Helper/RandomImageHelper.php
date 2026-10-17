<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_mosimage_random_image
 * @copyright   (C) 2015-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Module\MosimageRandomImage\Site\Helper;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class RandomImageHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Liefert einen zufälligen Beitrag (id, title, link und text) einer Kategorie. Der Text enthält
     * ein zufälliges, über Mosimage eingebundenes Bild des Beitrags.
     */
    public function getRandomImage(Registry $params, CMSApplicationInterface $app): ?\stdClass
    {
        $catid = (int) $params->get('catid');

        if ($catid <= 0 || !PluginHelper::isEnabled('content', 'mosimage')) {
            return null;
        }

        $result = $this->getRandomArticle($catid, (bool) $params->get('includeSubCategories'), $app->getIdentity()->getAuthorisedViewLevels());

        if (!$result) {
            return null;
        }

        $article            = new \stdClass();
        $article->id        = (int) $result->id;
        $article->title     = $result->title;
        $article->text      = '{mosimage random}';
        $article->introtext = '{mosimage random}';
        $article->attribs   = '{}';
        $article->link      = Route::_(RouteHelper::getArticleRoute($result->id . ':' . $result->alias, $result->catid, $result->language));

        // Nur das Plugin Mosimage ersetzt den Platzhalter, die übrigen Content-Plugins werden nicht ausgeführt.
        $plugin = $app->bootPlugin('mosimage', 'content');
        $plugin->onContentBeforeDisplay(new BeforeDisplayEvent('onContentBeforeDisplay', [
            'context' => 'com_content.article',
            'subject' => $article,
            'params'  => new Registry(),
            'page'    => 0,
        ]));

        return $article;
    }

    /**
     * @param int[] $viewLevels
     */
    private function getRandomArticle(int $catid, bool $includeSubCategories, array $viewLevels): ?\stdClass
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['content.id', 'content.title', 'content.alias', 'content.catid', 'content.language']))
            ->from($db->quoteName('#__mosimage', 'mosimage'))
            ->join('INNER', $db->quoteName('#__content', 'content'), $db->quoteName('content.id') . ' = ' . $db->quoteName('mosimage.content_id'))
            ->join('INNER', $db->quoteName('#__categories', 'cat'), $db->quoteName('cat.id') . ' = ' . $db->quoteName('content.catid'))
            ->where($db->quoteName('cat.extension') . ' = ' . $db->quote('com_content'))
            ->where($db->quoteName('cat.published') . ' = 1')
            ->where($db->quoteName('content.state') . ' = 1')
            ->whereIn($db->quoteName('content.access'), $viewLevels)
            ->whereIn($db->quoteName('cat.access'), $viewLevels)
            ->where($db->quoteName('mosimage.images') . ' NOT IN (' . $db->quote('') . ', ' . $db->quote('[]') . ')')
            ->setLimit(1);

        $query->order($query->rand());

        if ($includeSubCategories) {
            $query->where('(' . $db->quoteName('cat.id') . ' = :catid OR ' . $db->quoteName('cat.parent_id') . ' = :parentid)')
                ->bind(':parentid', $catid, ParameterType::INTEGER);
        } else {
            $query->where($db->quoteName('cat.id') . ' = :catid');
        }

        $query->bind(':catid', $catid, ParameterType::INTEGER);

        try {
            return $db->setQuery($query)->loadObject() ?: null;
        } catch (\RuntimeException $e) {
            return null;
        }
    }
}

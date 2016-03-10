<?php
use Symfony\Component\Yaml\Exception\RuntimeException;
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

/**
 * Helper for mod_random_image
 *
 * @package Joomla.Site
 * @subpackage mod_random_image
 * @since 1.5
 */
class ModMosimageRandomImageHelper
{

    public static function getRandomImage ($catid, $includeSubCategories)
    {
        $article = new stdClass();
        $article->text = '{mosimage random}';
        $article->introtext = '{mosimage random}';
        $article->attribs = '{}';
        
        $db = JFactory::getDbo();
        
        $query = $db->getQuery(true)
            ->select('content_id')
            ->select('co.title')
            ->from('#__categories c');
        if ($includeSubCategories) {
            $query->join('inner', '#__content co ON (co.catid = c.id)');
        }
        $query->join('inner', '#__mosimage m ON (co.id = m.content_id)')
            ->where('c.extension = \'com_content\' and (c.id = '.$catid.' or c.parent_id = '.$catid.' )')
            ->order('rand()')
            ->setLimit(1);
        
        $db->setQuery($query);

        try
        {
            $result = $db->loadObject();
        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return;
        }
        
        $article->id = $result->content_id;
        $article->title = $result->title;
        return $article;
    }
}

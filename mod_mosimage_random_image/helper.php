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
 * @since 3.6
 */
class ModMosimageRandomImageHelper
{

	/** Liefert einen zufälligen Artikel (id und title) einer Kategorie.
	 * Der Artikel muss Bilder haben, die über mos_image eingebunden sind.
	 * 
	 * @param unknown $catid                Kategorie, aus der die Artikel ausgewählt wird
	 * @param unknown $includeSubCategories Legt fest, ob auch Artikel einer Unterkategories ausgewählt werden kann  
	 * @return void|stdClass
	 */  
    public static function getRandomImage ($catid, $includeSubCategories)
    {
        $article = new stdClass();
        $article->text = '{mosimage random}';
        $article->introtext = '{mosimage random}';
        $article->attribs = '{}';
        
        $db = JFactory::getDbo();
   
        $query = $db->getQuery(true)
            ->select('mosimage.content_id')
            ->select('content.title')
            ->from('#__categories cat');
        $whereCategorie = array();
        $whereCategorie[] = 'cat.id = '. $catid;
        if ($includeSubCategories) {
        	$whereCategorie[] = 'cat.parent_id = ' . $catid;
        } 
        $query->join('inner', '#__content content ON (content.catid = cat.id)');
        $query->join('inner', '#__mosimage mosimage ON (content.id = mosimage.content_id)')
            ->where('cat.extension = \'com_content\'')->extendWhere('AND', $whereCategorie, 'OR')
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

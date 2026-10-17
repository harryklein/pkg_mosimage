<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Administrator\Table;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tabelle #__mosimage: pro Beitrag (content_id) eine Liste der Bilder als JSON.
 */
class MosimageTable extends Table
{
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__mosimage', 'content_id', $db, $dispatcher);
        $this->_autoincrement = false;
    }

    public function check()
    {
        if ((int) $this->content_id <= 0) {
            $this->setError('Invalid content_id');

            return false;
        }

        if (!\is_string($this->images) || json_decode($this->images) === null) {
            $this->images = '[]';
        }

        return parent::check();
    }
}

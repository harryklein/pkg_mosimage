<?php
/**
 *
 * @version 2.0 $Id: options.php,v 1.2 2014-10-28 23:08:46 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *         
 */
defined('_JEXEC') or die('Restricted access');

class MosimageControllerOptions extends JControllerForm
{

    public function __construct ($config = array())
    {
        parent::__construct($config);
    }

    public function save ($key = null, $urlVar = null)
    {
        $result = parent::save($key, $urlVar);
        $task = $this->getTask();
        
        switch ($task) {
            case 'save':
                $document = JFactory::getDocument();
                $document->addScript(
                        JURI::root() .
                                 '/administrator/components/com_mosimage/js/mosimage.js');
                ?>
<script type="text/javascript">
					window.parent.SqueezeBox.close();	
				</script>
<?php
                // Der Aufruf von parent::save() setzt ein Redirect, der aber
                // wegen
                // dem Modal-Dialog hier nicht gewüscht ist.
                $this->setRedirect(null);
                break;
        }
        return $result;
    }
}

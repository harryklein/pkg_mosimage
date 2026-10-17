<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.viewmosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\EditorsXtd\ViewMosimage\Extension;

use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Button im Editor, der den Platzhalter {mosimage} an der Cursorposition einfügt.
 */
final class ViewMosimage extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    public function onEditorButtonsSetup(EditorButtonsSetupEvent $event): void
    {
        if (\in_array($this->_name, $event->getDisabledButtons(), true)) {
            return;
        }

        $this->loadLanguage();

        $event->getButtonsRegistry()->add(
            new Button(
                $this->_name,
                [
                    'action' => 'insert',
                    'text'   => Text::_('PLG_VIEWMOSIMAGE_BUTTON'),
                    'icon'   => 'pictures',
                    'name'   => $this->_type . '_' . $this->_name,
                ],
                [
                    'content' => '{mosimage}',
                ]
            )
        );
    }
}

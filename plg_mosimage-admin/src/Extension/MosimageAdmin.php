<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.mosimageadmin
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\EditorsXtd\MosimageAdmin\Extension;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Button im Editor eines Beitrags, der den Dialog zur Auswahl der Bilder (com_mosimage) öffnet.
 */
final class MosimageAdmin extends CMSPlugin implements SubscriberInterface
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

        $app = $this->getApplication();

        if (!$app instanceof CMSWebApplicationInterface) {
            return;
        }

        if (!$app->getIdentity()->authorise('core.edit', 'com_mosimage')) {
            return;
        }

        if (!$this->isEditAnArticle($event->getEditorId())) {
            return;
        }

        // Die Bilder werden über die ID des Beitrags zugeordnet, daher muss der Beitrag schon gespeichert sein.
        $id = $this->getIdFromRequest($app);

        if ($id === 0) {
            return;
        }

        $this->loadLanguage();

        $event->getButtonsRegistry()->add(
            new Button(
                $this->_name,
                [
                    'action'  => 'modal',
                    'link'    => 'index.php?option=com_mosimage&tmpl=component&task=options.edit&content_id=' . $id,
                    'text'    => Text::_('PLG_MOSIMAGE_ADMIN_BUTTON'),
                    'icon'    => 'images',
                    'iconSVG' => '<svg width="24" height="24" viewBox="0 0 576 512"><path d="M480 416v16c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V176'
                        . 'c0-26.51 21.49-48 48-48h16v208c0 44.112 35.888 80 80 80h336zm96-80V80c0-26.51-21.49-48-48-48H144c-26.51 0-48 21.49-48 48v256'
                        . 'c0 26.51 21.49 48 48 48h384c26.51 0 48-21.49 48-48zM256 128c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49'
                        . ' 48 48zm-96 144l55.515-55.515c4.686-4.686 12.284-4.686 16.971 0L272 256l135.515-135.515c4.686-4.686 12.284-4.686 16.971 0'
                        . 'L512 208v112H160v-48z"></path></svg>',
                    'name' => $this->_type . '_' . $this->_name,
                ],
                [
                    'popupType'  => 'iframe',
                    'textHeader' => Text::_('PLG_MOSIMAGE_ADMIN_BUTTON'),
                    'iconHeader' => 'icon-images',
                    'width'      => '95vw',
                    'height'     => '90vh',
                ]
            )
        );
    }

    private function isEditAnArticle(string $editorId): bool
    {
        return $editorId === 'jform_articletext';
    }

    private function getIdFromRequest(CMSWebApplicationInterface $app): int
    {
        $input = $app->getInput();

        if ($app->isClient('administrator')) {
            return max(0, $input->getInt('id'));
        }

        return max(0, $input->getInt('a_id'));
    }
}

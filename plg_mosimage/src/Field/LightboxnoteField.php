<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Field;

use Joomla\CMS\Form\Field\NoteField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Zeigt Informationen (Version, Autor, Lizenz) zu den unterstützten Lightboxen an.
 */
class LightboxnoteField extends NoteField
{
    protected $type = 'Lightboxnote';

    private const LIGHTBOXES = [
        ['Lytebox 3', '3.22', '-', 'http://lytebox.com/', 'Markus F. Hay', ['Creative Commons Attribution 3.0 License' => 'http://creativecommons.org/licenses/by/3.0/']],
        ['Lytebox 5', '5.5', '-', 'http://lytebox.com/', 'Markus F. Hay', ['Creative Commons Attribution 3.0 License' => 'http://creativecommons.org/licenses/by/3.0/']],
        ['Lightbox 2', '2.6', 'jQuery (Joomla)', 'https://lokeshdhakar.com/projects/lightbox2/', 'Lokesh Dhakar', ['Creative Commons Attribution 2.5 License' => 'http://creativecommons.org/licenses/by/2.5/']],
        ['Slimbox 2', '2.05', 'jQuery (Joomla)', 'https://www.digitalia.be/software/slimbox2', 'Christophe Beyls', ['MIT License' => 'https://opensource.org/licenses/mit-license.php']],
    ];

    protected function getLabel()
    {
        $html = '<table class="table table-sm table-bordered">'
            . '<thead><tr>'
            . '<th scope="col">' . Text::_('MOSIMAGE_LIGHTBOX_NAME') . '</th>'
            . '<th scope="col">' . Text::_('MOSIMAGE_LIGHTBOX_VERSION') . '</th>'
            . '<th scope="col">' . Text::_('MOSIMAGE_LIGHTBOX_USED_JS_LIBS') . '</th>'
            . '<th scope="col">' . Text::_('MOSIMAGE_LIGHTBOX_AUTHOR') . '</th>'
            . '<th scope="col">' . Text::_('MOSIMAGE_LIGHTBOX_LICENSE') . '</th>'
            . '</tr></thead><tbody>';

        foreach (self::LIGHTBOXES as [$name, $version, $libs, $url, $author, $licenses]) {
            $html .= '<tr><td><a href="' . $url . '" target="_blank" rel="noopener">' . $name . '</a></td>'
                . '<td>' . $version . '</td><td>' . $libs . '</td><td>' . $author . '</td><td>';

            foreach ($licenses as $license => $licenseUrl) {
                $html .= '<a href="' . $licenseUrl . '" target="_blank" rel="noopener">' . $license . '</a>';
            }

            $html .= '</td></tr>';
        }

        $html .= '</tbody></table>';

        return '<div class="alert alert-info"><h4>' . Text::_((string) $this->element['label']) . '</h4>' . $html . '</div>';
    }
}

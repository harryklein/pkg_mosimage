/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Schließt den Dialog des Editors, in dem com_mosimage angezeigt wird.
 */
if (window.parent && window.parent !== window) {
  window.parent.postMessage({ messageType: 'joomla:cancel' }, window.location.origin);
}

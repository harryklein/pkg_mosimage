/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Validierungs-Handler zum Überprüfen eines Eingabefeldes. Es kommen dabei folgende Regeln zum Einsatz:
 * - 1-4 Zahlen,
 * - optional mit einem % am Ende
 * - Whitespace vorn und hinten ignorieren
 */
document.addEventListener('DOMContentLoaded', () => {
  if (document.formvalidator) {
    document.formvalidator.setHandler('percentorabsolute', (value) => /^\s*\d{1,4}%?\s*$/.test(value));
  }
});

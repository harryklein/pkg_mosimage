/**
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2010 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 */

/**
 * Event-Handler zum Überprüfen eines Eingabefeldes. Es kommen dabei folgende Regeln zum Einsatz:
 * - 1-4 Zahlen, 
 * - optional mit einem % am Ende
 * - Whitespace vorm und hinten ignorieren
 */
window.addEvent('domready', function(){
	document.formvalidator.setHandler('percentorabsolute', function(value) {
		regex=/^\s*\d{1,4}%?\s*$/;
		return regex.test(value);
	});
});
/**
 * @version 2.0 $Id: mosimage.js,v 1.2 2014-01-18 22:37:04 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Plugin
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
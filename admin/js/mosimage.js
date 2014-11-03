function Mosimage() {

}

/**
 * @param list       id der Liste mit Bilder-URLs, relativ zu base_path
 * @param image      id eines src-Elements, wo das in der Liste ausgewählte Bild dargestellt werden soll 
 * @param base_path  Base-Url, um die relativen Bilder-URLs aus list erweitert werden kann
 */
Mosimage.previewImage = function(list, image, base_path) {
	var srcList = document.getElementById(list);
	var srcImage = document.getElementById(image);
	if ((srcList.length == 0) || (srcList.selectedIndex < 0)) {
		srcImage.src = base_path + '../media/system/images/blank.png';
		return
	}
	var srcOption = srcList.options[srcList.selectedIndex];
	var fileName = srcOption.value;

	if (fileName.length == 0) {
		srcImage.src = base_path + '../media/system/images/blank.png';
	} else {
		srcImage.src = base_path + fileName;
	}
}

Mosimage.getSelectedValue2 = function (srcListName) {
	var srcList = document.getElementById(srcListName);
	if (srcList == null) {
		return ''
	}
	i = srcList.selectedIndex;
	if (i != null && i > -1) {
		return srcList.options[i].value;
	} else {
		return '';
	}
}

Mosimage.setSelectedValue2 = function (srcListName, value) {
	var srcList = document.getElementById(srcListName);
	if (srcList == null) {
		return;
	}
	var srcLen = srcList.length;

	for ( var i = 0; i < srcLen; i++) {
		srcList.options[i].selected = false;
		if (srcList.options[i].value == value) {
			srcList.options[i].selected = true;
		}
	}
}


Mosimage.showImageProps = function(base_path) {
	var value = Mosimage.getSelectedValue2('jform_images');
	var parts = value.split('|');
	document.getElementById('jform__source').value = parts[0];

	if (parts[1] == null) {
		value = 'left';
	} else {
		value = parts[1] || '';
	}
	Mosimage.setSelectedValue2('jform__align', value);
	document.getElementById('jform__alt').value = parts[2] || '';
	document.getElementById('jform__border').value = parts[3] || '0';
	document.getElementById('jform__caption').value = parts[4] || '';
	Mosimage.setSelectedValue2('jform__caption_position', parts[5] || '');
	Mosimage.setSelectedValue2('jform__caption_align', parts[6] || '');
	var form = document.adminForm;
	form._width.value = parts[7] || '';

	srcImage = document.getElementById('jform_view_imagelist');
	if (parts[0]){
		srcImage.src = base_path + parts[0];
	} else {
		srcImage.src = base_path + '../media/system/images/blank.png';
	}
}

Mosimage.applyImageProps = function () {
	var form = document.adminForm;
	if (!Mosimage.getSelectedValue2('jform_images')) {
		alert("Select and image from the list");
		return;
	}
	value = document.getElementById('jform__source').value + '|'
			+ Mosimage.getSelectedValue2('jform__align') + '|'
			+ document.getElementById('jform__alt').value + '|'
			+ Mosimage.getSelectedValue2('jform__border') + '|'
			+ document.getElementById('jform__caption').value + '|'
			+ Mosimage.getSelectedValue2('jform__caption_position') + '|'
			+ Mosimage.getSelectedValue2('jform__caption_align') + '|'
			+ form._width.value;
	Mosimage.chgSelectedValue('jform_images', value);
}

Mosimage.chgSelectedValue = function (srcListName, value) {
	var srcList = document.getElementById(srcListName);

	i = srcList.selectedIndex;
	if (i != null && i > -1) {
		srcList.options[i].value = value;
		return true;
	} else {
		return false;
	}
}

Mosimage.delSelectedFromList2 = function (frmName, srcListName) {
	var srcList = document.getElementById(srcListName);
	var selectedIndex = srcList.selectedIndex;

	var srcLen = srcList.length;

	for ( var i = srcLen - 1; i > -1; i--) {
		if (srcList.options[i].selected) {
			srcList.options[i] = null;
		}
	}

	selectedIndex--;
	if (selectedIndex < 0){
		selectedIndex = 0;
	}
	srcList.selectedIndex = selectedIndex;
	Mosimage.showImageProps(JOOMLA_ROOT + 'images/')
}

Mosimage.addSelectedToList2 = function (frmName, srcListName, tgtListName) {
	var form = eval('document.' + frmName);
	var srcList = eval('form.' + srcListName);
	var tgtList = document.getElementById(tgtListName);

	var srcLen = srcList.length;
	var tgtLen = tgtList.length;
	var tgt = "x";

	// build array of target items
	for ( var i = tgtLen - 1; i > -1; i--) {
		tgt += "," + tgtList.options[i].value + ","
	}

	// Pull selected resources and add them to list
	// for (var i=srcLen-1; i > -1; i--) {
	for ( var i = 0; i < srcLen; i++) {
		if (srcList.options[i].selected /*
										 * && tgt.indexOf( "," +
										 * srcList.options[i].value + "," ) ==
										 * -1
										 */) {
			opt = new Option(srcList.options[i].text, srcList.options[i].value);
			tgtList.options[tgtList.length] = opt;
		}
	}
}


Mosimage.moveInList = function (frmName, srcListName, to) {
	var srcList = document.getElementById(srcListName); // eval( 'form.' +
														// srcListName );
	var total = srcList.options.length - 1;
	var index = srcList.selectedIndex;

	if (index == -1) {
		return false;
	}

	if (to == 0) {
		to = -1 * index;
	}

	if (to == 'end') {
		to = total;
	}

	if (to > 0 && index == total) {
		return false;
	}
	if (to < 0 && index == 0) {
		return false;
	}

	var fromIndex = index;
	var toIndex = index + to;

	/*
	 * var options = new Array; for (i=total; i >= 0; i--) { options[i] =
	 * srcList.options[i] } var element = options[fromIndex]
	 * 
	 * options.splice(fromIndex, 1); options.splice(toIndex, 0, element);
	 * 
	 * for (i = total; i >= 0; i--) { srcList.options[i] = options[i]; }
	 */

	var items = new Array;
	var values = new Array;
	for (i = total; i >= 0; i--) {
		items[i] = srcList.options[i].text;
		values[i] = srcList.options[i].value;
	}

	var elementItem = items[fromIndex]
	var elementValue = values[fromIndex]

	items.splice(fromIndex, 1);
	items.splice(toIndex, 0, elementItem);
	values.splice(fromIndex, 1);
	values.splice(toIndex, 0, elementValue);

	for (i = total; i >= 0; i--) {
		srcList.options[i] = new Option(items[i], values[i]);
		if (i == toIndex) {
			srcList.options[i].selected = true;
		}
	}

	srcList.selectedIndex = toIndex;
	srcList.focus();
	return true;
}


Mosimage.changeDynaList2 = function (listname, source, listWithFolderNames, orig_key,
		orig_val) {

	var folder_list = document.getElementById(listWithFolderNames);
	var selected_Elemenet_in_Folder_list = document
			.getElementById(listWithFolderNames).selectedIndex;
	var key = folder_list.options[selected_Elemenet_in_Folder_list].value;

	var list = document.getElementById(listname);

	// empty the list
	for (i in list.options.length) {
		list.options[i] = null;
	}
	i = 0;
	for (x in source) {
		if (source[x][0] == key) {
			opt = new Option();
			opt.value = source[x][1];
			opt.text = source[x][2];

			if ((orig_key == key && orig_val == opt.value) || i == 0) {
				opt.selected = true;
			}
			list.options[i++] = opt;
		}
	}
	list.length = i;
	list.selectedIndex = 0;
	Mosimage.previewImage(listname, 'jform_view_imagefiles', JOOMLA_ROOT + 'images/');
}

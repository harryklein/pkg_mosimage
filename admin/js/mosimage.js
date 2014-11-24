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
	srcList.defaultValue = value;
	var srcLen = srcList.length;
	for ( var i = 0; i < srcLen; i++) {
		srcList.options[i].selected = false;
		if (srcList.options[i].value == value) {
			srcList.options[i].selected = true;
		}
	}
}

Mosimage.initShowImageProps = function(base_path, messages) {
	this.messages = messages;
	var list = document.getElementById('jform_imageslist');
	list.wasChanged = false;
	list.previousSelectedIndex = list.selectedIndex;
	Mosimage.showImagePropsWithoutChecks(base_path);
}


Mosimage.showImagePropsWithoutChecks  = function(base_path){
	var value = Mosimage.getSelectedValue2('jform_imageslist');
	
	var props = JSON.parse(value);
	document.getElementById('jform__source').value = props.source;

	if (props.align == null) {
		value = 'left';
	} else {
		value = props.align || '';
	}
	Mosimage.setSelectedValue2('jform__align', value);
	
	document.getElementById('jform__alt').value = props.alt || '';
	document.getElementById('jform__alt').defaultValue = props.alt || '';
	
	document.getElementById('jform__border').value = props.border || '0';
	document.getElementById('jform__border').defaultValue = props.border || '0';
	
	document.getElementById('jform__caption').value = props.caption || '';
	document.getElementById('jform__caption').defaultValue = props.caption || '';
	
	Mosimage.setSelectedValue2('jform__caption_position', props.caption_position || '');
	
	Mosimage.setSelectedValue2('jform__caption_align', props.caption_align || '');
	
	var form = document.adminForm;
	form._width.value = props.width || '';

	if ( 'undefined' === typeof base_path) {
		return;
	}
	srcImage = document.getElementById('jform_view_imagelist');
	if (props.source){
		srcImage.src = base_path + props.source;
	} else {
		srcImage.src = base_path + '../media/system/images/blank.png';
	}
}


Mosimage.isValueChanged = function(elementId){
	var defaultValue = document.getElementById(elementId).defaultValue;
	var currentValue = document.getElementById(elementId).value;
	if (defaultValue == currentValue ) {
		return false;
	} 
	return true;
}


Mosimage.isValueChangedInSelect = function(elementId){
	var aa = document.getElementById(elementId).selectedOptions;
	var defaultValue = document.getElementById(elementId).defaultValue;
	var currentValue = document.getElementById(elementId).value;
	if (defaultValue == currentValue ) {
		return false;
	} 
	return true;
}


Mosimage.showImageProps = function(base_path) {
	var list = document.getElementById('jform_imageslist');
	if (Mosimage.isValueChangedInSelect('jform__align') ||
			Mosimage.isValueChanged('jform__alt') ||
			Mosimage.isValueChangedInSelect('jform__border') ||
			Mosimage.isValueChanged('jform__caption') || 
			Mosimage.isValueChangedInSelect('jform__caption_position') 
			){  
			if (confirm(this.messages[0])){
				Mosimage.applyImageProps(true);
			} else {
				// nun doch zu den alten Daten zurück, die noch in den Feldern zu sehen ist
				list.selectedIndex = list.previousSelectedIndex;
				return;
			}
	}
	Mosimage.showImagePropsWithoutChecks(base_path);
	list.previousSelectedIndex = list.selectedIndex;
}


Mosimage.resetImageProps = function () {
	document.getElementById('jform__align').value = document.getElementById('jform__align').defaultValue;
	document.getElementById('jform__alt').value = document.getElementById('jform__alt').defaultValue;
	document.getElementById('jform__caption').value = document.getElementById('jform__caption').defaultValue;
	
	document.getElementById('jform__border').value = document.getElementById('jform__border').defaultValue;
	document.getElementById('jform__caption_position').value = document.getElementById('jform__caption_position').defaultValue;
}

/**
 * 
 * jform_imageslist: Liste mit den Image-Daten
 * @param previous bei true werden die Image-Daten nicht aus dem selectedIndex, 
 * 		sonder aus dem previousSelectedIndex geholt. Default ist false
 */
Mosimage.applyImageProps = function (previous) {
	if ('undefined' === typeof previous) {
		previous = false;
    }
	
	var form = document.adminForm;
	var obj = {
		    source           : document.getElementById('jform__source').value,
		    align            : Mosimage.getSelectedValue2('jform__align'),
		    alt              : document.getElementById('jform__alt').value,
		    border           : Mosimage.getSelectedValue2('jform__border'),
		    caption          : document.getElementById('jform__caption').value,
		    caption_position : Mosimage.getSelectedValue2('jform__caption_position'),
		    caption_align    : Mosimage.getSelectedValue2('jform__caption_align'),
		    width            : form._width.value 
		};
	var value = JSON.stringify(obj);
	Mosimage.chgSelectedValue('jform_imageslist', value, previous);
	Mosimage.showImagePropsWithoutChecks();
}

Mosimage.chgSelectedValue = function (srcListName, value, previous) {
	if ('undefined' === typeof previous) {
		previous = false;
    }
	var srcList = document.getElementById(srcListName);
	if (previous){
		var i = srcList.previousSelectedIndex;
	} else {
		var i = srcList.selectedIndex;
	}
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
	Mosimage.showImageProps(JOOMLA_ROOT + 'images/');
	srcList.wasChanged = true;
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
	tgtList.wasChanged = true;
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
	srcList.wasChanged = true;
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

Mosimage.closeWindows = function (){
	window.parent.SqueezeBox.close();
}

Mosimage.convertToJson = function (temp){
	var myJsonString = JSON.stringify(temp);
	return;
}


Mosimage.revertChanges = function(){
	var list = document.getElementById('jform_imageslist');
	if (list.wasChanged === true){
		if (confirm('Änderungen verwerfen?')){
			Mosimage.closeWindows();
			return true;
		} else {
			return false;
		}
	} else {
		Mosimage.closeWindows();
		return true;
	}
}




/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Dialog zur Auswahl der Bilder eines Beitrags.
 *
 * Die Liste jform_imageslist enthält pro Bild eine Option, deren Wert die Properties des Bildes als JSON enthält:
 * {"source":"fruit/cherry.jpg","align":"left","alt":"","border":"0","caption":"","caption_position":"", ...}
 */
((document, Joomla) => {
  'use strict';

  const BLANK_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

  const Mosimage = {
    imagesUrl: '',
    folderImages: {},
    wasChanged: false,
    previousSelectedIndex: -1,

    byId: (id) => document.getElementById(id),

    /**
     * Zeigt das in der Liste list ausgewählte Bild im img-Element image an.
     */
    previewImage(list, image) {
      const srcList = this.byId(list);
      const srcImage = this.byId(image);
      const fileName = srcList && srcList.selectedIndex >= 0 ? srcList.options[srcList.selectedIndex].value : '';

      srcImage.src = fileName ? this.imagesUrl + fileName : BLANK_IMAGE;
    },

    getSelectedValue(listName) {
      const list = this.byId(listName);

      if (!list || list.selectedIndex < 0) {
        return '';
      }

      return list.options[list.selectedIndex].value;
    },

    /**
     * Setzt den Wert eines Feldes und merkt sich diesen als Ausgangswert (für die Erkennung von Änderungen).
     */
    setValue(id, value) {
      const element = this.byId(id);

      if (!element) {
        return;
      }

      element.value = value;

      // Bei einer Liste ohne passenden Eintrag den ersten Eintrag wählen
      if (element.tagName === 'SELECT' && element.selectedIndex < 0 && element.options.length) {
        element.selectedIndex = 0;
      }

      element.dataset.mosimageDefault = element.value;
    },

    isValueChanged(id) {
      const element = this.byId(id);

      return element && element.value !== (element.dataset.mosimageDefault ?? element.value);
    },

    getSelectedProps() {
      const value = this.getSelectedValue('jform_imageslist');

      if (!value) {
        return null;
      }

      try {
        return JSON.parse(value);
      } catch (e) {
        return null;
      }
    },

    showImagePropsWithoutChecks() {
      const props = this.getSelectedProps() || {};

      this.setValue('jform__source', props.source || '');
      this.setValue('jform__align', props.align == null ? 'left' : props.align);
      this.setValue('jform__alt', props.alt || '');
      this.setValue('jform__border', props.border || '0');
      this.setValue('jform_accesslevel', props.accesslevel || '1');
      this.setValue('jform__caption', props.caption || '');
      this.setValue('jform__caption_position', props.caption_position || '');
      this.byId('mosimage_caption_align').value = props.caption_align || '';
      this.byId('mosimage_width').value = props.width || '';

      this.byId('jform_view_imagelist').src = props.source ? this.imagesUrl + props.source : BLANK_IMAGE;
    },

    isAnyPropChanged() {
      return ['jform__align', 'jform_accesslevel', 'jform__alt', 'jform__border', 'jform__caption', 'jform__caption_position']
        .some((id) => this.isValueChanged(id));
    },

    showImageProps() {
      const list = this.byId('jform_imageslist');

      if (this.byId('jform__source').value.length !== 0 && this.isAnyPropChanged()) {
        if (window.confirm(Joomla.Text._('COM_MOSIMAGE_APLAY_CHANGED_VALUES'))) {
          this.applyImageProps(true);
        } else {
          // nun doch zu den alten Daten zurück, die noch in den Feldern zu sehen sind
          list.selectedIndex = this.previousSelectedIndex;

          return;
        }
      }

      this.showImagePropsWithoutChecks();
      this.previousSelectedIndex = list.selectedIndex;
    },

    resetImageProps() {
      ['jform__align', 'jform__alt', 'jform__caption', 'jform__border', 'jform_accesslevel', 'jform__caption_position'].forEach((id) => {
        const element = this.byId(id);

        if (element && element.dataset.mosimageDefault !== undefined) {
          element.value = element.dataset.mosimageDefault;
        }
      });
    },

    /**
     * Übernimmt die Werte der Eingabefelder in die Liste jform_imageslist.
     *
     * @param {boolean} previous bei true werden die Werte nicht in den selectedIndex,
     *                           sondern in den previousSelectedIndex geschrieben.
     */
    applyImageProps(previous = false) {
      const list = this.byId('jform_imageslist');
      const index = previous ? this.previousSelectedIndex : list.selectedIndex;

      if (index == null || index < 0 || index >= list.options.length) {
        return;
      }

      const props = {
        source: this.byId('jform__source').value,
        align: this.byId('jform__align').value,
        alt: this.byId('jform__alt').value,
        border: this.byId('jform__border').value,
        accesslevel: this.byId('jform_accesslevel').value,
        caption: this.byId('jform__caption').value,
        caption_position: this.byId('jform__caption_position').value,
        caption_align: this.byId('mosimage_caption_align').value,
        width: this.byId('mosimage_width').value,
      };

      list.options[index].value = JSON.stringify(props);
      this.wasChanged = true;

      if (!previous) {
        this.showImagePropsWithoutChecks();
      }
    },

    delSelectedFromList() {
      const list = this.byId('jform_imageslist');
      let { selectedIndex } = list;

      for (let i = list.options.length - 1; i > -1; i -= 1) {
        if (list.options[i].selected) {
          list.remove(i);
        }
      }

      selectedIndex = Math.max(0, Math.min(selectedIndex - 1, list.options.length - 1));
      list.selectedIndex = list.options.length ? selectedIndex : -1;
      this.showImagePropsWithoutChecks();
      this.previousSelectedIndex = list.selectedIndex;
      this.wasChanged = true;
    },

    addSelectedToList() {
      const srcList = this.byId('jform_imagefiles');
      const tgtList = this.byId('jform_imageslist');

      Array.from(srcList.options).filter((option) => option.selected).forEach((option) => {
        tgtList.add(new Option(option.text, JSON.stringify({ source: option.value })));
      });

      if (tgtList.options.length > 0) {
        tgtList.selectedIndex = tgtList.options.length - 1;
        this.showImageProps();
      }

      this.wasChanged = true;
    },

    /**
     * Verschiebt den ausgewählten Eintrag. to ist 'begin', 'end' oder eine relative Position (-1, +1).
     * Bei 'begin'/'end' bleibt die Auswahl an der alten Position, außer die Shift-Taste ist gedrückt.
     */
    moveInList(to, event) {
      const list = this.byId('jform_imageslist');
      const total = list.options.length - 1;
      const fromIndex = list.selectedIndex;

      if (fromIndex === -1 || (to > 0 && fromIndex === total) || (to < 0 && fromIndex === 0)) {
        list.focus();

        return;
      }

      let toIndex;

      if (to === 'begin') {
        toIndex = 0;
      } else if (to === 'end') {
        toIndex = total;
      } else {
        toIndex = fromIndex + to;
      }

      const option = list.options[fromIndex];
      list.remove(fromIndex);
      list.add(option, toIndex <= total - 1 ? toIndex : null);

      Array.from(list.options).forEach((o) => { o.selected = false; });

      if ((to === 'end' || to === 'begin') && !(event && event.shiftKey)) {
        list.selectedIndex = fromIndex;
      } else {
        list.selectedIndex = toIndex;
      }

      this.showImagePropsWithoutChecks();
      this.previousSelectedIndex = list.selectedIndex;
      this.wasChanged = true;
      list.focus();
    },

    /**
     * Füllt die Liste der verfügbaren Bilder mit den Bildern des ausgewählten Ordners.
     */
    changeFolder() {
      const folder = this.byId('jform_folder').value;
      const list = this.byId('jform_imagefiles');

      list.options.length = 0;
      (this.folderImages[folder] || []).forEach((image) => list.add(new Option(image.text, image.value)));

      if (list.options.length) {
        list.selectedIndex = 0;
      }

      this.previewImage('jform_imagefiles', 'jform_view_imagefiles');
    },

    /**
     * Schließt den Dialog des Editors
     */
    closeWindow() {
      if (window.parent && window.parent !== window) {
        window.parent.postMessage({ messageType: 'joomla:cancel' }, window.location.origin);
      }
    },

    submit(task) {
      const form = this.byId('adminForm');

      if (task === 'options.cancel') {
        if (this.wasChanged && !window.confirm(Joomla.Text._('COM_MOSIMAGE_DISALLOW_CHANGED_VALUES'))) {
          return;
        }

        Joomla.submitform(task, form);

        return;
      }

      if (this.isAnyPropChanged()) {
        this.applyImageProps();
      }

      // die Bilder wieder in einem Feld zusammenführen
      const values = Array.from(this.byId('jform_imageslist').options).map((option) => option.value);
      this.byId('jform_images').value = `[${values.join(',')}]`;

      Joomla.submitform(task, form);
    },

    init() {
      const options = Joomla.getOptions('com_mosimage.edit') || {};

      this.imagesUrl = options.imagesUrl || '';
      this.folderImages = options.folderImages || {};

      const on = (id, eventName, handler) => {
        const element = this.byId(id);

        if (element) {
          element.addEventListener(eventName, handler);
        }
      };

      on('jform_folder', 'change', () => this.changeFolder());
      on('jform_imagefiles', 'change', () => this.previewImage('jform_imagefiles', 'jform_view_imagefiles'));
      on('jform_imageslist', 'change', () => this.showImageProps());
      on('jform_add', 'click', () => this.addSelectedToList());
      on('jform_remove', 'click', () => this.delSelectedFromList());
      on('jform_pos1', 'click', (event) => this.moveInList('begin', event));
      on('jform_up', 'click', (event) => this.moveInList(-1, event));
      on('jform_down', 'click', (event) => this.moveInList(1, event));
      on('jform_end', 'click', (event) => this.moveInList('end', event));
      on('jform_applay', 'click', () => this.applyImageProps());
      on('jform_reset', 'click', () => this.resetImageProps());

      document.querySelectorAll('[data-mosimage-task]').forEach((button) => {
        button.addEventListener('click', () => this.submit(button.dataset.mosimageTask));
      });

      this.changeFolder();

      // 1. Bild selektieren
      const list = this.byId('jform_imageslist');

      if (list.selectedIndex < 0 && list.options.length) {
        list.selectedIndex = 0;
      }

      this.previousSelectedIndex = list.selectedIndex;
      this.showImagePropsWithoutChecks();
    },
  };

  // Auch die Toolbar von Joomla (falls vorhanden) über Mosimage absenden
  Joomla.submitbutton = (task) => Mosimage.submit(task);

  document.addEventListener('DOMContentLoaded', () => Mosimage.init());

  window.Mosimage = Mosimage;
})(document, Joomla);

<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Helper;

use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class MosimageDirProperties
{
    public const MAX_AMOUNT_IMAGES = 30;

    public const KEY_RANDOM = 'random';

    public const KEY_FOLDER = 'folder';

    public const KEY_FILE = 'file';

    public const KEY_TITLE = 'title';

    public const KEY_BORDER = 'border';

    public const KEY_IMAGE_ALIGN = 'align';

    public const KEY_POS = 'pos';

    public const KEY_COUNT_OF_IMAGES = '#';

    public const DELEMITER = '=';

    public const FILE_FILTER = '\.[gG][iI][fF]$|\.[jJ][pP][gG]$|\.[jJ][pP][eE][gG]$|\.[pP][nN][gG]$|\.[wW][eE][bB][pP]$';

    private array $param = [];

    /**
     * Object wird mittels MosimageDirProperties::parse($value) erzeugt.
     */
    private function __construct(string $value)
    {
        preg_match_all('/[a-zA-Z0-9#]+' . self::DELEMITER . '\[(.*?)\]/', $value, $matches);

        foreach ($matches[0] as $attr) {
            [$key, $rawValue]  = explode(self::DELEMITER, $attr, 2);
            $this->param[$key] = substr($rawValue, 1, -1);
        }
    }

    /**
     * Liefert die Information aus dem Platzhalter der Form
     * {mosimage folder=[tt-hessen/module/Tanklager/] title=[Dauerleihgabe Tanklager] random=[false] align=[left] border=[1] pos=[] #=[5]}
     *
     * folder  Ordner relativ zu images/, der die Bilder enthält.
     * title   (optional) Titel aller Bilder. Default ist der Name der folders.
     * random  (optional) Bei true ist Reihenfolge der Bilder zufällig, ansonten nach Filename aufsteigend sortiert. Default ist false.
     * align
     * border
     * pos
     * #       maximale Anzahl Bilder, default ist 1
     *
     * @param string $value Platzhalter inkl. den Parametern, der durch ein Bild oder mehrere Bilder ersetzt werden sollen.
     */
    public static function parse(string $value): MosimageDirProperties
    {
        return new MosimageDirProperties($value);
    }

    /**
     * @return \stdClass[]|false
     */
    public function getImgagePropertiesAsArrayObject()
    {
        try {
            $result = [];

            foreach ($this->getFileNames() as $file) {
                $item                   = new \stdClass();
                $item->source           = $file;
                $item->file             = $this->getFile();
                $item->align            = $this->getImageAlign();
                $item->alt              = $this->getTitle();
                $item->border           = $this->getBorder();
                $item->caption          = $this->getTitle();
                $item->caption_position = $this->getCaptionPosition();
                $result[]               = $item;
            }

            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Name des Folders, aus dem das Image kommen soll
     *
     * @throws \InvalidArgumentException Wenn Folder nicht angegeben
     */
    private function getRelativeFolderName(): string
    {
        if (\array_key_exists(self::KEY_FOLDER, $this->param)) {
            // Kein Verlassen des Ordners images/
            return trim(str_replace(['..', '\\'], ['', '/'], $this->param[self::KEY_FOLDER]), '/');
        }

        throw new \InvalidArgumentException("Can't found parameter folder");
    }

    /**
     * Name des Titels. Wenn nicht angegeben, so wird als Titel der Name des Folders zurückgegeben.
     */
    private function getTitle(): string
    {
        if (\array_key_exists(self::KEY_TITLE, $this->param)) {
            return $this->param[self::KEY_TITLE];
        }

        return str_replace('_', ' ', basename('images/' . $this->getRelativeFolderName()));
    }

    /**
     * Liefert den Paramter 'file'. Wenn nicht angegeben, wird 'cover.jpg' zurückgegeben.
     */
    private function getFile(): string
    {
        return $this->param[self::KEY_FILE] ?? 'cover.jpg';
    }

    private function getCaptionPosition(): string
    {
        return $this->param[self::KEY_POS] ?? '';
    }

    /**
     * Liefert die Breite des Rahmens um das Bild. Default ist 0 (Wert der Konfiguration)
     */
    private function getBorder(): string
    {
        return $this->param[self::KEY_BORDER] ?? '0';
    }

    /**
     * Liefert die Ausrichtung des Images. Gültige Werte sind left, right und none. Default ist left.
     */
    private function getImageAlign(): string
    {
        $align = $this->param[self::KEY_IMAGE_ALIGN] ?? '';

        return \in_array($align, ['left', 'right', 'none'], true) ? $align : 'left';
    }

    /**
     * Liefert eine oder mehrere Dateinamen (mit den Endungen gif, jpg, jpeg, png und webp) aus dem Ordner
     * 'images/'. $this->getRelativeFolderName().
     *
     *  maxAmount = 1
     *  (c)  random = true   => ein zufälliges Bild
     *  (d1) random = false  => entweder die mit file übergebene Datei, cover.jpg oder
     *  (d2)                 => das 1. Bild, wenn cover.jpg nicht existiert.
     *  maxAmount > 1
     *  (e)  random = true   => zufällig maxAmount
     *  (f)  random = false  => die ersten maxAmount
     *
     * @throws \RuntimeException Wenn keine Image-Dateien im Ordner vorhanden sind.
     */
    private function getFileNames(): array
    {
        $relativeFolder = $this->getRelativeFolderName();
        $folder         = JPATH_ROOT . '/images/' . $relativeFolder;

        if (!is_dir($folder)) {
            throw new \RuntimeException('Folder [images/' . $relativeFolder . "] don't exist.");
        }

        $allFiles = Folder::files($folder, self::FILE_FILTER);

        if ($this->getMaxAmountImages() === 1) {
            if ($this->isRandom()) {
                // c
                shuffle($allFiles);
            } elseif (is_file($folder . '/' . basename($this->getFile()))) {
                // d1
                return [$relativeFolder . '/' . basename($this->getFile())];
            }

            // c, d2
            if ($allFiles) {
                return [$relativeFolder . '/' . $allFiles[0]];
            }

            throw new \RuntimeException('Folder [images/' . $relativeFolder . "] don't contain any images.");
        }

        // e, f
        if ($this->isRandom()) {
            shuffle($allFiles);
        }

        $files = [];

        foreach (\array_slice($allFiles, 0, $this->getMaxAmountImages()) as $file) {
            $files[] = $relativeFolder . '/' . $file;
        }

        if (!$files) {
            throw new \RuntimeException('Folder [images/' . $relativeFolder . "] don't contain any images.");
        }

        return $files;
    }

    /**
     * Liefert true, wenn Image zufällig ausgewählt werden soll. Default ist false.
     */
    private function isRandom(): bool
    {
        return ($this->param[self::KEY_RANDOM] ?? '') === 'true';
    }

    /**
     * Liefert die maximale Anzahl Bilder, die angezeigt werden sollen. Default ist 1.
     * Der maximale Wert ist self::MAX_AMOUNT_IMAGES, 0 steht für alle (maximal self::MAX_AMOUNT_IMAGES).
     */
    private function getMaxAmountImages(): int
    {
        if (!\array_key_exists(self::KEY_COUNT_OF_IMAGES, $this->param)) {
            return 1;
        }

        $value = (int) $this->param[self::KEY_COUNT_OF_IMAGES];

        if ($value < 0) {
            return 1;
        }

        if ($value > self::MAX_AMOUNT_IMAGES || $value === 0) {
            return self::MAX_AMOUNT_IMAGES;
        }

        return $value;
    }
}

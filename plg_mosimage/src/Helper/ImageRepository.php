<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Helper;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Liest die einem Beitrag zugeordneten Bilder aus der Tabelle #__mosimage.
 */
class ImageRepository
{
    private DatabaseInterface $db;

    /** @var array<int, \stdClass[]> */
    private array $cache = [];

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Liefert die Image-Properties des Beitrags als Liste von Objekten, z.B.
     * <pre>
     * {"source":"stories/fruit/cherry.jpg","align":"left","alt":"","border":"0","caption":"", ...}
     * </pre>
     *
     * @return \stdClass[]
     */
    public function getImages(int $contentId): array
    {
        if ($contentId <= 0) {
            return [];
        }

        if (!\array_key_exists($contentId, $this->cache)) {
            $query = $this->db->getQuery(true)
                ->select($this->db->quoteName('images'))
                ->from($this->db->quoteName('#__mosimage'))
                ->where($this->db->quoteName('content_id') . ' = :id')
                ->bind(':id', $contentId, ParameterType::INTEGER);

            $images                    = (string) $this->db->setQuery($query)->loadResult();
            $this->cache[$contentId] = self::convertToObjectArray($images);
        }

        // Kopien liefern, damit Änderungen (z.B. align) nicht in den Cache durchschlagen
        return array_map(static fn ($item) => clone $item, $this->cache[$contentId]);
    }

    /**
     * Liefert introtext und fulltext eines Beitrags.
     */
    public function getArticleTexts(int $contentId): ?\stdClass
    {
        if ($contentId <= 0) {
            return null;
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['introtext', 'fulltext']))
            ->from($this->db->quoteName('#__content'))
            ->where($this->db->quoteName('id') . ' = :id')
            ->bind(':id', $contentId, ParameterType::INTEGER);

        return $this->db->setQuery($query)->loadObject() ?: null;
    }

    /**
     * Konvertiert das Json-Format bzw. das alte '|'-Format in ein Array mit Objekten.
     *
     * @return \stdClass[]
     */
    public static function convertToObjectArray(string $images): array
    {
        $images = trim($images);

        if ($images === '') {
            return [];
        }

        $jsonObjects = json_decode($images, false);

        if ($jsonObjects === null) {
            return self::convertOldImagelistFormat($images);
        }

        $result = [];

        foreach ((array) $jsonObjects as $item) {
            if (\is_object($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Konvertiert das alte Format aus Joomla 1.0 (eine Zeile pro Bild, Werte durch '|' getrennt).
     *
     * @return \stdClass[]
     */
    public static function convertOldImagelistFormat(string $images): array
    {
        $imageslist = preg_replace("#(\n\r)+#", '', $images);
        $imageslist = preg_replace("#(\r)*#", '', $imageslist);

        $result = [];

        foreach (explode("\n", $imageslist) as $imageAsString) {
            if (trim($imageAsString) === '') {
                continue;
            }

            $properties              = array_pad(explode('|', $imageAsString), 8, '');
            $image                   = new \stdClass();
            $image->source           = $properties[0];
            $image->align            = $properties[1];
            $image->alt              = $properties[2];
            $image->border           = $properties[3];
            $image->caption          = $properties[4];
            $image->caption_position = $properties[5];
            $image->caption_align    = $properties[6];
            $image->width            = $properties[7];
            $result[]                = $image;
        }

        return $result;
    }
}

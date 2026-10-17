<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * H2N Mosimage Plugin is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * H2N Mosimage Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

namespace HKlein\Plugin\Content\Mosimage\Extension;

use HKlein\Plugin\Content\Mosimage\Helper\HtmlHelper;
use HKlein\Plugin\Content\Mosimage\Helper\ImageRepository;
use HKlein\Plugin\Content\Mosimage\Helper\MosimageDirProperties;
use HKlein\Plugin\Content\Mosimage\Helper\PluginConfiguration;
use HKlein\Plugin\Content\Mosimage\Lightbox\LightboxFactory;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class Mosimage extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    public const REGEX        = '/{mosimage\s*.*?}/i';
    public const REGEX_DIR    = '/{mosimage\s*folder=.*?}/i';
    public const REGEX_RANDOM = '/{mosimage\s*random}/i';

    private const HTML_FOR_STOP_FLOATING = '<div style="clear:both">&nbsp;</div>';

    private const SUPPORTED_CONTEXTS = [
        'com_content.featured',
        'com_content.category',
        'com_content.article',
        'com_tags.tag',
    ];

    protected $autoloadLanguage = true;

    private ?ImageRepository $repository = null;

    public static function getSubscribedEvents(): array
    {
        return ['onContentBeforeDisplay' => 'onContentBeforeDisplay'];
    }

    public function onContentBeforeDisplay(BeforeDisplayEvent $event): void
    {
        // Bemerkung zu $row
        // Article
        // introtext: Intro
        // fulltext : Haupttext (ohne Intro)
        // text     : Text, der angezeigt wird (intro + Hauptext bzw. nur Haupttext)

        // Frontpage:
        // introtext: Intro
        // fulltext : nicht vorhanden
        // text     : nicht vorhanden

        // com_tags
        // introtext: nicht vorhanden
        // fulltext: nicht vorhannden
        // text: Intro oder Hauptext, wenn Intro nicht vorhanden
        $context = $event->getContext();

        if (!\in_array($context, self::SUPPORTED_CONTEXTS, true)) {
            return;
        }

        $row = $event->getItem();

        if ($this->isNothingToDo($row)) {
            return;
        }

        try {
            $params = clone $event->getParams();

            if ($context === 'com_tags.tag') {
                // Nur Beiträge haben Mosimage-Bilder; die ID des Beitrags steht in content_item_id
                if (($row->type_alias ?? 'com_content.article') !== 'com_content.article') {
                    return;
                }

                $row->introtext = $row->text ?? '';
                $articleId      = (int) ($row->content_item_id ?? $row->id ?? 0);
            } else {
                $params->merge(new Registry($row->attribs ?? ''));
                $articleId = (int) ($row->id ?? 0);
            }

            $row->introtext ??= '';

            $isReplacePlaceholderRandom = $this->replacePlaceHolder($row, $articleId, $params, self::REGEX_RANDOM, 'processImagesRandom');
            $isReplacePlaceholderDir    = $this->replacePlaceHolder($row, $articleId, $params, self::REGEX_DIR, 'processImagesDir');
            $isReplacePlaceholder       = $this->replacePlaceHolder($row, $articleId, $params, self::REGEX, 'processImages');
            $this->replaceClearFlotingPlaceHolder($row);

            if (($isReplacePlaceholderDir || $isReplacePlaceholder || $isReplacePlaceholderRandom) && !empty($row->text)) {
                $row->text .= self::HTML_FOR_STOP_FLOATING;
            }
        } catch (\Throwable $e) {
            Log::add('Mosimage: ' . $e->getMessage(), Log::WARNING, 'mosimage');
        }
    }

    private function replaceClearFlotingPlaceHolder(object $row): void
    {
        $regex          = '/{clear-floting}/i';
        $row->introtext = preg_replace($regex, self::HTML_FOR_STOP_FLOATING, $row->introtext);

        if (!empty($row->text)) {
            $row->text = preg_replace($regex, self::HTML_FOR_STOP_FLOATING, $row->text);
        }
    }

    /**
     * @return boolean liefert true, wenn Platzhalter ersetzt worden sind, sonst false
     */
    private function replacePlaceHolder(object $row, int $articleId, Registry $params, string $regex, string $processImagesFunction): bool
    {
        // text enthält auch das Intro, wenn es angezeigt werden soll.
        // ohne Anzeige des Intros müssen die Bilder aus Intro übersprungen werden
        preg_match_all($regex, $row->introtext, $matchesInIntro);
        $introCount = \count($matchesInIntro[0]);
        $text       = empty($row->text) ? '' : $row->text;
        preg_match_all($regex, $text, $matchesInText);
        $count = \count($matchesInText[0]);

        if (!$count && !$introCount) {
            return false;
        }

        $config = new PluginConfiguration($this->params);
        $this->addStyleSheetAndScripts($config);
        $screenres = $this->getScreenSizeFromCookie();
        $this->addScreenSizeCookie($config);
        $images = $this->$processImagesFunction($row, $articleId, $config, $matchesInIntro[0], $matchesInText[0], $screenres, (bool) $params->get('show_intro'));

        $row->introtext = $this->replaceSequentially($regex, $images->introImages, $row->introtext);

        if (!empty($row->text)) {
            $row->text = $this->replaceSequentially($regex, $images->images, $row->text);
        }

        return true;
    }

    /**
     * Ersetzt den n-ten Treffer von $regex durch $replacements[n]. Fehlende Ersetzungen werden zu ''.
     */
    private function replaceSequentially(string $regex, array $replacements, string $text): string
    {
        $replacements = array_values($replacements);
        $i            = 0;

        return preg_replace_callback(
            $regex,
            static function () use (&$i, $replacements) {
                return $replacements[$i++] ?? '';
            },
            $text
        );
    }

    private function isTextContainIntroText(object $row): bool
    {
        if (empty($row->text) || empty($row->introtext) || empty($row->fulltext)) {
            return false;
        }

        // im text ist der Intro nicht enthalten, daher beginnt die Anzeige der Bilder wieder bei 0
        return str_starts_with($row->text, $row->introtext);
    }

    private function addStyleSheetAndScripts(PluginConfiguration $config): void
    {
        $wa = $this->getApplication()->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('plg_content_mosimage');
        $wa->useStyle('plg_content_mosimage.mosimage');

        LightboxFactory::getInstance($config->getLightboxType())->addScriptAndCssToDocument($this->getApplication());
    }

    private function getScreenSizeFromCookie(): ?array
    {
        $value = $this->getApplication()->getInput()->cookie->getString('botmtscreenres', '');

        if (preg_match('/^(\d{2,5})x(\d{2,5})$/', $value, $matches)) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        return null;
    }

    private function addScreenSizeCookie(PluginConfiguration $config): void
    {
        if ($this->getScreenSizeFromCookie() !== null) {
            return;
        }

        if (str_contains((string) $config->getFullsizeMaxWidth(), '%') || str_contains((string) $config->getFullsizeMaxHeight(), '%')) {
            $this->getApplication()->getDocument()->getWebAssetManager()->addInlineScript(
                'document.cookie = "botmtscreenres=" + screen.width + "x" + screen.height + "; path=/; SameSite=Lax";',
                [],
                [],
                [],
                'plg_content_mosimage.screensize'
            );
        }
    }

    private function isNothingToDo(object $row): bool
    {
        $foundMosimageInText      = isset($row->text) && \is_string($row->text) && str_contains($row->text, 'mosimage');
        $foundMosimageInIntrotext = isset($row->introtext) && \is_string($row->introtext) && str_contains($row->introtext, 'mosimage');
        $foundClearFloating       = (isset($row->text) && \is_string($row->text) && str_contains($row->text, '{clear-floting}'))
            || (isset($row->introtext) && \is_string($row->introtext) && str_contains($row->introtext, '{clear-floting}'));

        return !$foundMosimageInText && !$foundMosimageInIntrotext && !$foundClearFloating;
    }

    public function getMosimageParameter(string $mosimage): string
    {
        return trim(preg_replace('/{mosimage\s*(.*)\s*}/', '$1', $mosimage));
    }

    private function getRepository(): ImageRepository
    {
        return $this->repository ??= new ImageRepository($this->getDatabase());
    }

    private function processImagesRandom(object $row, int $articleId, PluginConfiguration $config, array $matchesInIntro, array $matchesInText, ?array $screenres, bool $showIntro = false): \stdClass
    {
        $images      = [];
        $introImages = [];

        $article        = $this->getRepository()->getArticleTexts($articleId);
        $introCount     = $article ? preg_match_all(self::REGEX, $article->introtext ?? '') : 0;
        $fullCount      = $article ? preg_match_all(self::REGEX, $article->fulltext ?? '') : 0;
        $jsonObjectList = $this->getRepository()->getImages($articleId);
        $rowImageCount  = min(\count($jsonObjectList), max($introCount, $fullCount));

        if ($rowImageCount === 0) {
            $introImages[] = HtmlHelper::createHtmlForNoneIntroImage(0);
            $images[]      = HtmlHelper::createHtmlForNoneIntroImage(0);
        } else {
            $imageData        = $jsonObjectList[random_int(0, $rowImageCount - 1)];
            $imageData->align = '';
            $html             = HtmlHelper::createImageAndBuildHtml($imageData, $config, $screenres);
            $introImages[]    = $html;
            $images[]         = $html;
        }

        $result              = new \stdClass();
        $result->introImages = $introImages;
        $result->images      = $images;

        return $result;
    }

    private function processImagesDir(object $row, int $articleId, PluginConfiguration $config, array $matchesInIntro, array $matchesInText, ?array $screenres, bool $showIntro = false): \stdClass
    {
        $images      = [];
        $introImages = [];

        foreach ($matchesInIntro as $i => $match) {
            try {
                $imagePropertiesAsArrayObject = MosimageDirProperties::parse($match)->getImgagePropertiesAsArrayObject();

                if ($imagePropertiesAsArrayObject) {
                    $introImages[] = HtmlHelper::createImageAndBuildHtmlFromArrayObject($imagePropertiesAsArrayObject, $config, $screenres);
                } else {
                    $introImages[] = HtmlHelper::createHtmlForNoneIntroImage($i);
                }
            } catch (\Exception $e) {
                $introImages[] = '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        }

        foreach ($matchesInText as $i => $match) {
            try {
                $imagePropertiesAsArrayObject = MosimageDirProperties::parse($match)->getImgagePropertiesAsArrayObject();

                if ($imagePropertiesAsArrayObject) {
                    $images[] = HtmlHelper::createImageAndBuildHtmlFromArrayObject($imagePropertiesAsArrayObject, $config, $screenres);
                } else {
                    $images[] = HtmlHelper::createHtmlForNoneImage($i);
                }
            } catch (\Exception $e) {
                $images[] = '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        }

        $result              = new \stdClass();
        $result->introImages = $introImages;
        $result->images      = $images;

        return $result;
    }

    private function processImages(object $row, int $articleId, PluginConfiguration $config, array $matchesInIntro, array $matchesInText, ?array $screenres, bool $showIntro): \stdClass
    {
        $introCount = \count($matchesInIntro);
        $count      = \count($matchesInText);

        $jsonObjectsList = $this->getRepository()->getImages($articleId);
        $rowImageCount   = \count($jsonObjectsList);

        $introImages = [];

        for ($i = 0; $i < $introCount; $i++) {
            $param = $this->getMosimageParameter($matchesInIntro[$i]);

            if ($i < $rowImageCount) {
                $introImages[] = HtmlHelper::createImageAndBuildHtml($jsonObjectsList[$i], $config, $screenres, $param);
            } else {
                $introImages[] = HtmlHelper::createHtmlForNoneIntroImage($i);
            }
        }

        $start = $this->isTextContainIntroText($row) ? $introCount : 0;

        if (empty($row->fulltext) || $showIntro) {
            $start = 0;
        }

        $images = [];

        for ($i = $start, $m = 0; $m < $count; $i++, $m++) {
            $param = $this->getMosimageParameter($matchesInText[$m]);

            if ($i < $rowImageCount) {
                $images[] = HtmlHelper::createImageAndBuildHtml($jsonObjectsList[$i], $config, $screenres, $param);
            } else {
                $images[] = HtmlHelper::createHtmlForNoneImage($i);
            }
        }

        $result              = new \stdClass();
        $result->introImages = $introImages;
        $result->images      = $images;

        return $result;
    }
}

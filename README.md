# pkg_mosimage
Nachbau des mosimage-Features aus Joomla 1.0 für Joomla 6 (ab Version 2.0.0; Versionen bis 1.x laufen unter Joomla 3).

Das Paket enthält:
* `com_mosimage`: Pflege der einem Beitrag zugeordneten Bilder (Dialog im Editor) und Verwaltung des Bilder-Caches
* `plg_mosimage` (Inhalt - Mosimage): ersetzt `{mosimage}`-Platzhalter durch Vorschaubilder mit Lightbox
* `plg_mosimage-admin` (Editor-Button "Mosimage"): öffnet den Dialog zur Bildauswahl
* `plg_mosimage-viewmosimage` (Editor-Button "{mosimage}"): fügt den Platzhalter ein
* `mod_mosimage_random_image`: zeigt ein zufälliges Mosimage-Bild aus einer Kategorie

## Voraussetzungen
* Joomla 6.x, PHP >= 8.3 mit GD (EXIF optional für die automatische Drehung von Fotos)
* MySQL/MariaDB

## Joomla-Paket bauen

### Voraussetzungen
* U*x-artiges System mit bash, git, php (CLI) und zip
* (optional) Java, ant und ant-joomla: wird benötigt, wenn man das erstellte Paket per Kommandozeile installieren will.

### Paket bauen
Der Aufruf von

    bin/build.sh

baut das Paket. Die Version wird aus der Umgebungsvariable `VERSION` (bzw. `BUILD_NUMBER`) übernommen, z.B.

    VERSION=2.0.0 bin/build.sh

Das installationsfähige Joomla-Paket befindet sich im Ordner `./deploy`. Dieses Paket enthält alles Nötige inkl. der Sub-Packages.
Eingepackt werden nur Dateien, die von git verwaltet werden bzw. neu und nicht ignoriert sind.
Beim Bauen werden alle PHP-Dateien (`php -l`) und Sprachdateien (wie Joomla sie liest) geprüft.

Alternativ kann das Paket auf der Kommandozeile installiert werden:

    php cli/joomla.php extension:install --path=/pfad/zu/pkg_mosimage-2.0.0.zip

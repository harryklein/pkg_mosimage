# pkg_mosimage
Nachbau des mosimage-Features aus Joomla 1.0 für Jommla 3.5

# Joomla-Paket bauen

## Voraussetzungen
* U*x-artiges System mit bash, sed, zip
* (optional) Java >=1.7, ant und ant-jommla: wird benötigt wenn man das erstellte Paket per Kommandozeile installieren will.

## Paket bauen
Der Aufruf von 
    ant -f bin/build.xml
baut das Paket. Mit
    ant -f bin/build.xml deploy
kann man es auch lokal installieren.

Das installationsfähige Joomla-Paket befindet sich im Ordner ./deploy. Dieses Paket enthält alle nötiges inkl. der Sub-Packages.



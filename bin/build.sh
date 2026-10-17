#!/bin/bash
#
# Baut das Joomla-Paket pkg_mosimage inkl. aller Teil-Erweiterungen.
#
# Ergebnis: deploy/pkg_mosimage-VERSION.zip
#
# Es werden nur Dateien eingepackt, die von git verwaltet werden (bzw. neu und nicht ignoriert sind).
#

ROOT_DIR=$(dirname "$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")")
BUILD_DIR="${ROOT_DIR}/build"
DEPLOY_DIR="${ROOT_DIR}/deploy"

PACKAGE_NAME=pkg_mosimage
SUB_PACKAGES="com_mosimage plg_mosimage plg_mosimage-admin plg_mosimage-viewmosimage mod_mosimage_random_image"

# Dateien/Ordner der Teil-Erweiterungen, die nicht in die ZIP-Dateien gehören
EXCLUDE_PATTERN='^(bin|doc|build|deploy|report)/|(^|/)(\.gitignore|build\.properties)$'

function usage(){
	echo ""
	echo "Aufruf: $(basename "$0") [--help] [--build-version|--build|-b]"
	echo "Baut das Paket in [${ROOT_DIR}]"
	echo "   --build-version|--build|-b : Liefert die VERSIONS-Nummer des Builds"
	echo "   --help|-h                  : Diese Hilfe"
	echo ""
	echo "Die Version wird aus der Umgebungsvariable VERSION bzw. BUILD_NUMBER ermittelt, sonst HEAD."
	echo ""
}

function getVersionString(){
	if [ -n "${VERSION}" ]; then
		VERSION=$(echo "${VERSION}" | sed -e 's/-/./g')
	elif [ -n "${BUILD_NUMBER}" ]; then
		VERSION="build-${BUILD_NUMBER}"
	else
		VERSION="HEAD"
	fi
}

function fail(){
	echo "ERROR: $*"
	echo ""
	echo "BUILD FAILED"
	exit 1
}

#
# Listet alle Dateien eines Ordners, die von git verwaltet werden bzw. neu und nicht ignoriert sind.
#
# $1 Ordner relativ zu ROOT_DIR
#
function listFiles(){
	(cd "${ROOT_DIR}/$1" && git ls-files --cached --others --exclude-standard . | sort -u | while read -r f; do [ -f "$f" ] && echo "$f"; done)
}

#
# Setzt Version und Erstellungsdatum im Manifest
#
# $1 Manifest-Datei
#
function replaceParameterInManifest(){
	sed -i -e "s#<version>.*</version>#<version>${VERSION}</version>#" \
	       -e "s#<creationDate>.*</creationDate>#<creationDate>$(date '+%Y-%m-%d')</creationDate>#" "$1"
}

function checkPhpLint(){
	local DIR=$1
	local ERRORS=0
	while read -r f; do
		php -l "$f" > /dev/null 2>&1 || { php -l "$f"; ERRORS=$((ERRORS+1)); }
	done < <(find "${DIR}" -name '*.php')
	[ ${ERRORS} -eq 0 ] || fail "PHP-Syntaxfehler in [${DIR}]"
}

#
# Prüft die Sprachdateien so, wie Joomla sie liest (parse_ini_file im RAW-Modus)
#
function checkLanguageFiles(){
	local DIR=$1
	while read -r f; do
		php -r 'exit(@parse_ini_file($argv[1], false, INI_SCANNER_RAW) === false ? 1 : 0);' "$f" || fail "Fehler in Sprachdatei [$f]"
	done < <(find "${DIR}" -name '*.ini')
}

function buildSubPackage(){
	local NAME=$1
	local TARGET="${BUILD_DIR}/${NAME}"

	echo "* Baue [${NAME}]"
	mkdir -p "${TARGET}"
	(cd "${ROOT_DIR}/${NAME}" && listFiles "${NAME}" | grep -Ev "${EXCLUDE_PATTERN}" | xargs -d '\n' cp --parents -t "${TARGET}") \
		|| fail "Kann Dateien von [${NAME}] nicht kopieren"

	local MANIFEST
	MANIFEST=$(find "${TARGET}" -maxdepth 1 -name '*.xml')
	[ "$(echo "${MANIFEST}" | wc -w)" -eq 1 ] || fail "Manifest von [${NAME}] nicht eindeutig: [${MANIFEST}]"
	replaceParameterInManifest "${MANIFEST}"

	checkPhpLint "${TARGET}"
	checkLanguageFiles "${TARGET}"

	(cd "${TARGET}" && zip -qr "${BUILD_DIR}/package/${NAME}.zip" .) || fail "Kann [${NAME}.zip] nicht erzeugen"
}

function buildPackage(){
	local TARGET="${BUILD_DIR}/package"

	cp "${ROOT_DIR}/pkg_mosimage.xml" "${ROOT_DIR}/script.php" "${TARGET}" || fail "Kann Paket-Manifest nicht kopieren"
	(cd "${ROOT_DIR}" && listFiles language | sed 's#^#language/#' | xargs -d '\n' cp --parents -t "${TARGET}") \
		|| fail "Kann Sprachdateien nicht kopieren"
	replaceParameterInManifest "${TARGET}/pkg_mosimage.xml"
	checkPhpLint "${TARGET}"
	checkLanguageFiles "${TARGET}/language"

	(cd "${TARGET}" && zip -qr "${DEPLOY_DIR}/${PACKAGE_NAME}-${VERSION}.zip" .) || fail "Kann Paket nicht erzeugen"
}

case "$1" in
	-h|--help)
		usage
		exit 0
		;;
	-b|--build|--build-version)
		getVersionString
		echo "${VERSION}"
		exit 0
		;;
esac

getVersionString

command -v zip > /dev/null || fail "zip ist nicht installiert"
command -v php > /dev/null || fail "php ist nicht installiert"

rm -rf "${BUILD_DIR}" "${DEPLOY_DIR}"
mkdir -p "${BUILD_DIR}/package" "${DEPLOY_DIR}"

for SUB_PACKAGE in ${SUB_PACKAGES}; do
	buildSubPackage "${SUB_PACKAGE}"
done

buildPackage

echo ""
echo "SUCCESS: Datei ${DEPLOY_DIR}/${PACKAGE_NAME}-${VERSION}.zip wurde erzeugt."

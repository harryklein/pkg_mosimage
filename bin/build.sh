#!/bin/bash

ROOT_DIR=${WORKSPACE:=$PWD}
REPORT_DIR=${ROOT_DIR}/report
BUILD_DIR=${ROOT_DIR}/build

ERROR_COUNTER=0;

function usage(){
	echo ""
	echo "Aufruf: $(basename $0) [--help] [--build-version|--build|-b] [--ignore-lang-error|-l] [--fast|-f]"
	echo "Baut das Modul/Komponente in [${ROOT_DIR}]"
	echo "   --build-version|--build|-b : Liefert die VERSIONS-Nummer des Builds"
	echo "   --ignore-lang-error|-l     : Fehler in den Sprachfiles führen nicht zu einem"	
	echo "                                Abbruch."   
	echo "   --fast|-f                  : Keine Sprachfike-und Lint-Checks"                     
	echo "   --help|-h                  : Diese Hilfe"
	
	echo ""
	echo "Es werden folgende Skripte zusätzlich benutzt:"
	for i in bin/*.sh
	do
	  local NAME=$(basename $i)
	  if [ "${NAME}" == "build.sh" ]
	  then
	    :
	  else 
	    echo "* ${NAME}"
	  fi
	done
}

function debug(){
  if [ "$DEBUG" == "1" ]
  then
    echo "DEBUG: $@"
  fi
}

function getVersionString(){
  if [ "${VERSION}" == "" ]
  then
    if [ "${BUILD_NUMBER}" == "" ]
    then
	    VERSION="HEAD"
	else
	    VERSION="build-${BUILD_NUMBER}"
	fi
  else
  	VERSION=$(echo "${VERSION}" | sed -e s/'-'/'\.'/g)
  fi
}

function getComamdLineParameter(){
  while true
  do
    case "${1}" in
            -h|--help)
                    usage
                    exit
                    ;;
            --build|-b)
                    getVersionString
                    echo $VERSION
                    exit
                    ;;
            --ignore-lang-error|-l)
                    IGNORE_LANG_ERROR=1
                    ;;
            --fast|-f)
                    FAST=1
                    ;;
            '')
                  break
                  ;;
    esac
    shift 1
  done
}

function check_exit_code(){
	EXIT_CODE=$1
	shift 1
	MSG=$@
	
	if [ $EXIT_CODE -ne 0 ]	
	then
		echo "ERROR: $MSG."
		echo ""
		echo "BUILD FAILED"
		echo ""
		exit 1
	fi
}


function loadConfiguration(){
  local CONF_FILE="${ROOT_DIR}/build.properties"
  if [ -f "${CONF_FILE}" -a -r  "${CONF_FILE}" ]
  then
    echo "INFO: Lade Konfiguration [${CONF_FILE}]"
    . "${ROOT_DIR}/build.properties"
    if [[ $(echo ${ZIP_FILE_NAME}) == "" ]]
    then
      check_exit_code 1 "ZIP_FILE_NAME ist nicht in [${CONF_FILE}] gesetzt oder leer"
    fi
  else
    check_exit_code 1 "Kann Konfiguration [${CONF_FILE}] nicht laden"
  fi

}

function createBuildDirectory(){
    if [ -d "${BUILD_DIR}" -o -f "${BUILD_DIR}" ]
    then
	    echo "INFO: Lösche Build-Verzeichnis [${BUILD_DIR}]."
	    rm -rf "${BUILD_DIR}"
    fi
    echo "INFO: Lege Verzeichnis [${BUILD_DIR}] an."
    mkdir -p "${BUILD_DIR}"
}

function createReportDirectory(){
    if [ -d "${REPORT_DIR}" -o -f "${REPORT_DIR}" ]
    then
            echo "INFO: Lösche Build-Verzeichnis [${REPORT_DIR}]."
            rm -rf "${REPORT_DIR}"
    fi
    echo "INFO: Lege Verzeichnis [${REPORT_DIR}] an."
    mkdir -p "${REPORT_DIR}"
}


function copyAllFileToBuildDirectory(){
  echo "INFO: kopiere Filelelist nach [${BUILD_DIR}]"
  cp  "${ROOT_DIR}/bin/filelist.txt" "${BUILD_DIR}"
  check_exit_code $? "Kann [${ROOT_DIR}/bin/filelist.txt] nicht nach [${BUILD_DIR}] kopieren"

  local FILES=$(cat "${ROOT_DIR}/bin/filelist.txt")
  cp  --parent $FILES "${BUILD_DIR}"
  check_exit_code $? "Kann Dateien aus [${ROOT_DIR}/bin/filelist.txt] nicht nach [${BUILD_DIR}] kopieren"
}


function replaceParameterInConfigFile(){
  local CONFIG_FILE=$(ls *.xml)
  if [ $(echo ${CONFIG_FILE} | wc -w) -ne 1 ]
  then
    check_exit_code 1 "Kann Konfigurationsdatei nicht ermmiteln: Gefunden wurde [$CONFIG_FILE]"
  fi
  sed -e s#'<version>.*</version>'#"<version>${VERSION}</version>"#g ${CONFIG_FILE}|\
  sed -e s#'<creationDate>.*</creationDate>'#"<creationDate>$(date "+%d %b %Y")</creationDate>"#g > ${CONFIG_FILE}.tmp
  mv ${CONFIG_FILE}.tmp ${CONFIG_FILE}
}

function buildArtefakct(){

  echo "* Baue ZIP [${ZIP_FILE_NAME}-${VERSION}] in [$(pwd)]"
  cat filelist.txt | zip "${ROOT_DIR}/deploy/${ZIP_FILE_NAME}-${VERSION}.zip" '-@'
  check_exit_code $? "Datei $ROOT_DIR/deploy/${ZIP_FILE_NAME}-${VERSION}.zip konnte nicht erzeugt werden."

  echo ""
  echo "SUCCESS: Datei ${ROOT_DIR}/deploy/${ZIP_FILE_NAME}-${VERSION}.zip wurde erzeugt."
}

function changeIntoWorkingDirectory(){
  cd "$ROOT_DIR"
  check_exit_code $? "ERROR: Kann nicht in das Verzeichnis [${ROOT_DIR}] wechseln. Abbruch"
}

function checkFilelistFile(){
  if [ ! -f "${ROOT_DIR}"/bin/filelist.txt ]
  then
    echo "ERROR: Kann Datei [${ROOT_DIR}/filelist.txt] nicht finden. Abbruch"
    exit 1
  fi
}


function createDepoloyDirectory(){
  echo "* INFO: Lösche Deploy-Ordner [${ROOT_DIR}/deploy]"
  rm -rf "${ROOT_DIR}/deploy"
  check_exit_code $? "Kann Verzeichnis [${ROOT_DIR}/deploy] nicht löschen"

  if [ ! -d deploy ]
  then
          mkdir deploy
          check_exit_code $? "Kann Verzeichnis [${ROOT_DIR}/deploy] nicht anlegen"
  fi
}


function changeIntoBuildDirectory(){
  cd "${BUILD_DIR}"
  check_exit_code $? "Kann nicht nach {${BUILD_DIR}] wechseln"
}

#
# Versucht, die Datei bin/$1 zu laden und auszuführen.
# Exsistiert die Datei nicht, so passiert schlicht nichts.
#
# @param $1 Name der Datei, die geladen und ausgeführt werden soll
#
function loadAndExecuteOtherScript(){
  local FILE_NAME="${ROOT_DIR}/bin/${1}"
  if [ -f "${FILE_NAME}" -a -r "${FILE_NAME}" ]
  then
    echo "* Laden Datei [${FILE_NAME}] und führe sie aus."
    shift 1
    . "${FILE_NAME}"	
  fi
}

getComamdLineParameter "$@"

loadConfiguration
getVersionString
changeIntoWorkingDirectory

checkFilelistFile

loadAndExecuteOtherScript build-subpackage.sh  "$@"

loadAndExecuteOtherScript checkUnusedProperties "de-DE"

createDepoloyDirectory
createBuildDirectory
copyAllFileToBuildDirectory

changeIntoBuildDirectory
replaceParameterInConfigFile

createReportDirectory
loadAndExecuteOtherScript build-phplint.sh  "$@"

buildArtefakct

loadAndExecuteOtherScript checkAllLanguageFiles "$@"

loadAndExecuteOtherScript build-ext.sh  "$@"

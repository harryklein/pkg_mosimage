#!/bin/bash

ROOT_DIR=$(dirname $(dirname $(readlink -f ${BASH_SOURCE[0]})))
REPORT_DIR=${ROOT_DIR}/report
BUILD_DIR=${ROOT_DIR}/build

>&2 echo "ROOT_DIR ....: [${ROOT_DIR}]"
>&2 echo "REPORT_DIR ..: [${REPORT_DIR}]"
>&2 echo "BUILD_DIR  ..: [${BUILD_DIR}]"

ERROR_COUNTER=0;

function usage(){
	echo ""
	echo "Aufruf: $(basename $0) [--help] [--build-version|--build|-b] [--ignore-lang-error|-l] [--fast|-f]"
	echo "Baut das Modul/Komponente in [${ROOT_DIR}]"
	echo "   --build-version|--build|-b : Liefert die VERSIONS-Nummer des Builds"
	echo "   --ignore-lang-error|-l     : Fehler in den Sprachfiles führen nicht zu einem"	
	echo "                                Abbruch."   
	echo "   --fast|-f                  : Keine Sprachfike-und Lint-Checks"   
	echo "   --check|-c                 : Überprüft die Files in bin/ auf Veränderungen"                  
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

#
# Ausgabe von Meldungen. Je nach Level wirde unterschiedliche Zeichen und Einrückungen ausgegeben
# $1 : Level 1-3
# $2 : printf-Format
# $3-n : Parameter für das printf-Format
#
function trace(){
  local FORMAT=""
  local VALUE=""
  case "${1}" in
    1)
	FORMAT='%1s '
	VALUE='*'
	;;
    2)
	FORMAT='%3s '
	VALUE='-'
	;;
	
    3) 
	FORMAT='%5s '
	VALUE='>'
	;;
	
  esac
  printf "${FORMAT}" "${VALUE}"
  shift 1
  printf "$@" 
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
            --check|-c)
            		CHECK_BIN_FILES=1
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
  
  if [ -z "${BUILD_FILE_MASTER}" ]
  then
  	BUILD_FILE_MASTER=../joomla-build
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
  changeIntoBuildDirectory
  
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
    trace 1 "Laden Datei [${FILE_NAME}] und führe sie aus.\n"
    shift 1
    . "${FILE_NAME}"	
  fi
}

function checkAllBinFiles(){
	if [ "${CHECK_BIN_FILES}" != "1" ]
	then
		return
	fi
	trace 1 "Prüfe alle Build-Files auf Veränderung. Der Master liegt in [${BUILD_FILE_MASTER}]\n"
	
	[ -d ${BUILD_FILE_MASTER} ]
	check_exit_code $? "[${BUILD_FILE_MASTER}] ist kein Verzeichnis. Abbruch"
	
	local FILES=$(find bin -maxdepth 1 -type f | grep -v filelist.txt | grep -v '^bin/\.')
	local RESULT
	local i
	for i in $FILES
	do
		if [ -f ${BUILD_FILE_MASTER}/$i ]
		then
			trace 2 "%-60s" "$i"
			diff -q $i ${BUILD_FILE_MASTER}/$i
			RESULT=$?
			if [ ${RESULT} -ne 0 ]
			then
				ERROR_COUNTER=$((ERROR_COUNTER+1))
				diff -u $i ${BUILD_FILE_MASTER}/$i
			else
				echo "ok"
			fi
		fi
	done
	
	check_exit_code ${ERROR_COUNTER} "Nicht alle Buildfiles sind gleich. Abbruch."


}

getComamdLineParameter "$@"

loadConfiguration
getVersionString
changeIntoWorkingDirectory

checkAllBinFiles

checkFilelistFile

loadAndExecuteOtherScript build-subpackage.sh  "$@"

loadAndExecuteOtherScript checkUnusedProperties "de-DE"

createDepoloyDirectory
createBuildDirectory
createReportDirectory

copyAllFileToBuildDirectory

changeIntoBuildDirectory
replaceParameterInConfigFile

loadAndExecuteOtherScript replaceCopyrightInfo "$@"

loadAndExecuteOtherScript checkPhpLint  "$@"

changeIntoBuildDirectory
buildArtefakct

loadAndExecuteOtherScript checkAllLanguageFiles "$@"

loadAndExecuteOtherScript build-ext.sh  "$@"

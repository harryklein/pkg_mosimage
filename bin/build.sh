#!/bin/bash

ROOT_DIR=${WORKSPACE:=$PWD}

function usage(){
	echo ""
	echo "Aufruf: $(basename $0) [--help] [--build-version|--build|-b] [--ignore-lang-error|-l] [--fast|-f]"
	echo "Baut das Modul/Komponente in [${ROOT_DIR}]"
	echo "   --build-version|--build|-b : Liefert die VERSIONS-Nummer des Builds"
	echo "   --ignore-lang-error|-l     : Fehler in den Sprachfiles führen nicht zu einem"	
	echo "                                Abbruch."   
	echo "   --fast|-f                  : Keine Sprachfike-und Lint-Checks"                     
	echo "   --help|-h                  : Diese Hilfe"
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
    BUILD_DIR=${ROOT_DIR}/build
    if [ -d "${BUILD_DIR}" -o -f "${BUILD_DIR}" ]
    then
	    echo "INFO: Lösche Build-Verzeichnis [${BUILD_DIR}]."
	    rm -rf "${BUILD_DIR}"
    fi
    echo "INFO: Lege Verzeichnis [${BUILD_DIR}] an."
    mkdir -p "${BUILD_DIR}"
}

function createReportDirectory(){
    REPORT_DIR=${ROOT_DIR}/report
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

  FILES=$(cat "${ROOT_DIR}/bin/filelist.txt")
  cp  --parent $FILES "${BUILD_DIR}"
  check_exit_code $? "Kann Dateien aus [${ROOT_DIR}/bin/filelist.txt] nicht nach [${BUILD_DIR}] kopieren"
}


function replaceParameterInConfigFile(){
  CONFIG_FILE=$(ls *.xml)
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
  echo "INFO: Lösche Deploy-Ordner [${ROOT_DIR}/deploy]"
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

function phpLint(){
  echo "* Prüfen der PHP-Dateien auf Syntaktische Fehler"
  if [ "${FAST}" == "1" ]
  then
  	echo "  - Prüfung übersprungen, da Programmschalter --fast|-f angegeben worden ist."
  	return
  fi
  local FOUND_ERROR=0
  local PHP_FILES=$(find . -name "*.php")
  local i
  for i in ${PHP_FILES}
  do
    printf "  - %-60s " ${i}
    php5 -l $i 2>> "${REPORT_DIR}/php-lint.log" >/dev/null
    if [ $? -ne 0 ]
    then
      echo "error"
      FOUND_ERROR=1
    else
      echo "ok"
    fi
  done 
  check_exit_code ${FOUND_ERROR} "Syntaktischen Fehler gefunden. Details siehe [${REPORT_DIR}/php-lint.log]"
}

ERROR_COUNTER=0;

function checkUnusedProperties(){
  if [ "${FAST}" == "1" ]
  then
  	echo "Ignore check unused properties"
  	return
  fi
	
 if [ -z "${LANG_FILE_NAME}" ]
  then
  	local FILE=${ZIP_FILE_NAME}
  else
    local FILE=${LANG_FILE_NAME}
  fi
  local LANGUAGE_1=${1}
  local EXTENTION=${2}
  if [ -n "${EXTENTION}" ]
  then
    EXTENTION=".${EXTENTION}"
  fi
  
  if [ -z "${LANG_FILE_DIRECTORY}" ]
  then
	LANG_FILE_DIRECTORY="."
  fi
  echo "LANG_FILE_DIRECTORY [${LANG_FILE_DIRECTORY}]"
   
  for dir in ${LANG_FILE_DIRECTORY}
  do
    FILE_1=${dir}/language/${LANGUAGE_1}/${LANGUAGE_1}.${FILE}${EXTENTION}.ini
    echo "Prüfe, ob alle Labels der Datei [${FILE_1}] im Code (php, xml) benutzt werden"
    local SRC="$(find ${dir} -name "*.php" -or -name "*.xml")"
    
    if [ "${dir}" == "admin" ]
    then
      SRC="${SRC}  $(find . -maxdepth 1 -name "*.xml")";
    fi

    local LABELS=$(cat ${FILE_1} | grep v '^#'| grep '=' | grep -v ';' |  cut -d '=' -f 1)
    local i
    for i in $LABELS
    do
      debug "Label [$i]"
      local FOUND=0
      for f in $SRC
      do
        grep -q ${i} ${f}
        if [ $? -eq 0 ]
  	then
          FOUND=1
          break
        fi 
      done
      if [ $FOUND -eq 0 ]
      then
        echo "NOT FOUND [$FILE_1] [$i]"
      fi
    done
  done

}

# ############################################################################
#                                                                            #
# Prüft, ob die Sprachdateien konsitent sind. D.h. ob die Schlüssel der      #
# einer Sprachdatei $1 auch in der Sprachdatei $2 enthalten ist              #
#                                                                            #
#  {LANG_FILE_DIRECTORY}/language/${1}/${1}.${FILE}${EXTENTION}.ini          #
#  {LANG_FILE_DIRECTORY}/language/${2}/${2}.${FILE}${EXTENTION}.ini          #
#                                                                            #
# @param $1 Sprache der Quelldatei                                           #
# @param $2 Sprache, die überprüft werden soll                               #
# @param $3 Erweiterug: "" oder sys                                          #
# Enviroment:                                                                #
#    $FAST                                                                   #
#    LANG_FILE_NAME                                                          #
#    LANG_FILE_DIRECTORY                                                     #
#    ZIP_FILE_NAME                                                           #
# ############################################################################
function checkLanguageFile(){
  if [ "${FAST}" == "1" ]
  then
  	echo "Ignore check language file"
  	return
  fi
  
  if [ -z "${LANG_FILE_NAME}" ]
  then
    local FILE=${ZIP_FILE_NAME}
  else
    local FILE=${LANG_FILE_NAME}
  fi
  local LANGUAGE_1=${1}
  local LANGUAGE_2=${2}
  local EXTENTION=${3}
  if [ -n "${EXTENTION}" ]
  then
    EXTENTION=".${EXTENTION}"
  fi
  
  if [ -z "${LANG_FILE_DIRECTORY}" ]
  then
	LANG_FILE_DIRECTORY="."
  fi 
  for dir in ${LANG_FILE_DIRECTORY}
  do 
    FILE_1=${dir}/language/${LANGUAGE_1}/${LANGUAGE_1}.${FILE}${EXTENTION}.ini
    FILE_2=${dir}/language/${LANGUAGE_2}/${LANGUAGE_2}.${FILE}${EXTENTION}.ini
  
    if [ ! -r ${FILE_1} -o ! -r ${FILE_2} ]
    then
      check_exit_code 1 "Datei [${FILE_1}] oder [${FILE_2}] wurde nicht gefunden"
    fi
    echo "  - [${FILE_2}] mit den Labels aus [${FILE_1}] prüfen"
    local LABELS=$(cat ${FILE_1} | grep -v '^#' | grep '=' | grep -v ';' | grep '^[A-Z_][A-Z_][A-Z_]' | cut -d '=' -f 1)
    local i
    for i in $LABELS
    do
      grep -q "^${i}=" ${FILE_1}
      if [ $? -ne 0 ]
      then
        echo "Not found label [${i}] in [${FILE_1}]" 
        ERROR_COUNTER=$((ERROR_COUNTER+1))
      else
        grep -q "^${i}=" ${FILE_2}
        if [ $? -ne 0 ]
        then
          printf "Not found label %-40s in %s \n" "[${i}]" "[${FILE_2}]"
          ERROR_COUNTER=$((ERROR_COUNTER+1))
        fi
      fi
    done
  done
}

function checkAllLanguageFiles(){
  echo "* Prüfe die Sprachfiles auf Konsistenz."
  local LANGUAGE_1="en-GB"
  local LANGUAGE_2="de-DE"
  checkLanguageFile ${LANGUAGE_1} ${LANGUAGE_2}
  checkLanguageFile ${LANGUAGE_2} ${LANGUAGE_1}
  checkLanguageFile ${LANGUAGE_1} ${LANGUAGE_2} "sys"
  checkLanguageFile ${LANGUAGE_2} ${LANGUAGE_1} "sys"
  if [ "${IGNORE_LANG_ERROR}" != "1" ]
  then
  	check_exit_code ${ERROR_COUNTER} "Sparchfiles sind nicht konsitent. Es wurden [${ERROR_COUNTER}] Fehler gefunden."
  fi
}

getComamdLineParameter "$@"

loadConfiguration
getVersionString
changeIntoWorkingDirectory
checkFilelistFile

checkUnusedProperties "de-DE"

createDepoloyDirectory
createBuildDirectory
copyAllFileToBuildDirectory


changeIntoBuildDirectory
replaceParameterInConfigFile

createReportDirectory
phpLint

buildArtefakct

checkAllLanguageFiles

if [ -f "${ROOT_DIR}/bin/build-ext.sh" -a -x "${ROOT_DIR}/bin/build-ext.sh" ]
then
  . "${ROOT_DIR}/bin/build-ext.sh" "$@"

fi 

#!/bin/bash

ROOT_DIR=${WORKSPACE:=$PWD}

function usage(){
	echo ""
	echo "Aufruf: $(basename $0) [--help]"
	echo "Baut das Modul/Komponente in [${ROOT_DIR}]"
	echo "   --build-version -b liefert die VERSIONS-Nummer des Builds"
	echo "   --help|-h  Diese Hilfe"
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
esac

echo "WORKSPACE: [$WORKSPACE]"


function check_exit_code(){
	EXIT_CODE=$1
	shift 1
	MSG=$@
	
	if [ $EXIT_CODE -ne 0 ]	
	then
		echo "ERROR: $MSG. ABBRUCH"
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

  echo "INFO: Baue ZIP [${ZIP_FILE_NAME}-${VERSION}] in [$(pwd)]"
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

function copyArtefactToSrc(){
  local SRC_FILE="${ROOT_DIR}/../$item/deploy/${1}-${VERSION}.zip"
  local DEST_FILE="${1}.zip"
  echo "* Kopiere ${1}-${VERSION}.zip nach ${DEST_FILE}"  
  cp  ${SRC_FILE} ${DEST_FILE}
  check_exit_code $? "Kann [${SRC_FILE}] nicht nach [${DEST_FILE}] kopieren"  
}


buildAllSubPackage(){
  echo "ITEMS: $ITEMS"
  local $item;
  for item in $ITEMS
  do
    (
      printf "==========================================\n"
      printf "= Baue SubPackage %-22s =\n" "[$item]"
      printf "==========================================\n"
      SUBPACKAGE_DIR="${ROOT_DIR}/../$item"
      cd ${SUBPACKAGE_DIR}
      check_exit_code $? "ERROR: Kann nicht in das Verzeichnis [${ROOT_DIR}] wechseln. Abbruch"
      ./bin/build.sh
      
      RESULT=$?
      exit $RESULT
    )
    check_exit_code $? "Fehler beim Erstellen des Sub-Package [${item}]"
    
    copyArtefactToSrc ${item}
    
done
}



loadConfiguration
getVersionString
changeIntoWorkingDirectory

checkFilelistFile

buildAllSubPackage



createDepoloyDirectory
createBuildDirectory
copyAllFileToBuildDirectory


changeIntoBuildDirectory
replaceParameterInConfigFile

#createReportDirectory
#phpLint

buildArtefakct

if [ -f "${ROOT_DIR}/bin/build-ext.sh" -a -x "${ROOT_DIR}/bin/build-ext.sh" ]
then
  . "${ROOT_DIR}/bin/build-ext.sh"	

fi 

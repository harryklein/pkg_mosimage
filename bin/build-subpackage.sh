#!/bin/bash

function copyArtefactToSrc(){
  local SRC_FILE="${ROOT_DIR}/../$item/deploy/${1}-${VERSION}.zip"
  local DEST_FILE="${1}.zip"
  echo "* Kopiere ${1}-${VERSION}.zip nach ${DEST_FILE}"  
  cp  ${SRC_FILE} ${DEST_FILE}
  check_exit_code $? "Kann [${SRC_FILE}] nicht nach [${DEST_FILE}] kopieren"  
}

#
# Ermittelt alle zu bauenden Sub-Package aus der Datei bin/filelist.txt
#
# @return: Liste mit allen zu bauenden Sub-Package
#
getAllNamesOfSubPackage(){
	grep '\.zip$' "${ROOT_DIR}"/bin/filelist.txt | sed -e s/'\.zip$'/''/g
} 

buildAllSubPackage(){
  local ITEMS=$(getAllNamesOfSubPackage)
  local item;
  for item in $ITEMS
  do
    (
      printf "==========================================\n"
      printf "= Baue SubPackage %-22s =\n" "[$item]"
      printf "==========================================\n"
      local SUBPACKAGE_DIR="${ROOT_DIR}/../$item"
      cd ${SUBPACKAGE_DIR}
      check_exit_code $? "ERROR: Kann nicht in das Verzeichnis [${SUBPACKAGE_DIR}] wechseln. Abbruch"
      ./bin/build.sh
      
      local RESULT=$?
      exit $RESULT
    )
    check_exit_code $? "Fehler beim Erstellen des Sub-Package [${item}]"

    copyArtefactToSrc ${item}
  done
}

changeIntoWorkingDirectory
buildAllSubPackage

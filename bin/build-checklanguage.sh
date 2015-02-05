#
# $Id: build-checklanguage.sh,v 1.1 2015/02/05 20:20:17 harry Exp $
#


#
# Prüft, ob die Sprachdateien konsitent sind. D.h. ob die Schlüssel der
# einer Sprachdatei $1 auch in der Sprachdatei $2 enthalten ist
#
#  {LANG_FILE_DIRECTORY}/language/${1}/${1}.${FILE}${EXTENTION}.ini
#  {LANG_FILE_DIRECTORY}/language/${2}/${2}.${FILE}${EXTENTION}.ini
#
# @param $1 Sprache der Quelldatei
# @param $2 Sprache, die überprüft werden soll
# @param $3 Erweiterug: "" oder sys
# Enviroment:
#    $FAST
#    LANG_FILE_NAME
#    LANG_FILE_DIRECTORY
#    ZIP_FILE_NAME
#
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

changeIntoWorkingDirectory
checkAllLanguageFiles
#
# $Id: build-checkUnusedProperties.sh,v 1.1 2015/02/05 22:25:26 harry Exp $
#

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

changeIntoWorkingDirectory
checkUnusedProperties "$@"
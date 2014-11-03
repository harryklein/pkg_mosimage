function getComamdLineParameterExt(){
  while true
  do
    case "${1}" in
            --fix-lang-file|-x)
                    FIX_LANG_FILE=1
                    ;;
            '')
                  break
                  ;;
    esac
    shift 1
  done
}



function getLanguageFileName(){
  case "$2" in
    sys)
      echo "admin/language/${1}/${1}.com_mosimage.sys.ini"
      ;;
    *)
      echo "admin/language/${1}/${1}.com_mosimage.ini"
      ;;
  esac
}

function compareLanguageFilesAndAdaptThisFiles(){
  local LANG_TEMPLATE_FILE=$(getLanguageFileName "de-DE")
  local LANG_OTHER_FILE=$(getLanguageFileName "en-GB")

  local LANG_TMP=${LANG_OTHER_FILE}.tmp
  rm -f ${LANG_TMP}

  local LABEL=$(cat ${LANG_TEMPLATE_FILE} | grep -v '#' | cut -d '=' -f 1)
  # Die Sprach-Datei de_DE zeilenweise durchgegen:
  # Bei Label Inhalt aus dem en_GB-File nehmen bzw. Dummy-Eintrag erzeugen,
  # alle anderen Zeilen übernehmen
  # 
  local LINE
  while read LINE
  do 
    echo $LINE | grep -q '^[A-Z_0-9]*='
    if [ $? -eq 0 ] 
    then
      # Zeile enthält Label
      local LABEL=$(echo $LINE | cut -d '=' -f 1 )
      
      cat ${LANG_OTHER_FILE} | grep "^${LABEL}=" >> ${LANG_TMP}
      if [ $? -ne 0 ]
      then
        printf "%-10s:%s\n"  "+ label" "${LABEL}"
        echo "${LABEL}=\"\"" >> ${LANG_TMP}
      else
        printf "%-10s:%s\n" "Found label" "${LABEL}"
      fi
    else
      
      echo $LINE  | grep -q '^# $Id:'
      if [ $? -eq 0 ]
      then
        echo "+ID"
        grep '^# $Id:' ${LANG_OTHER_FILE} >> ${LANG_TMP}
      else
        printf "%-10s:%s\n" "+ line" "${LINE}"
        echo $LINE >> ${LANG_TMP}
      fi
    fi
  done < ${LANG_TEMPLATE_FILE}

  diff ${LANG_TMP} ${LANG_OTHER_FILE}
  if [ $? -ne 0 ]
  then
    pwd
    local SUFFIX=$(date "+%Y-%m-%d-%H-%M-%S")
    mv -v ../${LANG_OTHER_FILE} ../${LANG_OTHER_FILE}_${SUFFIX}
    check_exit_code $? "Kann Datei [{LANG_OTHER_FILE}] nicht als  [${LANG_OTHER_FILE}_${SUFFIX}] sichern."
    mv -v ${LANG_TMP} ../${LANG_OTHER_FILE}
    check_exit_code $? "Kann Datei [{LANG_TMP}] nicht nach  [${LANG_OTHER_FILE}] verschieben."
  fi
}

getComamdLineParameterExt "$@"

if [ "$FIX_LANG_FILE" == "1" ]
then
  compareLanguageFilesAndAdaptThisFiles
fi


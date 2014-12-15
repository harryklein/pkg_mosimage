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

#
# Temporärer Dateien löschen.
#
function trapDeleteFiles(){
  echo "* Löschen der temporären Datein [${ORIG_FILE} ${OTHER_FILE}]"
  if [ "${ORIG_FILE}" != "" ]
  then
    echo "  - ${ORIG_FILE}"
    rm -f "${ORIG_FILE}"
  fi

  if [ "${OTHER_FILE}" != "" ]
  then
    echo "  - ${OTHER_FILE}"
    rm -f "${OTHER_FILE}"
  fi
  exit 0
}

#
# Überprüft die Header der einzelnen Dateien. Bei Fehlern erfolgt
# # # ein Abbruch. Aus Vorlage werden die ersten 10 Zeilen der ersten Datei verwendet.
#
function checkPhpHeader(){
  echo "* Prüfe Header in den PHP-Files auf Konstenz .. "
  cd ..
  local f_admin=$(find admin -name "*.php")
  local f_site=$(find admin -name "*.php")  

  trap 'trapDeleteFiles' EXIT  
  trap 'trapDeleteFiles' 1 2 3 15  
  ORIG_FILE=$(mktemp)
  OTHER_FILE=$(mktemp)
  
  local ERROR=0
  local FIRST=1

  for i in $f_admin $f_site
  do
    if [ $FIRST -eq 1 ]
    then
      cat $i | sed -e s/'\$Id: .*\$'/'\$Id: \$'/g |\
        grep -v 'www.sonerekici.com' |\
        grep -v 'Open Source Matters. All rights reserved.' |\
        sed -e s/' 20[0-9][0-9]-'/' 20xx-'/g |\
        head -10 > ${ORIG_FILE}
      FIRST=0
      echo "  - Vorlage für Header-Konsistenzprügung ist [${i}]"
      echo "    ==== Inhalt - Beginn ===="
      pr -T --indent=6 --page-width=160 ${ORIG_FILE}
      echo "    ==== Inhalt - Ende ======"
      continue
    fi
    printf "  - %-60s " $i
    cat $i | sed -e s/'\$Id: .*\$'/'\$Id: \$'/g |\
        grep -v 'www.sonerekici.com' |\
        grep -v 'Open Source Matters. All rights reserved.' |\
        sed -e s/' 20[0-9][0-9]-'/' 20xx-'/g |\
        head -10 > ${OTHER_FILE}
    diff ${ORIG_FILE} ${OTHER_FILE}
    if [ $? -ne 0 ] 
    then
      echo "error"
      ERROR=$((ERROR+1))
    else
      echo "ok"
    fi
  done
  if [ $ERROR -ne 0 ]
  then
    echo "Die Header in PHP-Dateien weicht in $ERROR Fällen vom erwarteten Inhalt ab."
  fi

}


getComamdLineParameterExt "$@"

if [ "$FIX_LANG_FILE" == "1" ]
then
  compareLanguageFilesAndAdaptThisFiles
fi

checkPhpHeader

exit 0
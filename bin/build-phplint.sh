#
# $Id: build-phplint.sh,v 1.1 2015/02/05 20:20:18 harry Exp $
#

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

changeIntoWorkingDirectory
phpLint

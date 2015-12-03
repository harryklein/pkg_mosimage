#!/bin/bash
ROOT_DIR=$(dirname $(dirname $(readlink -f ${BASH_SOURCE[0]})))
>&2 echo "ROOT_DIR ....: [${ROOT_DIR}]"

cd ${ROOT_DIR}

(./bin/build.sh && ./bin/ant.sh  deploy ) ; date

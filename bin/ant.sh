#!/bin/bash

ROOT_DIR=$(dirname $(dirname $(readlink -f ${BASH_SOURCE[0]})))
>&2 echo "ROOT_DIR ....: [${ROOT_DIR}]"

cd ${ROOT_DIR}

export VERSION=$(./bin/build.sh -b)
ant -lib ${HOME}/ant-joomla/lib -DVERSION=${VERSION} -f bin/build.xml $@

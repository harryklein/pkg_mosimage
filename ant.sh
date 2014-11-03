#!/bin/bash
export VERSION=$(./bin/build.sh -b)
ant -lib ${HOME}/ant-joomla/lib -DVERSION=${VERSION} $@

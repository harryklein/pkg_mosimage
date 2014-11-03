#!/bin/bash


ROOT_DIR="/home/harald/project/joomla-1.5/com_mosimage"
DEPLOY_DIR="/home/harald/project/joomla-1.5/com_mosimage/deploy-dist"
XML=mosimage
ZIP=com_mosimage

function listOfFilesInDeployDist(){
	echo "-------------------------------------------------------------------------------------"
	echo "- List of file in [${DEPLOY_DIR}]"
	echo "-------------------------------------------------------------------------------------"       
	ls -l ${DEPLOY_DIR}
}

function my_exit(){
	echo $@
	exit 1
}

listOfFilesInDeployDist

cd ${DEPLOY_DIR} || my_exit "ERROR: Kann nicht in das Verzeichnis [${DEPLOY_DIR}] wechseln"

FILE_LIST=$(ls com_mosimage-v*.zip  plugin_mosimage-v*.zip plugin_mosimage-admin-v*.zip)

zip ../deploy/mosimage-erst-entpacken ${FILE_LIST} || my_exit "ERROR: Kann Zip [../deploy/mosimage-erst-entpacken.zip] nicht erstellen "





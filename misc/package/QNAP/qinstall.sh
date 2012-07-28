#!/bin/sh
#================================================================
# Copyright (C) 2010 QNAP Systems, Inc.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#----------------------------------------------------------------
#
# qinstall.sh
#
#	Abstract: 
#		A QPKG installation script for
#		Piwik v1.0
#
#	HISTORY:
#		2008/03/26	-	Created	- KenChen 
#		2010/11/05 -	Modified - AndyChuo (zeonism at gmail dot com) 
#		2012/07/28 -	Modified - Anthon Pang (anthon at piwik dot org)
# 
#================================================================

##### Util #####
CMD_APACHE="/usr/local/apache/bin/apachectl"
CMD_CP="/bin/cp"
CMD_CUT="/bin/cut"
CMD_CHMOD="/bin/chmod"
CMD_ECHO="/bin/echo"
CMD_GETCFG="/sbin/getcfg"
CMD_GREP="/bin/grep"
CMD_LN="/bin/ln"
CMD_MKTEMP="/bin/mktemp"
CMD_MKDIR="/bin/mkdir"
CMD_PIDOF="/bin/pidof"
CMD_READLINK="/usr/bin/readlink"
CMD_RM="/bin/rm"
CMD_SETCFG="/sbin/setcfg"
CMD_SYNC="/bin/sync"
CMD_SETCFG="/sbin/setcfg"
CMD_SLEEP="/bin/sleep"
CMD_TAR="/bin/tar"
CMD_TOUCH="/bin/touch"
CMD_WLOG="/sbin/write_log"

##### System #####
UPDATE_PROCESS="/tmp/update_process"
UPDATE_PB=0
UPDATE_P1=1
UPDATE_P2=2
UPDATE_PE=3

SYS_HOSTNAME=`/bin/hostname`
SYS_IP=`$CMD_GREP "${SYS_HOSTNAME}" /etc/hosts | $CMD_CUT -f 1`
SYS_CONFIG_DIR="/etc/config" #put the configuration files here
SYS_INIT_DIR="/etc/init.d"
SYS_rcS_DIR="/etc/rcS.d/"
SYS_rcK_DIR="/etc/rcK.d/"
SYS_QPKG_CONFIG_FILE="/etc/config/qpkg.conf" #qpkg infomation file
SYS_QPKG_CONF_FIELD_NAME="Name"
SYS_QPKG_CONF_FIELD_VERSION="Version"
SYS_QPKG_CONF_FIELD_QPKGFILE="QPKG_File"
SYS_QPKG_CONF_FIELD_ENABLE="Enable"
SYS_QPKG_CONF_FIELD_DATE="Date"
SYS_QPKG_CONF_FIELD_SHELL="Shell"
SYS_QPKG_CONF_FIELD_INSTALL_PATH="Install_Path"
SYS_QPKG_CONF_FIELD_CONFIG_PATH="Config_Path"
SYS_QPKG_CONF_FIELD_WEBUI="WebUI"
SYS_QPKG_CONF_FIELD_WEBPORT="Web_Port"
SYS_QPKG_CONF_FIELD_SERVICEPORT="Service_Port"
SYS_QPKG_CONF_FIELD_AUTHOR="Author"
SYS_WEB_STATUS=`${CMD_GETCFG} QWEB enable` 
PUBLIC_SHARE=`/sbin/getcfg SHARE_DEF defPublic -d Public -f /etc/config/def_share.info`
WEB_SHARE=`/sbin/getcfg SHARE_DEF defWeb -d Qweb -f /etc/config/def_share.info`

##### QPKG #####
#
. qpkg.cfg
#####	Func ######
#
find_base(){
	# Determine BASE installation location according to smb.conf
	
	publicdir=`/sbin/getcfg Public path -f /etc/config/smb.conf`
	if [ ! -z $publicdir ] && [ -d $publicdir ];then
		publicdirp1=`/bin/echo $publicdir | /bin/cut -d "/" -f 2`
		publicdirp2=`/bin/echo $publicdir | /bin/cut -d "/" -f 3`
		publicdirp3=`/bin/echo $publicdir | /bin/cut -d "/" -f 4`
		if [ ! -z $publicdirp1 ] && [ ! -z $publicdirp2 ] && [ ! -z $publicdirp3 ]; then
			[ -d "/${publicdirp1}/${publicdirp2}/${PUBLIC_SHARE}" ] && QPKG_BASE="/${publicdirp1}/${publicdirp2}"
		fi
	fi
	
	# Determine BASE installation location by checking where the Public folder is.
	if [ -z $QPKG_BASE ]; then
		for datadirtest in /share/HDA_DATA /share/HDB_DATA /share/HDC_DATA /share/HDD_DATA /share/MD0_DATA /share/MD1_DATA; do
			[ -d $datadirtest/$PUBLIC_SHARE ] && QPKG_BASE="/${publicdirp1}/${publicdirp2}"
		done
	fi
	if [ -z $QPKG_BASE ] ; then
		echo "The ${PUBLIC_SHARE} share not found."
		_exit 1
	fi

}

_exit(){
	local ret=0

	case $1 in
		0)#normal exit
			ret=0
			if [ "x$QPKG_INSTALL_MSG" != "x" ]; then
				$CMD_WLOG "${QPKG_INSTALL_MSG}" 4
			else
				$CMD_WLOG "${QPKG_NAME} ${QPKG_VER} installation succeeded." 4
			fi
			$CMD_ECHO "$UPDATE_PE" > ${UPDATE_PROCESS}
			;;
		*)
			ret=1
			if [ "x$QPKG_INSTALL_MSG" != "x" ];then
				$CMD_WLOG "${QPKG_INSTALL_MSG}" 1
			else
				$CMD_WLOG "${QPKG_NAME} ${QPKG_VER} installation failed" 1
			fi
			$CMD_ECHO -1 > ${UPDATE_PROCESS}
			;;
	esac

	exit $ret
}

pre_update()
{
	TMP_DIR=/tmp
	$CMD_CP -af $QPKG_CONFIG_PATH $TMP_DIR
}

post_update()
{
	$CMD_CP -af "${TMP_DIR}/config.ini.php" ${QPKG_CONFIG_PATH}
}

install()
{
	TMP=`$CMD_READLINK $QPKG_INSTALL_PATH`
	UPDATE_FLAG=0
	if [ -f "${QPKG_SOURCE_DIR}/${QPKG_SOURCE_FILE}" ]; then
		if [ -d ${QPKG_DIR} ]; then
			CURRENT_QPKG_VER="`/sbin/getcfg ${QPKG_NAME} Version -f /etc/config/qpkg.conf`"
			QPKG_INSTALL_MSG="${QPKG_NAME} ${QPKG_VER} is already installed. Setup will now perform package upgrading."
			$CMD_ECHO "$QPKG_INSTALL_MSG"
			UPDATE_FLAG=1
		fi		
			
		$CMD_ECHO "$UPDATE_P1" > ${UPDATE_PROCESS}
		if [ $UPDATE_FLAG -eq 0 ]; then
			$CMD_ECHO "Install ${QPKG_NAME} ..."
			[ ! -d $QPKG_DIR ] || $CMD_RM -rf $QPKG_DIR
		else
			pre_update
		fi
			
		#install QPKG files 
		$CMD_TAR xzf "${QPKG_SOURCE_DIR}/${QPKG_SOURCE_FILE}" -C $QPKG_INSTALL_PATH
		if [ $? = 0 ]; then		
			# restore backups
			if [ ${UPDATE_FLAG} -eq 1 ]; then
				post_update
			else
				$CMD_CHMOD 0777 "${QPKG_DIR}"
				$CMD_CHMOD 0777 -R "${QPKG_DIR}/config"
				$CMD_CHMOD 0777 -R "${QPKG_DIR}/tmp"

				$CMD_MKDIR -p ${QPKG_DIR}/tmp/{sessions, templates_c, cache, assets, latest, tcpdf}
				$CMD_CHMOD 0777 -R "${QPKG_DIR}/tmp/"
			fi
			
			chown httpdusr.everyone ${QPKG_DIR} -R
		
			$CMD_ECHO "$UPDATE_P2" > ${UPDATE_PROCESS}
		else
			${CMD_RM} -rf ${QPKG_DIR}
			QPKG_INSTALL_MSG="${QPKG_NAME} ${QPKG_VER} installation failed. ${QPKG_SOURCE_DIR}/${QPKG_SOURCE_FILE} file error."
			$CMD_ECHO "$QPKG_INSTALL_MSG"
			_exit 1
		fi			
			
		# set QPKG information to $SYS_QPKG_CONFIG_FILE
		$CMD_ECHO "Set QPKG information to $SYS_QPKG_CONFIG_FILE"
		[ -f ${SYS_QPKG_CONFIG_FILE} ] || $CMD_TOUCH ${SYS_QPKG_CONFIG_FILE}
		$CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_NAME} "${QPKG_NAME}" -f ${SYS_QPKG_CONFIG_FILE}
		$CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_VERSION} "${QPKG_VER}" -f ${SYS_QPKG_CONFIG_FILE}
	
		#default value to activate(or not) your QPKG if it was a service/daemon
		$CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_ENABLE} "UNKNOWN" -f ${SYS_QPKG_CONFIG_FILE}
	
		#set the qpkg file name
		[ "x${SYS_QPKG_CONF_FIELD_QPKGFILE}" = "x" ] || $CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_QPKGFILE} "${QPKG_QPKG_FILE}" -f ${SYS_QPKG_CONFIG_FILE}
	
		#set the date of installation
		$CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_DATE} `date +%F` -f ${SYS_QPKG_CONFIG_FILE}
	
		#set the path of start/stop shell script
		[ "x${QPKG_SERVICE_PROGRAM}" = "x" ] || $CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_SHELL} "${QPKG_DIR}/${QPKG_SERVICE_PROGRAM}" -f ${SYS_QPKG_CONFIG_FILE}
	
		#set path where the QPKG installed
		$CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_INSTALL_PATH} "${QPKG_DIR}" -f ${SYS_QPKG_CONFIG_FILE}
	
		#set path where the QPKG configure directory/file is
		[ "x${QPKG_CONFIG_PATH}" = "x" ] || $CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_CONFIG_PATH} "${QPKG_CONFIG_PATH}" -f ${SYS_QPKG_CONFIG_FILE}
	
		#set the port number if your QPKG was a service/daemon and needed a port to run.
		[ "x${QPKG_SERVICE_PORT}" = "x" ] || $CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_SERVICEPORT} "${QPKG_SERVICE_PORT}" -f ${SYS_QPKG_CONFIG_FILE}
	
		#set the port number if your QPKG was a service/daemon and needed a port to run.
		[ "x${QPKG_WEB_PORT}" = "x" ] || $CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_WEBPORT} "${QPKG_WEB_PORT}" -f ${SYS_QPKG_CONFIG_FILE}
	
		#set the URL of your QPKG Web UI if existed.
		[ "x${QPKG_WEBUI}" = "x" ] || $CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_WEBUI} "${QPKG_WEBUI}" -f ${SYS_QPKG_CONFIG_FILE}
		
		#Sign up
		[ "x${QPKG_AUTHOR}" = "x" ] && $CMD_ECHO "Warning: ${SYS_QPKG_CONF_FIELD_AUTHOR} is not specified!!"
		[ "x${QPKG_AUTHOR}" = "x" ] || $CMD_SETCFG ${QPKG_NAME} ${SYS_QPKG_CONF_FIELD_AUTHOR} "${QPKG_AUTHOR}" -f ${SYS_QPKG_CONFIG_FILE}
			
		$CMD_SYNC
		QPKG_INSTALL_MSG="$QPKG_NAME ${QPKG_VER} has been installed in $QPKG_DIR."
		$CMD_ECHO "$QPKG_INSTALL_MSG"			
		_exit 0
	else
		QPKG_INSTALL_MSG="${QPKG_NAME} ${QPKG_VER} installation failed. ${QPKG_SOURCE_DIR}/${QPKG_SOURCE_FILE} file not found."
		$CMD_ECHO "$QPKG_INSTALL_MSG"
		_exit 1
	fi
}

##### Main #####

$CMD_ECHO "$UPDATE_PB" > ${UPDATE_PROCESS}
install

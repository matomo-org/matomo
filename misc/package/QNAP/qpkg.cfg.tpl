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
# qpkg_all.cfg
#
#	Abstract: 
#		A QPKG configuration file for
#		Piwik v1.0
#
#	HISTORY:
#		2010/11/05 -	Created - AndyChuo (zeonism at gmail dot com) 
#		2012/07/28 -	Modified - Anthon Pang (anthon at piwik dot org)
# 
#================================================================
QPKG_AUTHOR="QNAP Systems, Inc."
QPKG_SOURCE_DIR="."
QPKG_QPKG_FILE="Piwik.qpkg"
QPKG_SOURCE_FILE="Piwik.tgz"
QPKG_NAME="Piwik"
QPKG_VER="{{VERSION}}"
QPKG_MAJOR_VER="1"
QPKG_MINOR_VER="0"
QPKG_TYPE="Web Applications"
QPKG_LOG_PATH=""
QPKG_INSTALL_PATH="/share/${WEB_SHARE}"
QPKG_CONFIG_PATH="$QPKG_INSTALL_PATH/piwik/config/config.ini.php"
QPKG_DIR="$QPKG_INSTALL_PATH/piwik"
QPKG_WEBUI="/piwik/" #URL relative path of your QPKG web interface led by "/"
QPKG_INSTALL_MSG=""
QPKG_WEB_PORT=""
QPKG_SERVICE_PORT=""

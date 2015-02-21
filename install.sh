#!/bin/sh
PLUGINDIR=$(cd `dirname $0` && pwd)
CONFIGFILE=${PLUGINDIR}/../../data/config.php
DBHOST=`grep dbHost ${CONFIGFILE} | cut -d\' -f2`
DBUSER=`grep dbUser ${CONFIGFILE} | cut -d\' -f2`
DBPASS=`grep dbPassword ${CONFIGFILE} | cut -d\' -f2`
DBNAME=`grep dbName ${CONFIGFILE} | cut -d\' -f2`

MYSQLOPTS="--user=${DBUSER} --password=${DBPASS} --host=${DBHOST} ${DBNAME}"

MYPLUGIN=`basename ${PLUGINDIR}`/index.php

INSTALLED=`mysql ${MYSQLOPTS} -N -e "SELECT LOCATE('${MYPLUGIN}', value) AS foo FROM lychee_settings WHERE lychee_settings.key = 'plugins'"`

if [ ${INSTALLED} -ne 0 ] ; then
  echo ${MYPLUGIN} already installed!
else
  echo "Installing plugin: ${MYPLUGIN}"
  mysql ${MYSQLOPTS} -N -e "UPDATE lychee_settings SET value=CONCAT_WS(';', NULLIF(value, ''), '${MYPLUGIN}') WHERE lychee_settings.key = 'plugins'"
fi

#mysql ${MYSQLOPTS} -N -e "UPDATE lychee_settings SET value='' WHERE lychee_settings.key = 'plugins'"
#exit

PLUGINS=`mysql ${MYSQLOPTS} -N -e "SELECT value FROM lychee_settings WHERE lychee_settings.key = 'plugins'"`
echo "Plugins: ${PLUGINS}"

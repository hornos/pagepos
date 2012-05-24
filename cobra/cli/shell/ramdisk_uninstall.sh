#!/bin/bash
effuser=`whoami`
if test "${effuser}" != "root"; then
  exit 1
fi


# begin header
cobra_bootstrap="/home/cobra/Cobra/1.0/cobra/gc.bootstrap.php"
cobra_home=`dirname ${cobra_bootstrap}`
phpcli_bootstrap="${cobra_home}/bootstrap.sh"
if ! test -r ${phpcli_bootstrap}; then
  exit 1
fi
. ${phpcli_bootstrap}
# end header


# read config
config="./config.sh"
if ! test -r ${config}; then
  exit 1;
fi
. ./config.sh


# script setup
script=$(basename $0)
name="${script%%.sh}"


#### MAIN   ####
if ! test -r ${ramdisk_lock}; then
  exit 1
fi

sessions_gc_pid=""
sessions_gc_pid=`ps -aux 2>/dev/null | grep -v "SCREEN" | grep "daemon.sh" | grep "sessions_gc" | awk '{print $2}'`
users_gc_pid=""
users_gc_pid=`ps -aux 2>/dev/null | grep -v "SCREEN" | grep "daemon.sh" | grep "users_gc" | awk '{print $2}'`

if ! test "${users_gc_pid}" = "" ; then
  kill ${users_gc_pid}
  echo -n "Waiting for sessions_gc to terminate: "
  while test "${users_gc_pid}" != "" ; do
    sleep 2
    echo -n "."
    users_gc_pid=`ps -aux 2>/dev/null | grep -v "SCREEN" | grep "daemon.sh" | grep "users_gc" | awk '{print $2}'`
  done
  echo
fi


if ! test "${sessions_gc_pid}" = "" ; then
  kill ${sessions_gc_pid}
  echo -n "Waiting for sessions_gc to terminate: "
  while test "${sessions_gc_pid}" != "" ; do
    sleep 2
    echo -n "."
    sessions_gc_pid=`ps -aux 2>/dev/null | grep -v "SCREEN" | grep "daemon.sh" | grep "sessions_gc" | awk '{print $2}'`
  done
  echo
fi


cd ${cobra_home}/..;
cobra_dir=`basename ${cobra_home}`

## Cobra
rm -f ${apache_conf_dir}/${apache_conf_file}
echo -n "Apache reload: "
${cmd_apache_reload} > /dev/null
ret=$?
if test $ret != 0; then
  echo "Failed"
  exit 1
fi
echo "Ok"

## Ramdisk
echo -n "Ramdisk umount: "
umount_ramdisk
ret=$?
if test $ret != 0; then
  echo "Failed"
  exit 1
fi
echo "Ok"


exit 0
#### MAIN   ####

#!/bin/bash
# begin header
if test "${COBRA_BOOTSTRAP}" == "" ; then
  export COBRA_BOOTSTRAP="/home/cobra/Cobra/1.0.4/cobra/gc.bootstrap.php"
fi
cobra_bootstrap=${COBRA_BOOTSTRAP}
cobra_home=`dirname ${cobra_bootstrap}`
phpcli_bootstrap="${cobra_home}/bootstrap.sh"
if ! test -r ${phpcli_bootstrap}; then
  exit 1
fi
. ${phpcli_bootstrap}
# end header

script=$(basename $0)
name="${script%%.sh}"

# arguments
program="sessions_gc"
sleeptime=30
verbose=false
killprog=false
force=false
while getopts kfhvp:t: option ; do
  case ${option} in
    p)
      program=${OPTARG};;
    t)
      sleeptime=${OPTARG};;
    v)
      verbose=true;;
    k)
      killprog=true;;
    f)
      force=true;;
    h)
      echo "Usage: ${name} [ -p program ] [ -s sleeptime ]";
      exit 2;;
  esac
done

# variables
php_script="${phpcli_dir}/${program}.cli.php"
lock="${program}.lck"

#### FUNCTION ####
function signal_exit() {
  daemon_message "Signal exit"
  delete_lock ${lock}
  exit 3
}

function daemon_message() {
  message=${*}
  if ${verbose} ; then
    echo "${script} [${program}]: ${message}"
  fi
}

function daemon_main() {
  now=$(date +"%Y-%m-%d %H:%M:%S")
  daemon_message "Started ${now}"
  
  # run php
  if test -x ${php_script}; then
    ${php_script} 2> /dev/null
    ret=0
  else
    echo "${php_script} is not executable"
    daemon_message "${php_script} is not executable"
    ret=2
  fi
  
  now=$(date +"%Y-%m-%d %H:%M:%S")
  daemon_message "Finished ${now}"
  return ${ret}
}
#### FUNCTIONS ####


if $killprog; then
  kill_lock ${lock}
  exit $?
fi


trap signal_exit 2 3 9 15

if $force; then
  delete_lock ${lock}
fi

# check lock
create_lock ${lock}
if test $? -eq 0; then
  echo "LOCK EXISTS"
  daemon_message "Lock exists"
  exit 1
fi


counter=$sleeptime
while true ; do
  if test $counter -eq $sleeptime; then
    echo
    daemon_main
    counter=0
    echo -n "Waiting"
  fi
  sleep 1
  counter=$(($counter+1))
  echo -n "."
done

delete_lock ${lock}

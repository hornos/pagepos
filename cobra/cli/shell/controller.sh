#!/bin/bash

# begin header
cobra_bootstrap="/home/cobra/Cobra/1.0.4/cobra/gc.bootstrap.php"
cobra_home=`dirname ${cobra_bootstrap}`
phpcli_bootstrap="${cobra_home}/bootstrap.sh"
if ! test -r ${phpcli_bootstrap}; then
  exit 1
fi
. ${phpcli_bootstrap}
# end header


cd ${cobra_home}/cli/shell
# script setup
script=$(basename $0)
name="${script%%.sh}"


# type of run
runtype=${1:-start}

if test "${runtype}" = "start"; then
  ./daemon.sh -v -p sessions_gc -t 10 &> /dev/null &
  sleep 1
  ./daemon.sh -v -p pagepos_gc -t 10 &> /dev/null &
else
  scpid=`get_lock "sessions_gc.lck"`
  if test -n "${scpid}"; then
    kill -s SIGTERM ${scpid}
    sleep 1
  fi

  scpid=`get_lock "pagepos_gc.lck"`
  if test -n "${scpid}"; then
    kill -s SIGTERM ${scpid}
  fi
fi

exit 0
#### MAIN   ####

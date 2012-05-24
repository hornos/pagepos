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

cd ${cobra_home}/cli/shell

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
sudo -u ${cobra_user} ./start_daemons.sh -s
sudo -u ${cobra_user} ./start_daemons.sh -p

exit 0
#### MAIN   ####

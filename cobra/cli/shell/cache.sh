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


# Variables
script=$(basename $0)
name="${script%%.sh}"
php_script="${phpcli_dir}/${name}.cli.php"
log="${log_dir}/${name}.log"
lock="${name}.lck"

# now=$(date +"%Y-%m-%d %H:%M:%S")
echo -n "[${script}] "  > ${log}

# Check lock
create_lock ${lock}
if test $? -eq 0; then
  echo "LOCK EXISTS"  >> ${log}
  exit 1
fi

# Run php
if test -x ${php_script}; then
  ${php_script} &> ${log}
  # ${php_script}
else
  echo "${php_script} is not executable" >> ${log} 
  delete_lock ${lock}
  exit 2
fi

# now=$(date +"%Y-%m-%d %H:%M:%S")
# echo "${script}: Finished ${now}"

delete_lock ${lock}

exit 0

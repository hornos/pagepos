#!/bin/bash

#### begin header
### check directory
db_dir=`basename ${1:-cobra}`
db_dir="./${db_dir}"
if ! test -d ${db_dir} ; then
  echo "Directory error: ${db_dir}"
  exit 1
fi

### change directory
cd ${db_dir}

### read DB config
db_cfg="./DB.conf"
if ! test -r ${db_cfg} ; then
  echo "DB config error"
  exit 2
fi
. ${db_cfg}
#### end header


# create DB init file
timestamp=`date`
echo -e "\n--\n-- ${timestamp}\n--" > "${COBRA_SQL_TEMP}"


# perform initialization
echo -e "\nDatabase reset: ${PG_DB} @ ${PG_HOST} : ${PG_PORT}"

for skel in ${COBRA_SQL_RESET[@]} ; do
  echo -e "\n\n\n-- skel src: ${skel}" >> "${COBRA_SQL_TEMP}"
  cat "${skel}" >> "${COBRA_SQL_TEMP}"
done

echo -ne "\nReset database? (y/n) "
read ans
if test "${ans}" != "y"; then
  exit 3
fi
$PG_CMD -f "${COBRA_SQL_TEMP}" -U "${PG_USER}" -h "${PG_HOST}" -p "${PG_PORT}" -d "${PG_DB}"

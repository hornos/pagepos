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

# read DB init
for init in ${COBRA_SQL_INIT[@]} ; do
  echo -e "\n\n\n-- init src: ${init}" >> "${COBRA_SQL_TEMP}"
  cat "${init}" >> "${COBRA_SQL_TEMP}"
done

# read DB skel
for skel in ${COBRA_SQL_SKEL[@]} ; do
  echo -e "\n\n\n-- skel src: ${skel}" >> "${COBRA_SQL_TEMP}"
  cat "${skel}" >> "${COBRA_SQL_TEMP}"
done


# perform initialization
echo -e "\nDatabase initialization: ${PG_DB} @ ${PG_HOST} : ${PG_PORT}"
echo -e "\nWARNING: ALL DATA WILL BE LOST!"

# clean before init
if test "${COBRA_SQL_CLEAN:-}" != "" ; then
  echo -ne "\n0. Step: Run the cleaner script? (y/n) "
  read ans
  if test "${ans}" != "y"; then
    exit 2
  fi
  $PG_CMD -f "${COBRA_SQL_CLEAN}" -U "${PG_USER}" -h "${PG_HOST}" -p "${PG_PORT}" -d "${PG_DB}"
fi

# init
echo -ne "\n1. Step: Initialize the database? (y/n) "
read ans
if test "${ans}" != "y"; then
  exit 2
fi
$PG_CMD -f "${COBRA_SQL_DB_INIT}" -U "${PG_USER}" -h "${PG_HOST}" -p "${PG_PORT}"

# setup
echo -ne "\n2. Step: Setup the database? (y/n) "
read ans
if test "${ans}" != "y"; then
  exit 3
fi
$PG_CMD -f "${COBRA_SQL_TEMP}" -U "${PG_USER}" -h "${PG_HOST}" -p "${PG_PORT}" -d "${PG_DB}"

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

db_usr="${2:-${PG_DB}_admin}"
# clean & init db
echo -e "\nLogin to database: ${PG_DB} @ ${PG_HOST} : ${PG_PORT}\n"
${PG_CMD} -U "${db_usr}" -h "${PG_HOST}" -p "${PG_PORT}" -d ${PG_DB}

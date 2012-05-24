#!/bin/bash

cat ${1} | awk -F, '
BEGIN{
  pre="INSERT INTO geonames2users (geonameid, email, link, status, price ) VALUES";
  pos="'\''demo'\'', '\''0'\'' );";
}
{
  printf("%s ('\''%s'\'','\''%s'\'','\''%s'\'', %s\n",pre,$1,$3,$4,pos);
}'


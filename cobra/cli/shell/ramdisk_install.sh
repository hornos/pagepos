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
if test -r ${ramdisk_lock}; then
  remount_ramdisk
fi

cd ${cobra_home}/..;
cobra_dir=`basename ${cobra_home}`

## Ramdisk
echo "Ramdisk: ${ramdisk} (${ramdisk_size})" 
echo -n "Ramdisk mount: "
create_ramdisk
remount_ramdisk
ret=$?
if test $ret != 0; then
  echo "Failed"
  exit 1
fi
echo "Ok"


## Cobra
echo -n "Install Cobra: "
mkdir ${ramdisk_home}
ret=$?
if test $ret != 0; then
  echo "Failed"
  exit 1
fi
cp ${verbose} -R ./* ${ramdisk_home}
echo "Ok"

## Permissions
chown -R ${cobra_user}.root ${ramdisk_home}/cobra/lock
chown -R ${cobra_user}.root ${ramdisk_home}/cobra/log

## Shell Bootstrap
phpcli="${ramdisk_home}/cobra/cli/shell/php.cli.sh"
daemon="${ramdisk_home}/cobra/cli/shell/daemon.sh"
gc_bootstrap="${ramdisk_home}/cobra/gc.bootstrap.php"
session_bootstrap="${ramdisk_home}/cobra/session.bootstrap.php"

bootstrap="\texport\tCOBRA_BOOTSTRAP=${gc_bootstrap}"
cp ${verbose} ${phpcli} ${phpcli}.tmp
cat ${phpcli}.tmp | awk -v rmbs="${bootstrap}" '{if(match($0,"COBRA_BOOTSTRAP=")){print rmbs}else{print $0}}' > ${phpcli}
rm ${verbose} ${phpcli}.tmp

cp ${verbose} ${daemon} ${daemon}.tmp
cat ${daemon}.tmp | awk -v rmbs="${bootstrap}" '{if(match($0,"COBRA_BOOTSTRAP=")){print rmbs}else{print $0}}' > ${daemon}
rm ${verbose} ${daemon}.tmp

## Reset Cache
echo -n "Reset cache: "
cd "${ramdisk_home}/cobra/cli/shell"
./cache.sh
ret=$?
if test $ret != 0; then
  echo "Failed"
  exit 1
fi
echo "Ok"

## Reset Apache
echo -n "Reset apache: "
apache_config="${ramdisk_home}/${apache_conf_file}"
cat > ${apache_config} <<END
Alias /cobra-ramdisk ${ramdisk_home}/htdocs

<Directory ${ramdisk_home}/htdocs>
   Options +FollowSymlinks
   AllowOverride All
   SetEnv COBRA_BOOTSTRAP ${session_bootstrap}
   order deny,allow
#   deny from all
</Directory>
END

ln -fs ${apache_config} ${apache_conf_dir}/${apache_conf_file}
${cmd_apache_reload} > /dev/null
ret=$?
if test $ret != 0; then
  echo "Failed"
  exit 1
fi
echo "Ok"

## Finish
chmod a-x "${ramdisk_home}/cobra/cli/shell/ramdisk_install.sh"
chmod a-x "${ramdisk_home}/cobra/cli/shell/ramdisk_uninstall.sh"

cd "${ramdisk_home}/cobra/cli/shell"
sudo -u ${cobra_user} ./start_daemons.sh -s

exit 0
#### MAIN   ####

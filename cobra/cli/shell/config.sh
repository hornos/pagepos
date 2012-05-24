
#### OS COMMANDS ####
# mount
verbose=""
cmd_mount="/bin/mount ${verbose}"
cmd_umount="/bin/umount ${verbose}"
# apache
cmd_apache_reload="/etc/init.d/apache2 reload"
apache_conf_dir="/etc/apache2/conf.d"
apache_conf_file="cobra-ramdisk.conf"
#### OS COMMANDS ####


#### COBRA       ####
# cobra
cobra_user="cobra"
# ramdisk
ramdisk=${1:-/var/ramdisk}
ramdisk_size=${2:-8m}
ramdisk_home="${ramdisk}/cobra"
ramdisk_lock="${ramdisk_home}/VERSION"
#### COBRA       ####


#### FUNCTIONS   ####
function create_ramdisk() {
  if ! test -d ${ramdisk} ; then
    mkdir ${verbose} ${ramdisk}
  fi
  return $?
}


function umount_ramdisk() {
  ${cmd_umount} ${ramdisk} 2> /dev/null
  return $?
}


function mount_ramdisk() {
  ${cmd_mount} -t tmpfs none ${ramdisk} -o size=${ramdisk_size}
  return $?
}


function remount_ramdisk() {
  ${cmd_mount} | grep "${ramdisk}" > /dev/null
  ret=$?
  if test $ret = 0; then
    # ${cmd_umount} ${ramdisk} 2> /dev/null
    umount_ramdisk
    ret=$?
    if test $ret != 0; then
      return $ret
    fi
  fi  
  # ${cmd_mount} -t tmpfs none ${ramdisk} -o size=${ramdisk_size}
  mount_ramdisk
  return $?
}
#### FUNCTIONS   ####


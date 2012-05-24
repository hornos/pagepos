#!/bin/bash

clean_sessions=false
clean_users=false
clean_pagepos=false
sleeptime=30

while getopts ":supt:" opt; do
  case ${opt} in
    s)
      clean_sessions=true
      ;;
    u)
      clean_users=true
      ;;
    p)
      clean_pagepos=true
      ;;
    t)
      sleeptime=${OPTARG}
      ;;
    ?)
      echo "Invalid option: -$OPTARG"
      ;;
  esac
done

if $clean_sessions; then
  echo -n "Cobra session cleaner: "
  screen -d -m -S sessions_gc -t sessions_gc ./daemon.sh -v -t ${sleeptime} -p sessions_gc
  echo $!
fi

if $clean_users; then
  echo -n "Cobra user cleaner:"
  screen -d -m -S users_gc -t users_gc ./daemon.sh -v -t ${sleeptime} -p users_gc
  echo $!
fi

if $clean_pagepos; then
  echo -n "Cobra pagepos cleaner:"
  screen -d -m -S pagepos_gc -t pagepos_gc ./daemon.sh -v -t ${sleeptime} -p pagepos_gc
  echo $!
fi


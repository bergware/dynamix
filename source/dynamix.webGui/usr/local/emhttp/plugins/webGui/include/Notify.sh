#!/bin/bash

# Add Notification
if [ ! "$1" == "-plugin" ] || [ ! "$3" == "-subject" ] || [ ! "$5" == "-description" ] || [ ! "$7" == "-importance" ]; then
  echo ""
  echo "USAGE - REQUIRED FIELDS IN THIS ORDER:"
  echo 'notify.sh -plugin "Plugin Name" -subject "Notification Subject" -description "Notification description" -importance "default,warning or alert"'
  echo ""
  echo 'The only acceptted characters are Alpha, Numeric and the following: < > - _'
  echo 'Notifications will accept HTML tags like <br>'
  exit 1;
else
  timestamp=$(date +%s)
  # Function to load parameters fron an "ini" formatted file
  get_param(){
    awk '$1=="["s"]"{f++}f&&$1~p{split($1,a,m);gsub("[\t ]*$","",a[2]);print a[2];exit}' s="$2" p="^[\t ]*$3" m="^[\t ]*$3[\t ]*=[\t ]*" FS="\$^" "$1"
  }
  # Set save path
  sfINI="/var/local/emhttp/plugins/dynamix.webGui.ini"
  NotificationPath=$(get_param $sfINI notify path)
  NotificationPath=$(echo "$NotificationPath/unread" | sed 's/"//g')
  NotifyPath=$(echo "$NotificationPath/$2-$timestamp.notify" | sed 's/"//g')

  # If directory not there, create it
  if [ ! -d "$NotificationPath" ]; then
    mkdir -p "$NotificationPath"
  fi

  # Write result to notification file
  echo -e "timestamp = $timestamp " > "$NotifyPath"
  echo -e "plugin = $2 " >> "$NotifyPath"
  echo -e "subject = $4 " >> "$NotifyPath"
  echo -e "description = $6 " >> "$NotifyPath"
  echo -e "importance = $8 " >> "$NotifyPath"
  exit 0;
fi
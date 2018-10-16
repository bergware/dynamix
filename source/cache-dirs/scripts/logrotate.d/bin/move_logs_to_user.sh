# this script is kind of fragile, would be be

# Syntax <logfile> <backup-directory name and log prefix name>

DATE=$(date +%Y%m%d_%H%M)
LOGDIR=$(dirname $1)
LOGBASENAME=$(basename $1)
# TODO Configure backup dir 
BACKUP_DIR=/mnt/user/app/unraid_logs/$2

logger "logrotate: move_logs_to_user $1 $2"

if [ -d /mnt/user ]; then

	[ ! -d ${BACKUP_DIR} ] && mkdir -p ${BACKUP_DIR} && chmod 777 ${BACKUP_DIR}

	# if logrotate param extension is used, then extension is after .1, otherwise .1 is after extension
	#mv $1.$2.1 ${BACKUP_DIR}/$1_${DATE}.$2

	# Find seems more stable towards changes making this and the logrotate script get out of sync, than moving a particular file
	find $LOGDIR -name "$2-*"  -exec mv {} ${BACKUP_DIR} \;
	find "${BACKUP_DIR}" -name "$2*" -maxdepth 1 -ctime +30 -delete
	
	chmod 666 ${BACKUP_DIR}/*
	logger "logrotate: move_logs_to_user finished $1 $2"
else
	logger "logrotate: move_logs_to_user ERROR missing /mnt/user"
fi

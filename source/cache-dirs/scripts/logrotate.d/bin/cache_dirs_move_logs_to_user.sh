# this script is kind of fragile, would be be

# Syntax <logfile> <backup-directory name and log prefix name>
# Script will move all prefix-* to rolled log dir

SCRIPT_NAME=$(basename "$0")
DATE=$(date +%Y%m%d_%H%M)
LOGDIR=$(dirname $1)
LOGBASENAME=$(basename $1)
# TODO Configure backup dir 
BACKUP_DIR=/mnt/user/app/unraid_logs/$2
LOG_PREFIX=$2
DELETE_AGE_DAYS=30

logger "logrotate: move_logs_to_user $1 $2"

if [ -d /mnt/user ]; then
	[ ! -d "${BACKUP_DIR}" ] && mkdir -p "${BACKUP_DIR}" && chmod 777 "${BACKUP_DIR}"

	# if logrotate param extension is used, then extension is after .1, otherwise .1 is after extension
	#mv $1.$2.1 ${BACKUP_DIR}/$1_${DATE}.$2

	# Find seems more stable towards changes making this and the logrotate script get out of sync, than moving a particular file
	find $LOGDIR -name "${LOG_PREFIX}-*"  -exec mv {} "${BACKUP_DIR}" \;
	find "${BACKUP_DIR}" -name "${LOG_PREFIX}*" -maxdepth 1 -type f -exec chmod 666 {} \; -print
	find "${BACKUP_DIR}" -name "${LOG_PREFIX}*" -maxdepth 1 -type f -ctime "+${DELETE_AGE_DAYS}" -delete

	logger "logrotate: $SCRIPT_NAME finished $1 $LOG_PREFIX"
else
	logger "logrotate: $SCRIPT_NAME ERROR missing /mnt/user"
fi

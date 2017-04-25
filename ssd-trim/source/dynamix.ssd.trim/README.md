**Dynamix SSD TRIM**

Dynamix SSD trim creates a cronjob to do regular SSD TRIM operations on all mount points which support the operation.
The command 'fstrim -a -v' is executed at the given interval.

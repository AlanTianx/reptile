#!/bin/bash
DATE=$(date +%Y%m%d%H%M)
PATH_CODE="/app/sem/semcode/mermining"
PATH_GO="/app/sem/semcode/vendor/google"
PHPNAME="$PATH_CODE/shoponline.php $PATH_CODE/similarweb.php $PATH_GO/gettarget1.php $PATH_CODE/brandsearch.php $PATH_CODE/getsemrush.php $PATH_CODE/getsemrush2.php"
for i in $PHPNAME
do
ps aux |grep  $i |grep -v grep  >> /dev/null
	if [ $? == 0 ];then
		continue
	else
		php $i >> /app/logs/mermining/mermining.$DATE.log
	fi
done
find  /app/logs/mermining/*log  -mtime +1 -delete

#!/bin/bash
PATH_CODE="/app/sem/semcode/mermining"
PHPNAME="$PATH_CODE/googlecrawler/examples/getgoogleindex_new.php"
for i in $PHPNAME
do
ps aux |grep  $i |grep -v grep
	if [ $? == 0 ];then
		continue
	else
		php $i
	fi
done



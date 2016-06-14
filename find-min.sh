#!/bin/bash
#
# Syntax: find-min.sh <namespace> <loops>

MIN=1000000
NS='drupal-perf'
LOOPS=100
: ${XHPROF_KIT_DOCROOT:="127.0.0.1"}
[ -n "$1" ] && NS=$1
[ -n "$2" ] && LOOPS=$2

for i in $(seq 1 $LOOPS)
do
	sudo ionice -c1 -n0 nice -n -20 chrt 99 taskset -c 1 php ./index-perf.php --namespace "$NS" 'http://'"$XHPROF_KIT_DOCROOT"'/node' | egrep 'loop time|xhprof'
done | while read line
do
        mytime=$(echo $line | cut -d'|' -f2 | cut -d's' -f1 | sed 's/^/1000*1000*/' | bc | cut -d'.' -f1)

	if [ $mytime -lt $MIN ]
	then
		MIN=$mytime
		echo $line
	fi
done

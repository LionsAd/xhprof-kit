#!/bin/bash
#
# Syntax: find-min.sh <namespace> <loops>

MIN=1000000000
NS='drupal-perf'
LOOPS=100
URL='/'
[ -n "$1" ] && NS=$1
[ -n "$2" ] && LOOPS=$2
[ -n "$3" ] && URL=$3

for i in $(seq 1 $LOOPS)
do
	curl -s 'http://127.0.0.1/index-perf.php?extra='"$NS"'&url='"$URL" | grep 'loop time: |'
done | while read line
do
	mytime=$(echo $line | cut -d'|' -f2 | cut -d's' -f1 | sed 's/^/1000*1000*/' | bc | cut -d'.' -f1)

	if [ $mytime -lt $MIN ]
	then
		MIN=$mytime
		echo $line
	fi
	LC_ALL=C sleep 0.1
done

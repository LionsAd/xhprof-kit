#!/bin/bash
#
# Syntax: find-min.sh <namespace> <loops>

MIN=1000000000
NS='drupal-perf'
LOOPS=100
[ -n "$1" ] && NS=$1
[ -n "$2" ] && LOOPS=$2

for i in $(seq 1 $LOOPS)
do
	curl -s 'http://127.0.0.1/index-perf.php?extra='"$NS" | grep 'loop time: |'
	#curl -H "Accept: application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5" -s 'http://127.0.0.1/node/1?extra='"$NS" | grep 'loop time: |0.'
	#curl -H "Accept: text/html,application/xml,application/xhtml+xml;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5" -s 'http://127.0.0.1/node/1?extra='"$NS" | grep 'loop time: |0.'
done | while read line
do
	mytime=$(echo $line | cut -d'|' -f2 | cut -d's' -f1 | sed 's/^/1000*1000*/' | cut -d'.' -f1 | bc)

	if [ $mytime -lt $MIN ]
	then
		MIN=$mytime
		echo $line
	fi
	LC_ALL=C sleep 0.1
done

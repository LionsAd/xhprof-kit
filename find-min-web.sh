#!/bin/bash
#
# Syntax: find-min.sh <namespace> <loops>

MIN=1000000000
NS='drupal-perf'
LOOPS=100
URL='/'
RUN_COLLECTOR=""
: ${XHPROF_KIT_DOCROOT:="127.0.0.1"}
[ -n "$XHPROF_KIT_COLLECTOR_FILE" ] && RUN_COLLECTOR=$XHPROF_KIT_COLLECTOR_FILE
[ -n "$1" ] && NS=$1
[ -n "$2" ] && LOOPS=$2
[ -n "$3" ] && URL=$3
[ -n "$4" ] && RUN_COLLECTOR=$4

# Do one call to ensure caches are primed.
curl -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' -s 'http://'"$XHPROF_KIT_DOCROOT"'/index-perf.php?extra='"$NS"'&url='"$URL"'&docroot='"$XHPROF_KIT_DOCROOT" | grep 'loop time: |' &>/dev/null

for i in $(seq 1 $LOOPS)
do
	curl -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' -s 'http://'"$XHPROF_KIT_DOCROOT"'/index-perf.php?extra='"$NS"'&url='"$URL"'&docroot='"$XHPROF_KIT_DOCROOT" | grep 'loop time: |'
done | while read line
do
	mytime=$(echo $line | cut -d'|' -f2 | cut -d's' -f1 | sed 's/^/1000*1000*/' | bc | cut -d'.' -f1)
	if [ $mytime -lt $MIN ]
	then
		MIN=$mytime
		echo $line
	fi
	if [ -n "$RUN_COLLECTOR" ]
	then
		RUN_ID=$(echo $line | cut -d'|' -f3)
                SANITIZED_NS=$(echo $NS | sed 's/\./_/g')
		echo "${RUN_ID}|${SANITIZED_NS}" >> $RUN_COLLECTOR
	fi
	LC_ALL=C sleep 0.1
done

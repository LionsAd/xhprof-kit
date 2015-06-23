#!/bin/bash

if [ $# -lt 2 ]
then
	echo "Usage: $0 <collector-file> <destination-directory> [namespace]"
	exit 1
fi

export RUN_COLLECTOR="$1"
export DESTINATION="$2"
export FIND_NAMESPACE=""
test -n "$3" && FIND_NAMESPACE="|$3"

XHPROF_DIR=$(php -r 'print ini_get("xhprof.output_dir");')

rm -f "$DESTINATION"/*

export IFS="|"

cat "$RUN_COLLECTOR" | sed 's/$/|/g' | grep "$FIND_NAMESPACE|" | while read run_id namespace
do
	cp -f "$XHPROF_DIR/$run_id"* $DESTINATION/"${run_id}.${namespace}.xhprof"
done

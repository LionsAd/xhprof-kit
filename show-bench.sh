#!/bin/sh

base_run=$1
base_branch=$2

IFS='|'
cat $(dirname $0)/data/${base_run}-${base_branch}.data | while read data base_run new_run extra
do
  # Display XHProf Report again, but override URL
  php $(dirname $0)/xhprof-check.php "$base_run" "$new_run" "$extra"
  echo
done
echo "---"
echo "ct = function calls, wt = wall time, cpu = cpu time used, mu = memory usage, pmu = peak memory usage"

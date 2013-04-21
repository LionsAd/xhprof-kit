#!/bin/bash

base=''
branch=master

[ -n "$1" ] && branch=$1
shift

basedir=$(dirname $0)

#echo $basedir/benchmark-branch.sh "$branch"

run_data=$($basedir/benchmark-branch.sh "$branch")
new_run=$(echo $run_data | cut -d'|' -f3)
xhprof_url=$(echo $run_data | cut -d'|' -f6)

while [ $# -gt 0 ]
do
	base_run=$1
	base_branch=$2
	shift
	shift

	php $(dirname $0)/xhprof-check.php "$base_run" "$new_run" "$base_branch..$branch"
        echo $xhprof_url | perl -pi -e "s/run=.*\"/run1=$base_run&run2=$new_run&extra=$base_branch..$branch\"/"
        echo

        # Save data for uploading and re-display
        echo "DATA|$base_run|$new_run|$base_branch..$branch" 1>&3
done

#echo "---"
#echo "fc = function calls, wt = wall time, cpu = cpu time used, mu = memory usage, pmu = peak memory usage"

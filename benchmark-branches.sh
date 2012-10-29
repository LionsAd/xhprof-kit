#!/bin/sh

b_base=$1
b_branch=$2
shift
shift

for branch in $* 
do
        $(dirname $0)/benchmark-vs-baseline.sh "$branch" $b_base $b_branch
done
echo "---"
echo "ct = function calls, wt = wall time, cpu = cpu time used, mu = memory usage, pmu = peak memory usage"

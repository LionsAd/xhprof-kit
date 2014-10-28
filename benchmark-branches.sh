#!/bin/sh

b_base=$1
b_branch=$2
shift
shift

mkdir -p $(dirname $0)/data/
exec 3>$(dirname $0)"/data/${b_base}-${b_branch}.data"
export XHPROF_KIT_COLLECTOR_FILE=$(dirname $0)"/data/${b_base}-${b_branch}.runs"
echo -n > "$XHPROF_KIT_COLLECTOR_FILE"

for branch in $* 
do
        $(dirname $0)/benchmark-vs-baseline.sh "$branch" $b_base $b_branch
done
unset XHPROF_KIT_COLLECTOR_FILE
echo "---"
echo "ct = function calls, wt = wall time, cpu = cpu time used, mu = memory usage, pmu = peak memory usage"

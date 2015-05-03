#!/bin/sh

BASEDIR=$( cd $(dirname $0); pwd );

path_to_test=$1
b_base=$2
b_branch=$3
shift
shift
shift

mkdir -p $(dirname $0)/data/
exec 3>$(dirname $0)"/data/${b_base}-${b_branch}.data"
export XHPROF_KIT_COLLECTOR_FILE=$(dirname $0)"/data/${b_base}-${b_branch}.runs"
echo -n > "$XHPROF_KIT_COLLECTOR_FILE"

echo "### BENCHMARKING ..."
echo

for branch in $*
do
        $(dirname $0)/benchmark-vs-baseline.sh $path_to_test "$branch" $b_base $b_branch
	echo
done

# Now create XHProf aggregate runs
NAMESPACES=$(cat "$XHPROF_KIT_COLLECTOR_FILE" | cut -d'|' -f2 | sort | uniq)
XHPROF_DIR=$(php -r 'print ini_get("xhprof.output_dir");')

LAST_RUN_ID=""
LAST_NS=""

for namespace in $NAMESPACES
do
	TMPDIR=$(mktemp -d 2>/dev/null || mktemp -d -t "xhprof-kit-tmp-dir")
	# bail out if the dir is empty.
	[ -z "$TMPDIR" ] && exit 1

	$(dirname $0)/copy-runs-to-directory.sh "$XHPROF_KIT_COLLECTOR_FILE" "$TMPDIR" "$namespace"

	TMPDIR2=$(mktemp -d 2>/dev/null || mktemp -d -t "agg-tmp-dir")
	# bail out if the dir is empty.
	[ -z "$TMPDIR2" ] && exit 1

	(
		cd "$TMPDIR2"
		$BASEDIR/XHProfCLI/bin/xhprof agg "$TMPDIR" >&2
		for file in *.xhprof
		do
			run_id=$(echo $file | cut -d'.' -f1)
			ns=$(echo $file | cut -d'.' -f2)
			echo "$run_id|$ns"
			# @todo Remove hardcoded drupal-perf everywhere.
			cp "$file" "$XHPROF_DIR/${run_id}.drupal-perf.xhprof"
		done
	)
	rm -rf "$TMPDIR"
	rm -rf "$TMPDIR2"
done | while read line
do
	run_id=$(echo $line | cut -d'|' -f1)
	ns=$(echo $line | cut -d'|' -f2)

	# @todo only works for two branches properly.
	if [ -n "$LAST_RUN_ID" ]
	then
		echo "DATA|$LAST_RUN_ID|$run_id|SUM: $LAST_NS..$ns" >&3
	fi

	LAST_RUN_ID=$run_id
	LAST_NS=$ns
done

echo
echo
echo
echo "### FINAL REPORT"
echo

# Now show the report with aggregates included
$(dirname $0)/show-bench.sh "$b_base" "$b_branch"

echo
echo "### XHPROF-LIB REPORT"
echo

# Now show the XHProfCLI report
TMPDIR=$(mktemp -d 2>/dev/null || mktemp -d -t "xhprof-kit-tmp-dir")
# bail out if the dir is empty.
[ -z "$TMPDIR" ] && exit 1
$(dirname $0)/copy-runs-to-directory.sh "$XHPROF_KIT_COLLECTOR_FILE" "$TMPDIR"
$(dirname $0)/XHProfCLI/bin/xhprof summary "$TMPDIR"
rm -rf $TMPDIR


unset XHPROF_KIT_COLLECTOR_FILE

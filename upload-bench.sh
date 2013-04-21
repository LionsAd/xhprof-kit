#!/bin/sh

KEY=$1
SRC=$2
base_run=$3
base_branch=$4
xhprof_url='http://www.lionsad.de/xhprof-kit/xhprof/xhprof_html/'

IFS='|'
cat $(dirname $0)/data/${base_run}-${base_branch}.data | while read data base_run new_run extra
do
  php $(dirname $0)/upload-run.php $KEY $base_run
  php $(dirname $0)/upload-run.php $KEY $new_run

  # Display XHProf Report again, but override URL
  php $(dirname $0)/xhprof-check.php "$base_run" "$new_run" "$extra"
  echo "$xhprof_url?run1=$base_run&run2=$new_run&source=$SRC&extra=$extra"
  echo
done

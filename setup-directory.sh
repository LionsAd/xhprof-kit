#!/bin/bash

BASE_DIR=$(dirname $0)

ln -sf "$BASE_DIR" xhprof-kit || { echo "Error: Could not setup xhprof-kit."; exit 1; }
ln -sf "$BASE_DIR/docroot/index-perf.php" index-perf.php || { echo "Error: Could not setup index-perf.php."; exit 1; }
ln -sf "$BASE_DIR/docroot/xhprof-kit.php" xhprof-kit.php || { echo "Error: Could not setup xhprof-kit.php."; exit 1; }
echo "XHProf-Kit was setup successfully." 1>&2

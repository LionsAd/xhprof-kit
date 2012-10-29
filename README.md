1. Copy index-perf.php to /var/www
2. Change .htaccess to refer to index-perf.php instead of index.php
3. Install drush registry rebuild project in drush folder.
4. Setup the necessary branches (core, core--issueno--cid for example)
5. Switch between them.

Use benchmark-branches.sh like:

$XHPROF-KIT-DIR/benchmark-branches.sh 508d81486ceb0 core core core--issueno--cid

Update the 508d81486ceb0 with whatever is the lowest baseline you find.

Hint: Update 1000 to 100 in benchmark-branch.sh to have faster runs. 1000 takes a while, but is very accurate.

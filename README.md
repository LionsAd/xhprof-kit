Note: Currently assumes xhprof to be present as symlink "xhprof" in xhprof-kit.
Note: Need to setup xhprof php extension before it works.

1. Symlink your xhprof-installation to the xhprof-kit dir.
   * ~/xhprof-kit$ ln -s /var/www/xhprof xhprof # assumes xhprof is installed in /var/www/xhprof.
2. Symlink index-perf.php from xhprof-kit dir to docroot of Drupal.
3. Install drush registry rebuild project in drush folder. (optional)
4. Setup the necessary branches (core, core--issueno--cid for example)
5. Switch between them.

Use benchmark-branches.sh like:

$XHPROF-KIT-DIR/benchmark-branches.sh 508d81486ceb0 core core core--issueno--cid

Update the 508d81486ceb0 with whatever is the lowest baseline you find.

Hint: Update 1000 to 100 in benchmark-branch.sh to have faster runs. 1000 takes a while, but is very accurate.

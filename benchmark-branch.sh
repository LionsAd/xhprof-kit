#!/bin/sh

branch=master
[ -n "$1" ] && branch=$1

drush cr 2>/dev/null
git checkout -q "$branch" --

settings_php=settings.default.php
if [ -f sites/default/settings."$branch".php ]
then
	settings_php=settings."$branch".php
fi

#@todo: Enable once we have proper scenarios support
#sudo ln -sf "$settings_php" sites/default/settings.php

drush cr 2>/dev/null
drush rr 2>/dev/null
$(dirname $0)/find-min-web.sh "$branch" 100 | tail -n 1

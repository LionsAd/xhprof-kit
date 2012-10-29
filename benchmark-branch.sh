#!/bin/sh

branch=master
[ -n "$1" ] && branch=$1

drush cc all 2>/dev/null
git checkout -q "$branch" --

settings_php=settings.default.php
if [ -f sites/default/settings."$branch".php ]
then
	settings_php=settings."$branch".php
fi

sudo ln -sf "$settings_php" sites/default/settings.php

sudo rm -rf sites/default/files/php
drush cc all 2>/dev/null
sudo rm -rf sites/default/files/php
drush rr 2>/dev/null
sudo rm -rf sites/default/files/php
$HOME/find-min-web.sh "$branch" 100 | tail -n 1

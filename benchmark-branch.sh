#!/bin/sh

BRANCH=master
URL="/"
LOOPS=100
[ -n "$1" ] && BRANCH=$1
[ -n "$2" ] && URL=$1

DRUPAL_VERSION="$(drush php-eval 'echo drush_drupal_major_version();')"
if [ ! $DRUPAL_VERSION ]; then
  echo "No Drupal installation found."
  return 1
fi

# Currently Drupal 7 and 8 are supported.
if [ $DRUPAL_VERSION -lt 7 -o $DRUPAL_VERSION -gt 8 ]; then
  echo "Drupal $DRUPAL_VERSION is not supported."
  return 1
fi

if [ $DRUPAL_VERSION -eq 8 ]; then
  drush cr 2>/dev/null
elif [ $DRUPAL_VERSION -eq 7 ]; then
  drush cc all 2>/dev/null
fi
git checkout -q "$BRANCH" --

settings_php=settings.default.php
if [ -f sites/default/settings."$BRANCH".php ]
then
	settings_php=settings."$BRANCH".php
fi

#@todo: Enable once we have proper scenarios support
#sudo ln -sf "$settings_php" sites/default/settings.php

if [ $DRUPAL_VERSION -eq 8 ]; then
  drush cr 2>/dev/null
  # This table gets bigger on every run and slows the PDOStatement.
  drush sqlq "TRUNCATE key_value_expire;"
elif [ $DRUPAL_VERSION -eq 7 ]; then
  drush cc all 2>/dev/null
  drush rr 2>/dev/null
fi
$(dirname $0)/find-min-web.sh "$BRANCH" "$LOOPS" "$URL" | tail -n 1

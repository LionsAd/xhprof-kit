#!/bin/bash

BASE_DIR=$(dirname $0)\

# Start subshell
(
cd $BASE_DIR

# Ensure submodules are present
git submodule init
git submodule update

# Ensure XHProfCLI is setup
echo "Setting up XHProfCLI, this step is optional, but needs composer."
cd XHProfCLI
composer install
)

# And setup the directory.
exec $BASE_DIR/setup-directory.sh

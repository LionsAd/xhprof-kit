#!/bin/bash

BASE_DIR=$(dirname $0)\

# Start subshell
(
cd $BASE_DIR

# Ensure submodules are present
git submodule init
git submodule update
)

# And setup the directory.
exec $BASE_DIR/setup-directory.sh

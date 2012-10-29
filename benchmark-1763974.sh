#!/bin/bash

for branch in "core-1763974--core" "core-1763974--126"
do
	$(dirname $0)/benchmark-vs-baseline.sh "$branch" 5089a55b70185 core
done

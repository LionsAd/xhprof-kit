#!/bin/bash

for branch in d8-core-1712444-core--rebase d8-core-1712444-twig-ra-opt--rebase d8-core-1712444-v1-ra-opt--rebase d8-core-1712444-v2-ra-opt--rebase d8-core-1712444-v3-ra-opt--rebase d8-core-1712444-twig-node--rebase
do
	$(dirname $0)/benchmark-vs-baseline.sh "$branch" 508939efc2f7b twig-ra-opt 5088f8fb987c9 core
done

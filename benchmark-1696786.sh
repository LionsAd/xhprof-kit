#!/bin/bash

for branch in core twig twig-autoescape
do
	$HOME/benchmark-vs-baseline.sh "$branch" 508ae2673e94f twig 508ae1d8981fd core
done

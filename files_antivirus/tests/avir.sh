#!/bin/bash


if [ -z $1 ]; then
	exit 56
fi

if [[ $1 =~ .*kitten.inf$  ]]; then
	echo "$1 : Kitten FOUND"
	exit 1
fi
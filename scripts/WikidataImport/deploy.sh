#!/bin/bash
#
# The deploy script for the WikidataImport code
#
# @param $1 (1 to run master, 0 to not run master)
# @param $2 (number of slaves to run, max 16, default 10)
#
# To run:
# ./deploy 1 15
#

#Find the path
ABSPATH=$(cd "$(dirname "$0")"; pwd)

#Start the master if we are asked to
if [ $1 -gt 0 ] ; then
	jstart -mem 1G -N WdIm.m php $ABSPATH/master.php
fi

#Get the limit (default 10)
limit=$2
if [[ -z $limit ]] ; then
	limit=10
fi
#If the limit is too high, lower it
if [ $limit -gt 16 ] ; then
	limit=16
else
	limit=$limit
fi

#Run the correct number of slaves
for (( c=01; c<=limit; c++ ))
do
	jstart -mem 350m -N WdIm.$c php $ABSPATH/slave.php
done
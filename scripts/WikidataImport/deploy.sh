#!/bin/bash
#
#The deploy script for the WikidataImport code
#

#Find the path
ABSPATH=$(cd "$(dirname "$0")"; pwd)

#Start the master
jstart -mem 350m -N WdIm.m php $ABSPATH/master.php

#Get the limit (default 10)
limit=$1
if [[ -z $limit ]] ; then
	limit=10
fi

#If the limit is too high, lower it
if [ $limit -gt 16 ] ; then
	limit=16
else
	limit=$limit
fi

#Run the slaves
for (( c=01; c<=limit; c++ ))
do
	jstart -mem 350m -N WdIm.$c php $ABSPATH/slave.php
done
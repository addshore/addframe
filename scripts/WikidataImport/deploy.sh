#!/bin/bash
#
#The deploy script for the WikidataImport code
#

BASEDIR=$(dirname $0)

jstart -mem 512m -N WdIm.m php $BASEDIR/master.php

jstart -mem 512m -N WdIm.01 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.02 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.03 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.04 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.05 php $BASEDIR/slave.php

jstart -mem 512m -N WdIm.06 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.07 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.08 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.09 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.10 php $BASEDIR/slave.php


jstart -mem 512m -N WdIm.11 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.12 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.13 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.14 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.15 php $BASEDIR/slave.php

jstart -mem 512m -N WdIm.16 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.17 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.18 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.19 php $BASEDIR/slave.php
jstart -mem 512m -N WdIm.20 php $BASEDIR/slave.php
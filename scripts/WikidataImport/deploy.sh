#!/bin/bash
#
#The deploy script for the WikidataImport code
#

ABSPATH=$(cd "$(dirname "$0")"; pwd)

jstart -mem 512m -N WdIm.m php $ABSPATH/master.php

jstart -mem 512m -N WdIm.01 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.02 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.03 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.04 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.05 php $ABSPATH/slave.php

jstart -mem 512m -N WdIm.06 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.07 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.08 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.09 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.10 php $ABSPATH/slave.php


jstart -mem 512m -N WdIm.11 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.12 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.13 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.14 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.15 php $ABSPATH/slave.php

jstart -mem 512m -N WdIm.16 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.17 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.18 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.19 php $ABSPATH/slave.php
jstart -mem 512m -N WdIm.20 php $ABSPATH/slave.php
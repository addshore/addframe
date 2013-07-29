#!/bin/bash
#The deploy script for wdimport.php

jstart -mem 512m -N wdiw.mm php ~/src/addwiki/scripts/wdimport.master.php

jstart -mem 512m -N wdiw.01 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.02 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.03 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.04 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.05 php ~/src/addwiki/scripts/wdimport.slave.php

jstart -mem 512m -N wdiw.06 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.07 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.08 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.09 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.10 php ~/src/addwiki/scripts/wdimport.slave.php


jstart -mem 512m -N wdiw.11 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.12 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.13 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.14 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.15 php ~/src/addwiki/scripts/wdimport.slave.php

jstart -mem 512m -N wdiw.16 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.17 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.18 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.19 php ~/src/addwiki/scripts/wdimport.slave.php
jstart -mem 512m -N wdiw.20 php ~/src/addwiki/scripts/wdimport.slave.php
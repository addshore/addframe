#!/bin/bash
#
# This will set correct permissions in the config directory
# All .cfgp will be 660
# All .cfg  will remain as they are
#

#Find the path
ABSPATH=$(cd "$(dirname "$0")"; pwd)

#Go to it
cd $ABSPATH/../configs

#Change the permissions
chmod 600 *.cfgp


#!/bin/bash


abpath=$(cd $(dirname $0) && pwd)

. /Users/playcrab/.bashrc

cd $abpath/data/db_deploy/ && ./db start *
pc restart 
ngc stop
ngc start

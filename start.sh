#!/bin/bash


abpath=$(cd $(dirname $0) && pwd)
cd $abpath/data/db_deploy/ && ./db start *
pc restart 
ngc start

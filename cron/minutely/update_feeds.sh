#!/usr/bin/env sh
# Immediately updates the JSON feeds by invoking the page directly with php
wget http://lmms.io/community?max_age=0 -O /dev/null
wget http://lmms.io/download?max_age=0 -O /dev/null


#!/bin/sh
# Created by User: soma Worker:陈鸿扬  Date: 16/11/9  Time: 17:01
#
# */1 * * * * /server/www/smart_restaurant/Shell/system.sh >> /server/www/smart_restaurant/Log/system.log 2>&1 &
# 0 0 10 * * /server/www/smart_restaurant/Shell/system.sh >> /server/www/smart_restaurant/Log/system.log 2>&1 &
#

. /etc/profile

curl http://smrt.host.com/Home/?/system/monthOpera/pw-6aecc1265f6bc211879f44dc7472c460/ &


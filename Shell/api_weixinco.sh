#!/bin/sh
#  Created by 陈鸿扬 on 16/8/19.
#
# */1 * * * * /server/www/SmartRestaurant/Shell/api_weixinco.sh >> /server/www/SmartRestaurant/Log/api_weixinco.log 2>&1 &
# */5 * * * * /server/www/SmartRestaurant/Shell/api_weixinco.sh >> /server/www/SmartRestaurant/Log/api_weixinco.log 2>&1 &
#
. /etc/profile
curl http://smrt.host.com/Api/?/weixinco/init/k-[PASS_WORD]/ &






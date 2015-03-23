#!/bin/sh
java -jar /srv/lib/jar/htmlcompressor-1.5.3.jar --compress-css --remove-surrounding-spaces html,head,title,meta,link,style,body,h1,p,img /srv/config/nginx/default/err/blank.src.html > /srv/config/nginx/default/err/blank.html
java -jar /srv/lib/jar/htmlcompressor-1.5.3.jar --compress-css --remove-surrounding-spaces html,head,title,meta,link,style,body,h1,p,img /srv/config/nginx/default/err/vm-502.src.html > /srv/config/nginx/default/err/vm-502.html

cd /srv/config/nginx/default/err
zopfli --i150 -c blank.html > blank.html.gz
zopfli --i150 -c vm-502.html > vm-502.html.gz
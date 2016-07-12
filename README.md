# server setup

As root:

    # cd /etc
    # ln -s /dev/null nginx-robots.conf

    # cp -a hosts /srv/etc/hosts
    # cat /srv/etc/hosts.template >> /srv/etc/hosts
    # mv hosts hosts.bak
    # ln -s /srv/etc/hosts

    # mv my.cnf my.cnf.bak
    # ln -s /srv/etc/my.cnf

    # mv nginx nginx.bak
    # ln -s /srv/etc/nginx

    # mv php-fpm.conf php-fpm.conf.bak
    # ln -s /srv/etc/php-fpm.conf

    # mv php.ini php.ini.bak
    # ln -s /srv/etc/php.ini

    # mv postfix postfix.bak
    # ln -s /srv/etc/postfix

    # cd /srv
    # mkdir -p /srv/incoming/tmp/nginx_upload_tmp
    # chown nginx /srv/incoming/tmp/nginx_upload_tmp
    # chmod 0700 /srv/incoming/tmp/nginx_upload_tmp

    # cd /srv
    # mkdir /srv/sites
    # git clone https://github.com/junemedia/dailysweeps.git /srv/sites/dailysweeps


# server setup

As root:

    # cd /etc
    # cp -a hosts hosts.bak
    # rm hosts
    # ln -s /srv/etc/hosts

    # cp -a my.cnf my.cnf.bak
    # rm my.cnf
    # ln -s /srv/etc/my.cnf

    # cp -a nginx nginx.bak
    # rm -rf nginx
    # ln -s /srv/etc/nginx

    # cp -a php-fpm.conf php-fpm.conf.bak
    # rm php-fpm.conf
    # ln -s /srv/etc/php-fpm.conf

    # cp -a php.ini php.ini.bak
    # rm php.ini
    # ln -s /srv/etc/php.ini

    # cp -a postfix postfix.bak
    # rm -rf postfix
    # ln -s /srv/etc/postfix

    # cd /srv
    # mkdir -p /srv/incoming/tmp/nginx_upload_tmp
    # chown nginx /srv/incoming/tmp/nginx_upload_tmp
    # chmod 0700 /srv/incoming/tmp/nginx_upload_tmp

    # ln -s /dev/null nginx-robots.conf

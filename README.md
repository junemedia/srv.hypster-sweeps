# server setup

## Basic setup:

    # yum update
    # yum install vim zsh git

    # adduser jshearer
    # passwd jshearer

    # groupadd jm-dev
    # usermod -a -G jm-dev jshearer

    # groupadd sudo
    # usermod -a -G sudo jshearer

Edit sudoers file and add line `%sudo ALL=(ALL)   ALL`

    # visudo

Make sure the CentOS 7 EPEL repository is installed

    # yum install epel-release

Install nginx and open up firewall:

    # yum install nginx
    # systemctl start nginx.service
    # systemctl enable nginx.service
    # firewall-cmd --zone=public --add-service=http
    # firewall-cmd --zone=public --add-service=http --permanent
    # firewall-cmd --zone=public --add-service=https
    # firewall-cmd --zone=public --add-service=https --permanent

Install php:

    # yum install php php-fpm php-mysql php-pear php-mbstring
    # systemctl start php-fpm.service
    # systemctl enable php-fpm.service

Install MySQL:

    # yum install http://www.percona.com/downloads/percona-release/redhat/0.1-3/percona-release-0.1-3.noarch.rpm
    # yum install Percona-Server-server-55
    # systemctl start mysql
    # systemctl enable mysql
    # mysql_secure_installation

Install memcached and fail2ban:

    # yum install php-pecl-memcache memcached
    # systemctl start memcached.service
    # systemctl enable memcached.service

    # yum install fail2ban
    # systemctl start fail2ban.service
    # systemctl enable fail2ban.service


Clone repository and create some dirs:

    # git clone https://github.com/junemedia/srv.git /srv
    # cd /srv/
    # mkdir incoming
    # mkdir -p incoming/tmp/nginx_upload_tmp
    # mkdir -p incoming/dailysweeps/reports
    # mkdir -p incoming/dailysweeps/pimg

Set permissions on temp upload dir:

    # chown nginx incoming/tmp/nginx_upload_tmp/
    # chmod 0700 incoming/tmp/nginx_upload_tmp/

Make `/etc` links:

    # cd /etc/
    # ln -s /dev/null nginx-robots.conf

    # cp -a hosts /srv/etc/hosts
    # cat /srv/etc/hosts.template >> /srv/etc/hosts
    # mv hosts hosts.bak
    # ln -s /srv/etc/hosts

    # mv my.cnf my.cnf.bak
    # ln -s /srv/etc/my.cnf
    # ln -s /srv/etc/my.cnf.d

    # mv nginx nginx.bak
    # ln -s /srv/etc/nginx

    # mv php-fpm.conf php-fpm.conf.bak
    # ln -s /srv/etc/php-fpm.conf

    # mv php.ini php.ini.bak
    # ln -s /srv/etc/php.ini

    # mv postfix postfix.bak
    # ln -s /srv/etc/postfix

    # cd /srv

    # cd /srv
    # mkdir /srv/sites
    # git clone https://github.com/junemedia/dailysweeps.git /srv/sites/dailysweeps

Create `log` directory

    # mkdir /srv/sites/log
    # chmod 0777 /srv/sites/log


## Notes

1. `php-fpm` runs as the `apache` user as set in `/srv/etc/php-fpm.conf`; assume this is left over from some earlier version of the code?

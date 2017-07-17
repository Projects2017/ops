#!/bin/bash
DEBIAN_FRONTEND=noninteractive apt-get update

# Install MySQL
MYSQL_PASSWORD=vagrant
echo "mysql-server-5.5 mysql-server/root_password password ${MYSQL_PASSWORD}
mysql-server-5.5 mysql-server/root_password seen true
mysql-server-5.5 mysql-server/root_password_again password ${MYSQL_PASSWORD}
mysql-server-5.5 mysql-server/root_password_again seen true
" | debconf-set-selections
DEBIAN_FRONTEND=noninteractive apt-get install -y --force-yes mysql-server

echo "phpmyadmin phpmyadmin/dbconfig-install boolean true
phpmyadmin phpmyadmin/mysql/admin-pass password ${MYSQL_PASSWORD}
phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | debconf-set-selections

# Import DB
MYSQL_CMD="mysql -u root -p${MYSQL_PASSWORD}"
echo "CREATE DATABASE pmdops;" | ${MYSQL_CMD}
${MYSQL_CMD} pmdops < /vagrant/vagrant/cache/pmdops.sql
echo "CREATE USER 'pmdops'@'localhost' IDENTIFIED BY 'maquis22';" | ${MYSQL_CMD}
echo "GRANT ALL ON pmdops.* TO 'pmdops'@'localhost';" | ${MYSQL_CMD}

# DEBIAN_FRONTEND=noninteractive apt-get install -y --force-yes openssh-server
DEBIAN_FRONTEND=noninteractive apt-get install -y --force-yes apache2 libapache2-mod-php5 phpmyadmin php5-curl

cp /vagrant/vagrant/pmdops.dev.conf /etc/apache2/sites-available/pmdops.dev.conf

a2enmod rewrite
a2ensite pmdops.dev.conf
a2dissite default
service apache2 restart

# Install a basic mailserver
echo "postfix postfix/mailname string clpanel.dev" | debconf-set-selections
echo "postfix postfix/main_mailer_type string 'Local'" | debconf-set-selections
DEBIAN_FRONTEND=noninteractive apt-get install -y postfix
echo "/^.*$/ vagrant" > /etc/postfix/canonical-redirect
postconf -e "canonical_maps = regexp:/etc/postfix/canonical-redirect"
service postfix restart

# output some instructions on use of VM
cat /vagrant/vagrant/docs/VAGRANT.txt


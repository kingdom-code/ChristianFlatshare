# ChristianFlatshare.org

CFS is a not-for-profit organisation which recieves minimal income from Google ads and donations, which in a good month cover hosting costs.

CFS has been open sourced under the GPLv3 to allow paritpation from the technical members of the community who wish to help develop the service.

There is a list of projects maintained in [Github](https://github.com/ChristianFlatshare/ChristianFlatshare/projects "CFS projects")


## Installation

### 1. Add the document root to your Apache2 config:
VirtualHost ServerName christianflatshare.org
Document root /srv/www/christianflatshare.org


### 2. Deloy the site file to your document root
/srv/www/christianflatshare.org


### 3. Install PHP 7.2
sudo add-apt-repository ppa:ondrej/php

apt-get install python-software-properties

sudo apt-get install php7.2

sudo apt-get install php-pear php7.2-curl php7.2-dev php7.2-gd php7.2-mbstring php7.2-zip php7.2-mysql php7.2-xml libapache2-mod-php


### 4. Recreate the database and seed data
mysql> CREATE DATABASE cfs

bash$ mysqladmin -u db_user -p  create cfs

bash$ mysql -u db_user cfs < install/database/db_user.sql


### 5. Update /etc/hosts to include:
127.0.0.1   localhost christianflatshare.org www.christianflatshare.org


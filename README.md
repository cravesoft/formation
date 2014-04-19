# Overview

The Formation installation consists of setting up the following components:

1. Packages / Dependencies
2. Database
3. Formation
4. Apache

# 1. Packages / Dependencies

`sudo` is not installed on Debian by default. Make sure your system is
up-to-date and install it.

    # run as root!
    apt-get update -y
    apt-get upgrade -y
    apt-get install sudo -y

**Note:**
During this installation some files will need to be edited manually.
If you are familiar with vim set it as default editor with the commands below.
If you are not familiar with vim please skip this and keep using the default editor.

    # Install vim and set as default editor
    sudo apt-get install -y vim
    sudo update-alternatives --set editor /usr/bin/vim.basic

Install the required packages:

    sudo apt-get install -y git-core curl ruby yui-compressor php5 php5-mcrypt php5-json php5-ldap

Install the sass Gem:

    sudo gem install sass

# 2. Database

    # Install the database packages
    sudo apt-get install -y mysql-server mysql-client libmysqlclient-dev

    # Pick a database root password (can be anything), type it and press enter
    # Retype the database root password and press enter

    # Secure your installation.
    sudo mysql_secure_installation

    # Login to MySQL
    mysql -u root -p

    # Type the database root password

    # Create a user for Formation
    # do not type the 'mysql>', this is part of the prompt
    # change $password in the command below to a real password you pick
    mysql> CREATE USER 'formation'@'localhost' IDENTIFIED BY '$password';

    # Create the Formation production database
    mysql> CREATE DATABASE IF NOT EXISTS `formation_production` DEFAULT CHARACTER SET `utf8` COLLATE `utf8_unicode_ci`;

    # Grant the Formation user necessary permissions on the table.
    mysql> GRANT SELECT, LOCK TABLES, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER ON `formation_production`.* TO 'formation'@'localhost';

    # Quit the database session
    mysql> \q

    # Try connecting to the new database with the new user
    sudo -u formation -H mysql -u formation -p -D formation_production

    # Type the password you replaced $password with earlier

    # You should now see a 'mysql>' prompt

    # Quit the database session
    mysql> \q

    # You are done installing the database and can go back to the rest of the installation.

# 3. Formation

    # We'll install Formation into your home directory
    cd $HOME

## Clone the Source

    # Clone Formation repository
    git clone https://github.com/cravesoft/formation.git

    # Go to formation dir
    cd formation

## Configure it

    # Copy the example Formation config
    cp config/formation.yml.example config/formation.yml

    # Make sure to change "localhost" to the fully-qualified domain name of your
    # host serving Formation where necessary
    editor config/formation.yml

    # Make sure to update username/password to match your setup.
    # You only need to adapt the production settings (first part).
    # If you followed the database guide then please do as follows:
    # Change 'database:password' with the value you have given to $password
    # You can keep the double quotes around the password

## Install back-end dependencies

    # Downloading the composer executable
    curl -sS https://getcomposer.org/installer | php

    # Resolve and download dependencies
    ./composer.phar install

## Install front-end dependencies

    # Instal Node Version Manager
    curl https://raw.github.com/creationix/nvm/master/install.sh | sh

**Note:**
The script clones the nvm repository to `~/.nvm` and adds the source line to the profile (`~/.bash_profile` or `~/.profile`).

    # Download, compile, and install the latest v0.10.x release of node
    nvm install 0.10

    # Use the installed node version
    nvm use 0.10

    # Install bower globally
    npm install -g bower

    # Resolve and download dependencies
    bower install

## Initialize Database and Activate Advanced Features

    # Process the schema and create it directly on Entity Storate Connection
    php vendor/bin/doctrine orm:schema-tool:create

    cd script
    recode iso-8859-1..utf-8 users.csv

    # Insert the users in the database
    php update.php
    
**Debug:**
    # Fill the database
    php fill.php

## Compile assets

# 4. Apache

## Installation
    # Install the server package
    sudo apt-get install -y apache2 libapache2-mod-php5

    # Formation uses a front controller to route requests, module rewrite needs to be enabled:
    sudo a2enmod rewrite

    # Enable module ssl
    sudo a2enmod ssl

    cd $HOME
    
    sudo chmod 644 formation/.htaccess
    sudo chgrp -R www-data formation
    sudo chmod -R 750 formation
    sudo chmod g+s formation

    # Make sure Formation can write to the log/, assets/css/ and cache/ directories
    sudo chown -R www-data formation/log/
    sudo chown -R www-data formation/cache/
    sudo chown -R www-data formation/assets/css
    sudo chmod -R u+rwX formation/log/
    sudo chmod -R u+rwX formation/cache/
    sudo chmod -R u+rwX formation/assets/css

## Site Configuration

Copy and enable an example site config:

    sudo cp apache2/formation.conf /etc/apache2/sites-available/formation.conf
    sudo ln -s /etc/apache2/sites-available/formation.conf /etc/apache2/sites-enabled/formation.conf

Make sure to edit the config file to match your setup:

    # Change ServerName to the fully-qualified
    # domain name of your host serving Formation.
    sudo editor /etc/apache2/sites-available/formation.conf

libapache2-mod-php5
php5-json
php5-ldap
/etc/apache2/conf-available/silex.conf
/etc/apache2/conf-available/phpmyadmin.conf
recode iso-8859-1..utf-8 users.csv
sudo make-ssl-cert /usr/share/ssl-cert/ssleay.cnf /etc/ssl/private/localhost.pem
sudo a2ensite ssl

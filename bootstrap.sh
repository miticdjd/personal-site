#!/usr/bin/env bash

# Install apache
apt-get update
apt-get install -y apache2

# Setup root directory
sudo rm -rf /var/www
sudo ln -fs /vagrant /var/www 
# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  
  config.vm.box = "personal-site"
  config.vm.box_url = "/home/dragan/virtualization/boxes/precise64.box"
  config.vm.network :private_network, ip: "192.168.30.10"

  # Install required packages
  web.vm.provision :shell, :inline => "apt-get update"
  web.vm.provision :shell, :inline => "apt-get install -y apache2"
  web.vm.provision :shell, :inline => "ln -fs /vagrant/web /var/www"

end

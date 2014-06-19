# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  
  config.vm.box = "personal-site"
  config.vm.box_url = "/home/dragan/virtualization/boxes/precise64.box"
  config.vm.network :private_network, ip: "192.168.30.10"

  # Install required packages
  config.vm.provision :shell, :path => "bootstrap.sh"

end

# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "puppet-debian-73-x64-virtualbox-nocm"
  config.vm.box_url = "http://puppet-vagrant-boxes.puppetlabs.com/debian-73-x64-virtualbox-nocm.box"
  config.vm.network :private_network, ip: "192.168.56.35"
  config.vm.synced_folder ".", "/var/www/vhosts/pmddealer.com/httpdocs"
  config.vm.synced_folder ".", "/vagrant"
  config.vm.provision "shell", path: "vagrant/install.sh"
end

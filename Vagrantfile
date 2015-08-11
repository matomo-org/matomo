VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.hostname = "piwik"
  config.vm.box = "trusty64"

  config.vm.synced_folder ".", "/vagrant", type: "nfs"

  config.ssh.forward_agent = true

  config.vm.network :private_network, ip: "192.168.33.10"

  config.vm.provision :shell, path: "misc/vagrant/bootstrap.sh"

  config.vm.provider :virtualbox do |vb|
    vb.customize ["modifyvm", :id, "--memory", "2048"]
  end

end

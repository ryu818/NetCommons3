# Configure your sources.list to the nearby mirror
execute "update sources.list" do
  command "sed -i 's/us.archive/ja.archive/g' /etc/apt/sources.list"
end

execute "apt-get update" do
  command "apt-get update"
end

# Install packages necessary for installation
%w{python-software-properties}.each do |pkg|
  package pkg do
    action [:install, :upgrade]
  end
end

# Add extra repos
execute "add latest php5 repository" do
  command "apt-get remove php5*; add-apt-repository -y ppa:ondrej/php5; apt-get update"
  not_if { ::File.exists?("/etc/apt/sources.list.d/ondrej-php5-#{node[:lsb][:codename]}.list")}
end

execute "add latest emacs repository" do
  command "apt-get remove emacs*; add-apt-repository -y ppa:cassou/emacs; apt-get update"
  not_if { ::File.exists?("/etc/apt/sources.list.d/cassou-emacs-#{node[:lsb][:codename]}.list")}
end

# Install packages necessary for this project
packages = %w{
  php5 php5-mysql php5-pgsql php5-curl php5-cli php5-fpm php5-imagick php5-xdebug php5-mcrypt php-pear
  git subversion nginx
  mysql-server postgresql curl imagemagick
  lv zsh tree axel expect make
  global w3m aspell exuberant-ctags wamerican-huge stunnel4 npm
  emacs24 emacs-goodies-el debian-el gettext-el
  iftop iotop iperf nethogs sysstat
  ruby1.9.1 ruby1.9.1-dev libnotify-bin
}

packages.each do |pkg|
  package pkg do
    action [:install, :upgrade]
    version node.default[:versions][pkg] if node.default[:versions][pkg].kind_of? String
  end
end

execute "install bundler" do
  command "gem i bundler"
end

execute "install gem packages" do
  command "cd /vagrant_data; bundle install"
end

execute "install npm packages" do
  command "npm -g install jshint"
end

execute "install phpunit" do
  command "pear config-set auto_discover 1; pear install pear.phpunit.de/PHPUnit"
  not_if { ::File.exists?("/usr/bin/phpunit")}
end

execute "install composer" do
  command "cd /vagrant_data; curl -sS https://getcomposer.org/installer | php; php composer.phar install; mv composer.phar /usr/local/bin/composer"
  not_if { ::File.exists?("/usr/local/bin/composer")}
end

template "/etc/nginx/conf.d/php-fpm.conf" do
  mode 0644
  source "php-fpm.conf.erb"
end

template "/etc/php5/fpm/php.ini" do
  source "php.ini.erb"
  mode 0644
  variables(:directives => node[:php][:directives])
end

template "/etc/php5/cli/php.ini" do
  source "php.ini.erb"
  mode 0644
  variables(:directives => node[:php][:directives])
end

service 'apache2' do
  action :stop
end

%w{mysql postgresql php5-fpm nginx}.each do |service_name|
    service service_name do
      action [:start, :restart]
    end
end

group "www-data" do
  action :modify
  members "vagrant"
  append true
end

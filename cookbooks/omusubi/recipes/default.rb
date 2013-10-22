# Configure your sources.list to the nearby mirror
execute "sources.list update" do
  command "sed -i 's/us.archive/ja.archive/g' /etc/apt/sources.list"
end

execute "add latest php5 repository" do
  command "apt-get remove php5*; add-apt-repository ppa:ondrej/php5"
  not_if { ::File.exists?("/etc/apt/sources.list.d/ondrej-php5-#{node[:lsb][:codename]}.list")}
end

execute "apt-get update" do
  command "apt-get update"
end

packages = %w{
  php5 php5-mysql php5-pgsql php5-curl php5-cli php5-fpm php5-imagick php5-xdebug php-pear
  git subversion nginx
  mysql-server postgresql curl imagemagick
}

packages.each do |pkg|
  package pkg do
    action [:install, :upgrade]
    if node.default[:versions][pkg].kind_of? String
      version node.default[:versions][pkg]
    end
  end
end

execute "phpunit-install" do
  command "pear config-set auto_discover 1; pear install pear.phpunit.de/PHPUnit"
  not_if { ::File.exists?("/usr/bin/phpunit")}
end

execute "composer-install" do
  command "curl -sS https://getcomposer.org/installer | php ;mv composer.phar /usr/local/bin/composer"
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

service 'apache2' do
  action :stop
end

%w{mysql postgresql php5-fpm nginx}.each do |service_name|
    service service_name do
      action [:start, :restart]
    end
end

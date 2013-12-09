#ignore /^lib\/Nc\/Vendor/
#ignore %r{^lib/Nc/Vendor/}

guard :shell do
  watch(%r{^.+Test\.php$}) do |m|
    n "#{m[0]} Changed"

    # Init database
    `mysql -uroot -proot -e 'DROP DATABASE IF EXISTS cakephp_test;'`
    `mysql -uroot -proot -e 'DROP DATABASE IF EXISTS cakephp_test2;'`
    `mysql -uroot -proot -e 'DROP DATABASE IF EXISTS cakephp_test3;'`
    `mysql -uroot -proot -e 'CREATE DATABASE cakephp_test;'`
    `mysql -uroot -proot -e 'CREATE DATABASE cakephp_test2;'`
    `mysql -uroot -proot -e 'CREATE DATABASE cakephp_test3;'`
    `cp app/Config/database.php app/Config/database.php.orig`
    `cp app/Config/database.php.test app/Config/database.php`

    # Invoke tests
    target = m[0].gsub /lib\/Nc\/Test\/Case\/([\w\/]+)Test\.php$/, '\1'
    n "app/Console/cake test app #{target} --stderr"
    `app/Console/cake test app #{target} --stderr`
    `cp app/Config/database.php.orig app/Config/database.php`
    `rm -f app/Config/database.php.orig`
  end
end

# Installed by guard-phpcs
guard :phpcs, :standard => 'CakePHP' do
  watch(%r{.*\.php$})
end

# Installed by guard-phpmd
guard :phpmd, :rules => 'ruleset/phpmd.xml' do
  watch(%r{.*\.php$})
end

# guard 'phpunit', :cli => '--colors', :all_on_start => false do
#   watch(%r{^.+Test\.php$})
# end

# Installed by guard-phpcs
guard 'phpcs', :standard => 'CakePHP' do
  watch(%r{.*\.php$})
end

# Installed by guard-phpmd
guard 'phpmd', :rules => 'ruleset/phpmd.xml' do
  watch(%r{.*\.php$})
end

guard 'phpunit', :cli => '--colors' do
  watch(%r{^.+Test\.php$})
end

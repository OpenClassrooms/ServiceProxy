{ pkgs, lib, config, inputs, ... }:

{
 languages.php = {
     enable = true;
     version = lib.mkDefault "8.2";
     extensions = [ "xdebug" ];

     ini = ''
       memory_limit = -1
       opcache.enable = 1
       opcache.revalidate_freq = 0
       opcache.validate_timestamps = 1
       opcache.max_accelerated_files = 30000
       opcache.memory_consumption = 256M
       opcache.interned_strings_buffer = 20
       realpath_cache_ttl = 3600
       xdebug.idekey = "PHPSTORM"
       xdebug.start_with_request = "yes"
       zend.assertions = 1
       date.timezone = "Europe/Paris"
       xdebug.output_dir = ".devenv/state/xdebug"
       xdebug.mode = "off"
     '';
   };
}

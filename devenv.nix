{ pkgs, lib, config, inputs, ... }:

{
  languages.php = {
    enable = true;

    ini = ''
      memory_limit = -1
    '';
  };

  pre-commit.hooks = {
    phpstan = {
        enable = true;
        entry = "vendor/phpstan/phpstan/phpstan analyze -c phpstan.neon";
        pass_filenames = false;
    };
    phpunit = {
        enable = true;
        entry = "vendor/phpunit/phpunit/phpunit";
        pass_filenames = false;
      };
    };
}

#!/usr/bin/env bash
cd "$(dirname "$0")"
echo "$(pwd)"
chmod 777 -R vendor/composer
chmod 777 -R vendor/autoload.php
COMPOSER_HOME="hostComposerHome/" php composer.phar dump-autoload --optimize 2>&1 


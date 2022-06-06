#!/bin/env just --justfile
run:
    php -S localhost:8100 -t {{justfile_directory()}}/public

update-i18n:
    tx pull -a --minimum-perc 85
    {{justfile_directory()}}/bin/console lint:xliff -- translations/messages.*.xlf
    bash -e {{justfile_directory()}}/dev/update_locale

update-scss:
    sass --no-source-map {{justfile_directory()}}/public/css/style.scss {{justfile_directory()}}/public/css/style.min.css

update-deps:
    composer update
    {{justfile_directory()}}/bin/console lint:twig
    {{justfile_directory()}}/bin/console lint:container
    {{justfile_directory()}}/bin/console cache:warmup
    composer recipes

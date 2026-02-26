# Acquia Drupal Starter toolkit
[![Build Status](https://github.com/acquia/acquia-cms-starterkit/actions/workflows/acms.yml/badge.svg)](https://github.com/acquia/acquia-cms-starterkit)

The official command-line tool for downloading and building drupal site with Acquia Drupal Starter Kit modules for different use cases.
Acquia Drupal Starter Kit cli tool helps you to quickly setup the Drupal sites based on your needs. Currently, the cli tool
will present you the following use cases:

| Name  | Description |
| ------------- | ------------- |
| Acquia Drupal Enterprise Low-code Starter kit | The low-code starter kit will install starter kit with Site Studio and a UIkit. It provides drag and drop content authoring and low-code site building. An optional content model can be added in the installation process.  |
| Acquia Drupal Community Starter kit | The community starter kit will get installed. An optional content model can be added in the installation process.  |
| Acquia Drupal Headless Starter kit | The headless starter kit preconfigures Drupal for serving structured, RESTful content to 3rd party content displays such as mobile apps, smart displays and frontend driven websites (e.g. React or Next.js).  |
- You can add your own cases and define the modules/themes that needs to be installed & enabled.
- You can edit/remove the default use cases provided by this cli tool.

# Installation
Composer is the recommended way to download this tool. In order to download this tool, run the below composer command:
```
composer require acquia/acquia-cms-starterkit
```

# Usage

Run the following command to to set up site:
```
./vendor/bin/acms acms:install
```

###### Note: Starter kit enable modules in bulk during installation which required minimum PHP memory limit to be 256M.

Check memory_limit using command `php -i | grep "memory_limit"`,If php memory limit is less than 256 then locate php.ini file and update memory_limit to 256

# Acquia CMS Starter toolkit
[![Build Status](https://github.com/acquia/acquia-cms-starterkit/actions/workflows/acms.yml/badge.svg)](https://github.com/acquia/acquia-cms-starterkit)

The official command-line tool for downloading and building drupal site with acquia_cms modules for different use cases.
Acquia CMS starterkit cli tool helps you to quickly setup the Drupal sites based on your needs. Currently, the cli tool
will present you the following use cases:

| Name  | Description |
| ------------- | ------------- |
| Acquia CMS Enterprise Low-code | The low-code starter kit will install Acquia CMS with Site Studio and a UIkit. It provides drag and drop content authoring and low-code site building. An optional content model can be added in the installation process.  |
| Acquia CMS community  | The community starter kit will install Acquia CMS. An optional content model can be added in the installation process.  |
| Acquia CMS Headless  | The headless starter kit preconfigures Drupal for serving structured, RESTful content to 3rd party content displays such as mobile apps, smart displays and frontend driven websites (e.g. React or Next.js).  |
- You can add your own cases and define the modules/themes that needs to be installed & enabled.
- You can edit/remove the default use cases provided by this cli tool.

# Installation
Composer is the recommended way to download this tool. In order to download this tool, run the below composer command:

```
composer config repositories.acquia_cms_starterkit vcs 'git@github.com:acquia/acquia-cms-starterkit.git'
```

OR optionally, you can add the following code under repository section in your project root composer.json file:

```
"acquia_cms_starterkit": {
  "type": "vcs",
  "url": "git@github.com:acquia/acquia-cms-starterkit.git"
},
```
After the composer repository is added, run the below composer command to download the tool:

```
composer require acquia/acquia-cms-starterkit:dev-develop
```

# Usage

Run the following command to to set up site:
```
./vendor/bin/acms acms:install
```

# Acquia CMS Starter toolkit
The official command-line tool for downloading and building drupal site with acquia_cms modules for different use cases.
Acquia CMS starterkit cli tool helps you to quickly setup the Drupal sites based on your needs. Currently, the cli tool
will present you the following use cases:

| Name  | Description |
| ------------- | ------------- |
| Acquia CMS Demo  | Low-code demonstration of ACMS with default content.  |
| Acquia CMS Low Code  | Acquia CMS with Site Studio but no content opinion.  |
| Acquia CMS Standard  | Acquia CMS with a starter content model, but no demo content, classic custom themes.  |
| Acquia CMS Minimal  | Acquia CMS in a blank slate, ideal for custom PS.  |
| Acquia CMS Headless  | Acquia CMS with headless functionality.  |
- You can add your own cases and define the modules/themes that needs to be installed & enabled.
- You can edit/remove the default use cases provided by this cli tool.

## Prerequisites
Created composer based drupal project using below command and use this tool to choose your use case.
```
composer create-project --no-interaction acquia/drupal-recommended-project
```

## Installation
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

## Usage

Run the following command to print hello world:
```
./vendor/bin/acms acms:install
```

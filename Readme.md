# About

A small tool to automate tasks related to projects with monolithic repos

# Installation

via composer (please add this repo as package source)

```bash
composer require kaystrobach/releasy @dev
```

via git

```bash
git clone git@github.com:kaystrobach/Releasy.git
```

# Usage

```bash
vendor/bin/releasy <command>
./releasy <command>
```

We assume a directory structure like:

```text

composer.json           -> Development collection
Changelog.md            -> Development collection
DistributionPackages
    typo3_extension     -> extension in the development collection
        composer.json
        ext_emconf.php
```

# Commands

```bash
Available commands:
  help                        Displays help for a command
  list                        Lists commands
 composer
  composer:setversion         Sets a new version of a TYPO3 extension.
 git
  git:tag                     Creates a new release and pushes it to the server
 phar
  phar:create                 Creates a phar
 release
  release:create              Creates a new release and pushes it to the server
  release:updatechangelog     Updates the changelog
 typo3
  typo3:extension:list        
  typo3:extension:package     Package an extension
  typo3:extension:setversion  Sets a new version of a TYPO3 extension.
```

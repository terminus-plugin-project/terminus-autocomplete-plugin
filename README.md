# Terminus Autocomplete Plugin

Version 2.x

[![Terminus v2.x Compatible](https://img.shields.io/badge/terminus-v2.x-green.svg)](https://github.com/terminus-plugin-project/terminus-autocomplete-plugin/tree/2.x)

A Terminus plugin to help provide tab completion for commands.

## Usage:
```
$ terminus autocomplete:install
```
This will check for requirements and provide final install instructions.
```
$ terminus autocomplete:check
```
This will check for requirements only.  See Requirements section below.
```
$ terminus autocomplete:test
```
This will print a message which explains how to test if tab completion is working.
```
$ terminus autocomplete:update
```
This will update the autocomplete commands and should be executed after every new Terminus release.

Learn more about [Terminus](https://pantheon.io/docs/terminus/) and [Terminus Plugins](https://pantheon.io/docs/terminus/plugins/).

## Installation:

To install this plugin using Terminus 3:
```
terminus self:plugin:install terminus-plugin-project/terminus-autocomplete-plugin
```

On older versions of Terminus:
```
mkdir -p ~/.terminus/plugins
composer create-project --no-dev -d ~/.terminus/plugins terminus-plugin-project/terminus-autocomplete-plugin
```
For help installing, see [Manage Plugins](https://pantheon.io/docs/terminus/plugins/).


## Requirements:

- [brew](https://brew.sh) (Mac only)
- [bash-completion](https://formulae.brew.sh/formula/bash-completion)
- [composer](https://getcomposer.org/download/)
- [cgr](https://github.com/consolidation/cgr)
- [symfony-autocomplete](https://github.com/bamarni/symfony-console-autocomplete)

These requirements will be checked before installation can be completed.
If you don't have all the requirements installed, don't worry too much.
You will be provided with instructions to guide you through the process.

## Help:

Run `terminus help autocomplete:[install|check|test|update]` for help.

## Credit:

Plugin inspired by [Autocompletion for Terminus 1.x on MacOS](https://wikihub.berkeley.edu/display/drupal/Autocompletion+for+Terminus+1.x+on+MacOS).

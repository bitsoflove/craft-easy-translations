# Easy Translations

Plugin to manage translations. Export and import functionality.

## Requirements

Craft 3

## Installation

To install the plugin, follow these instructions.

0. Update your composer.json file and add the repo as a VCS

```
  "repositories":
    {
        "type": "vcs",
        "url": "https://github.com/bitsoflove/craft-translator:dev-develop"
    }
```

1. Open your terminal and go to your Craft project:

```shell
cd /path/to/project
```

2. Tell Composer to install the plugin

```shell
composer require bitsoflove/craft-translator
```

3. Install the plugin in craft

In the Control Panel, go to Settings → Plugins and click the “Install” button for Translation.

**or**

```shell
php craft plugin/install craft-translator
```

## Usage

### *Table*
The table contains two columns. The first is a list of all the sources, the second are the translations (if they exist). The translations are based on the selected language in the top left multi-site menu. Changing site will result in the translations changing to the language of the selected site.

### *Sidebar*
Depending on the what rights where given to the user, different content will be visible here.

On the left of the page there is a list of all the template files. Selecting a template will result in only the translations contained in that file to be shown.

Beneath the template paths, there's a list of categories. These include all the translations contained in static translation files such as /translations/en/site.php

### *Logic*
By default the translations will first be extracted from the static translation files. Changing and saving some translations will result in **only** the changed translations to be saved to the database. The database translations have priority over those in the static files. This means that admins have full control over translations without having to alter any file or code.

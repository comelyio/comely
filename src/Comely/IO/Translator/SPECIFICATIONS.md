# Translator Specifications

Compiled cached translation file for a single language may scale up to 100 to 200KBs or even more, which is a serious 
unnecessary overhead on HTTP request served by your web server. Translator component solves this problem by dividing 
translations in groups and then loading/storing them separately.

## Translator Instance

Translator is a singleton class which means only a single instance of this class exists, and that is the most we will 
need.

```php
use Comely\IO\Translator\Translator;

$translator = Translator::getInstance();
```

## Languages

A language is represented by a directory with in parent directory that is specified to Translator component.

```php
use Comely\IO\FileSystem\Disk\Directory;

$translator->directory(new Directory('/home/user/domain.com/app/translations'));
```

* As per above example, `translations` directory should contain sub-directories i.e. `en`, `ur`, `lt` where each 
directory represents an individual language.
* Language names MUST BE either 2 alphabet name (e.g. `en`) OR 4 alphabet having "-" after first 2 alphabet (e.g. `en-us`)
* Language directories MUST BE in lowercase.

### Files

Translator component divides all your translations in 4 groups, each group is a YAML file (ending in .yml extension). 
These groups are:

Name | File | Description
--- | --- | ---
Dictionary | dictionary.yml | Should contain translations for words, actions and other vocabulary
Messages | messages.yml | Should contain error/success messages
Sitemap | sitemap.yml | Should contain links names and page titles
Misc | misc.yml | Should contain any other miscellaneous translations

* Files must be named in all lowercase, matching exact giving names
* Files must have `.yml` extensions


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
* Language names MUST BE either 2 alphabet name (e.g. `en`) OR 4 alphabet having `-` after first 2 alphabet (e.g. `en-us`)
* Language directories MUST BE in lowercase.

## Files

Translator component divides all your translations in 4 groups, each group is a YAML file (ending in .yml extension). 
These groups are:

Name | File | Description
--- | --- | ---
Dictionary | dictionary.yml | Should contain translations for words, actions and other vocabulary
Messages | messages.yml | Should contain error/success messages
Sitemap | sitemap.yml | Should contain links names and page titles
Misc | misc.yml | Should contain any other miscellaneous translations

* Files MUST BE named in all lowercase and match exact prescribed names
* Files MUST have `.yml` extensions

## YAML files and translation keys

*sample `en-us/dictionary.yml`*
```yaml
email: E-mail
email-addr: E-mail address
username: Username
password: Password

profile:
  name:
    complete: Full name
    first: First name
    last: Last name
  address:
    line1: Address line 1
    line2: ""
    country: Country
    city: City
    state: State/Province
    zip: Postal/ZIP code

```

As per above example, key `profile.name.first` will retrieve value `First name` for language `en-us`

--

* All translation keys MUST ONLY have alphabets, numbers and `_`,`-` or `.` characters.
* All translation keys SHOULD BE in lowercase alphabets. 

## Load Files

* Call `load()` method and chain to select which files to include.
* Language files will be complied when first translation is requested.
* Method `load()` SHOULD BE called before any translation is requested by your app.
* Method `load()` SHOULD NOT BE called more than once as it will reset all internally stored instances of compile 
Language files causing re-compile of languages on next translation request which comes with a overhead.

```php
$translator->load()
  ->dictionary()
  ->messages()
  ->sitemap()
  ->misc();
```
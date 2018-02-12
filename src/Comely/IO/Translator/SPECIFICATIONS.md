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
# Translator Specifications

## Translator Instance

Translator is a singleton class which means only a single instance of this class exists, and that is the most we will 
need.

```php
use Comely\IO\Translator\Translator;

$translator = Translator::getInstance();
```
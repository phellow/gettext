## Install via Composer

Add the following dependency to your project's _composer.json_ file:

```json
{
    "require": {
        "phellow/gettext": "1.*"
    }
}
```

## Usage

To translate texts via gettext, you have to set the translator first:

```php
$translator = new \Phellow\Gettext\GettextTranslator('path/to/locales', 'gettextDomain');

$intl = new \Phellow\Intl\IntlService('en_US');
$intl->setTranslator($translator);

$text = $intl->_('translate this');
$text = $intl->_n('one', 'more', 2);
```

## License

The MIT license.
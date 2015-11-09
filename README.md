# IDProvider
IDProvider is an extension to MediaWiki that provides (unique) IDs using different ID algorithms.

## Requirements
- MediaWiki 1.19+
- PHP 5.3+

## Installation
The recommended way to install this extension is by using [Composer][composer]. Just add the
following to the MediaWiki `composer.json` file and run the ``php composer.phar install/update`` command.

```json
{
	"require": {
		"gesinn-it/id-provider": "~0.5.2"
	}
}
```

## Usage
There are three ID Provider available at the moment:
* Increment (with or without prefix), e.g. Issue_00005
* Random UUID, e.g. 550e8400-e29b-11d4-a716-446655440000
* Random FakeID, e.g. HJ78tz

### Through the API
#### idprovider-increment
```
api.php?action=idprovider-increment
api.php?action=idprovider-increment&prefix=Issue_&padding=8&skipUniqueTest=true
```
#### idprovider-random
```
api.php?action=idprovider-random
api.php?action=idprovider-increment&type=fakeid&prefix=Issue_&skipUniqueTest=true
```

### Through a parser function
Please note that every time the parser function is executed, it will generate ID.
This is most likely not what you want!

The use of the parser function is useful for auto setting unique Semantic Forms page titles.
Please note that you should avoid spaces within the parser functions if you use it as a parameter of a Semantic Form info tag.
#### idprovider-increment
```
{{#idprovider-increment:}}
{{#idprovider-increment:Issue_}}
{{#idprovider-increment:
  |prefix=Issue_
  |padding=5
  |skipUniqueTest=true
}}
```
#### idprovider-random
```
{{#idprovider-random:}}
{{#idprovider-random:uuid}}
{{#idprovider-random:fakeid}}
{{#idprovider-random:
  |type=uuid
  |skipUniqueTest=true
}}
```

### Within PHP
```php
IDProviderFunctions::getIncrement($prefix, $padding, $start, $skipUniqueTest);
IDProviderFunctions::getIncrement('Issue_', 8);
```

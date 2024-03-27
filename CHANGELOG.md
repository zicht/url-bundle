# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

## 7.3.1 - 2024-03-27
### Fixed
- Replaced deprecated `Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST` by `::MAIN_REQUEST`

## 7.3.0 - 2024-01-05
### Added
- Forward merge of v5.3.0: Added the `\Zicht\Bundle\UrlBundle\Url\Params\TranslatedUriParser` and a
  `\Zicht\Bundle\UrlBundle\Url\Params\ParamTranslator` as services so a custom implementation in every
  project is no longer necessary.
- Forward merge of v5.3.0Also added `url_params` config so you can set a custom `param_separator`,
  `key_value_separator` and `value_separator` in `config/packages/zicht_url.yaml` (instead of setting
  these trough arguments in `config/services.yaml`).

## 7.2.5 - 2023-11-30
### Fixed
- Doctrine compatability

## 7.2.4 - 2023-10-24
### Changed
- Retrieving URL Alias repository from `getRepository('ZichtUrlBundle:UrlAlias')` to `getRepository(UrlAlias::class)`.

## 7.2.3 - 2023-09-15
### Fixed
- Replaced deprecated `AnonymousToken` by `NullToken`.
- Set correct argument name for ListableProvider implementations.

## 7.2.2 - 2023-04-28
### Fixed
- Fixed `Request::getMasterRequest()` deprecations.

## 7.2.1 - 2023-03-10
### Changed
- Forward merge of v5.2.7: Always show `public_url` filter.

## 7.2.0 - 2022-12-07
### Added
- Support for `doctrine/dbal ^3`

## 7.1.0 - 2022-12-22
### Added
- Options to allow manual urls in `UrlType`

## 7.0.3 - 2022-12-01
### Fixed
- Fixed UrlType to use the internal URL to be stored to the database
- Fixed UrlType to be able to handle other values
- Fix URL Alias entity `__toString()` to return public URL instead of ID

## 7.0.2 - 2022-12-01
### Added
- Forward merge of v6.0.2: Sonata 4 form group labels

## 7.0.1 - 2022-11-28
### Changed
- Alter `UrlType` to re-use `AutocompleteType` from `zicht/admin-bundle` to prevent multiple autocomplete implementations.

  Add this to your `zicht_admin.yaml` to override the default config:
```yaml
zicht_admin:
    quicklist:
    ...
    url_alias:
        repository: 'Zicht\Bundle\UrlBundle\Entity\UrlAlias'
        fields: ['public_url']
```

## 7.0.0 - 2022-10-06
### Added
- Support for Symfony ^5.4
### Removed
- Support for Symfony 4
- Support for PHP 7.2/7.3

## 6.1.0 - 2024-01-05
### Added
- Forward merge of v5.3.0

## 6.0.3 - 2023-03-10
### Changed
- Forward merge of v5.2.7: Always show `public_url` filter.

## 6.0.2 - 2022-12-01
### Added
- Sonata 4 form group labels

## 6.0.1 - 2022-11-14
### Fixed
- Definition of filters in `UrlAliasAdmin::configureDatagridFilters`

## 6.0.0 - 2022-09-30
### Added
- Support for Sonata ^4
### Removed
- Support for Sonata ^3

## 5.3.0 - 2024-01-05
### Added
- Added the `\Zicht\Bundle\UrlBundle\Url\Params\TranslatedUriParser` and a
  `\Zicht\Bundle\UrlBundle\Url\Params\ParamTranslator` as services so a custom implementation in every
  project is no longer necessary.
- Also added `url_params` config so you can set a custom `param_separator`, `key_value_separator` and
  `value_separator` in `config/packages/zicht_url.yaml` (instead of setting these trough arguments in
  `config/services.yaml`).

## 5.2.7 - 2023-03-10
### Changed
- Always show `public_url` filter.
### Fixed
- Fixed Sonata Admin deprecations.

## 5.2.6 - 2022-09-30
### Changed
- Swapped the zicht/standards-php (PHPCS) linter for PHP CS Fixer.

## 5.2.5 - 2022-06-13
### Fixed
- Fixed deprecated Twig template notation.
- Fixed deprecated notations in README.md.

## 5.2.4 - 2022-05-19
### Fixed
- Added cascade delete for `StaticReference`.

## 5.2.3 - 2022-04-06
### Removed
- Dropped non-default `ChangeTrackingPolicy("DEFERRED_EXPLICIT")` on entities.

## 5.2.2 - 2022-01-25
### Changed
- Made missing properties added in 5.2.1 protected instead of private

## 5.2.1 - 2022-01-24
### Added
- Added symfony/http-foundation:^4.4 as a requirement
- Added missing property definitions in classes
- Added `@final` annotations to the controllers. Controllers shouldn't be extended.
### Fixed
- Fixed deprecations (Events, Controller)
- Code cleanup (removed redundant spaces and newlines, added dangling commas)
### Removed
- Removed z2.yml file

## 5.2.0 - 2021-11-15
### Added
- Support for PHP 8

## 5.1.3 - 2021-02-24
### Changed
- Set minimum required PHP version from ^7.1 to ^7.2 because there is no installable set of packages
  for PHP 7.1 with the current dependencies.
### Fixed
- `TinymceTypeExtension` defines `getExtendedTypes` as non-static, it should be static as of Symfony 4.

## 5.1.2 - 2020-10-23
### Fixed
- Merged in from v4.2.2/v4.1.4: Only use Translations Bundle LanguageType for selection of language of the
  static ref translation when it is available from other sources. Removed dependency on Translations Bundle

## 5.1.1 - 2020-10-20
### Fixed
- Wronly merged `zicht_url.twig_extension` in `services.xml`.
### Changed
- Introduce `Doctrine\Persistence\ManagerRegistry`, fixing deprecated use of `RegistryInterface`.

## 5.1.0 - 2020-08-20
### Added
- Forward merge from 4.1.3 and 4.2.0.

## 5.0.0 - 2020-05-15
### Added
- Support for Symfony 4.x
### Removed
- Support for Symfony 3.x
### Changed
- Removed Zicht(Test)/Bundle/UrlBundle/ directory depth: moved all code up directly into src/ and test/

## 4.2.2 - 2020-10-23
### Fixed
- Merged in from v4.1.4: Only use Translations Bundle LanguageType for selection of language of the static ref
  translation when it is available from other sources. Removed dependency on Translations Bundle

## 4.2.1 - 2020-08-27
### Fixed
- Added missing `minLength` parameters to the `ShortUrlManager` and `UrlExtension::shortUrl`.

## 4.2.0 - 2020-08-18
### Added
- `ShortUrlManager` as an endpoint to handle easy to implement short versions for urls.
- `UrlExtension::shortUrl` to integrate short urls in Twig.

## 4.1.4 - 2020-10-23
### Fixed
- Only use Translations Bundle LanguageType for selection of language of the static ref translation when it is
  available from other sources. Removed dependency on Translations Bundle

## 4.1.3 - 2020-07-09
### Fixed
- Added missing English translations.

## 4.1.2 - 2020-05-15
### Changed
- Switched from PSR-0 to PSR-4 autoloading.

## 4.1.1 - 2020-04-28
### Changed
- Use FQCN for form types
- Made TinymceTypeExtension getExtensionType return the actual type class instead of its own class name.
- Removed deprecated alias attribute of the form.type_extension tag of the TinymceTypeExtension service definition.

## 4.1.0 - 2019-10-03
- Added _mode_ column and filter to the URL Alias admin.

## 4.0.9 - 2019-08-13
### Fixed
- The `UrlMapperPass` will now use an optional `priority`.
  Higher priority values are executed earlier.
  Given that only the first matching mapper is used, this becomes very useful behavior.

## 4.0.8 - 2019-08-13
### Fixed
- This version has been directly replaced by 4.0.9.

## 4.0.7 - 2019-07-05
### Fixed
- The `SitemapFilterEvent` rewrite didn't go so well, the sitemap filtering thus was broken for months.

## 4.0.6 - 2019-02-05
### Changed
- Changed admin.xml UrlAliasAdmin service definition to use a parameter for the class.

## 4.0.5 - 2018-12-21
### Fixed
- Update `composer.lock`.
- Update code to conform with `zicht/standards-php` 3.4.0.

## 4.0.4 - 2018-11-28
### Fixed
- Modified the query in the `ContainsUrlAliasValidator` to get the table name for doctrine, this is required in projects where the table names are for example prefixed.

## 4.0.3 - 2018-11-19
### Changed
- Changed the MarkupType option 'virtual' to 'inherit_data' because 'virtual' is deprecated
- See: https://github.com/symfony/symfony/issues/12603 for more detailed information

## 4.0.0 - 2018-06-21
### Added
- Support for Symfony 3.x
### Removed
- Support for Symfony 2.x

## 3.1.0
### Changed
- Added event dispatching within the sitemap generation, it is now possible to filter the resulting
  urls and modify the collection that will be given back to the sitemap controller.

## 3.0.0
### Changed
From this version on the minimal PHP requirement is `7.0`

## 2.19.2
- remove itertools reference
- fixed url aliases for uri with utf8 characters

## 2.19.0
- Added event dispatching within the sitemap generation, it is now possible to alter the query before it being executed,
  or filter the resulting urls and modify the collection that will be given back to the sitemap controller.

## 2.18.1 - 2018-03-08
- Added sort to findOneByPublicUrl, findOneByInternalUrl so they return the first created id.

## 2.18.0 - 2018-03-08
- Broken version

## 2.15.0 - 2017-09-12
- The public_url column has to be unique by default. This because it was not supported by the
  application but the db schema allowed double entries with the same pulbic_url.

  There is now validation and a change in the column property. Be careful with schema updates
  because it can lead to failures when there allready double entries for the public_url.

  This all can be disable if find any problems with upgrading by setting the strict property
  to false in the config:

  ```
    zicht_url:
        strict_public_url: false
        ....
  ```


## 2.13.1 - 2017-07-11
- Expand HTMLMappers' attributes with a new default entry: `option: ['data-href']`

## 2.13.0 - 2017-05-16
### Added
- New strategy to resolve conflicts with the internal url:
  STRATEGY_MOVE_NEW_TO_PREVIOUS
- New command `zicht:url:import-aliases` used to import a csv file with
  new aliases

## 2.12.1 - 2017-03-21
### Changed
* Conform to Symfony defaults for the signature of ControllerActions.

## 2.12.0 - 2017-03-07
### Added
Includes a `QueryStringUriParser` which does not render URLS with `/`-separated
values but with regular query string parameters.

## 2.11.1 - 2017-02-23
### Changed
-When security not configured, assume "true" for 'shouldGenerateAlias'

## 2.11.0 - 2017-01-10
### Added
Allow additional html attributes to be mapped by the HtmlMapper by configuring
`html_attributes` in the config file.

Default config:

```
    html_attributes:
        a : ['href', 'data-href']
        area : ['href', 'data-href']
        iframe : ['src']
        form : ['action']
        meta : ['content']
        link : ['href']
```

## 2.10.0 - 2016-11-16
### Added
- Adds AliasOverviewType which shows all public urls of an object in the CMS.

## 2.9.0 - 2016-09-16
### Changed
- Reworked the rewriting internals for alias processing by eliminating a lot of
  duplicated code.

## 2.8.0 - 2016-08-04
### Added
- Added TinyMCE type extension for Zicht Admin Bundle TinyMCE type. Which
  transforms the public urls to internal urls.
- Extended the UrlType so the submitted public urls are saved as internal urls.

## 2.7.0 - 2016-08-02
### Added
- Added Constraint which checks external urls for validity `
  Zicht\Bundle\UrlBundle\Validator\Constraints\ContainsValidUrls `

## 2.6.0 - 2016-07-28
### Added
- The `shouldGenerateAlias` method was added to support different strategies
  for when to add and remove aliases

# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

## 5.1.0 - 2020-08-20
### Added
- Forward merge release/4.x

## 5.0.0 - 2020-05-15
### Added
- Support for Symfony 4.x
### Removed
- Support for Symfony 3.x
### Changed
- Removed Zicht(Test)/Bundle/UrlBundle/ directory depth: moved all code up directly into src/ and test/

### 4.2.0 - 2020-08-18
### Added
- `ShortUrlManager` as an endpoint to handle easy to implement short versions for urls.
- `UrlExtension::shortUrl` to integrate short urls in Twig.

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

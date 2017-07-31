# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## 2.13.1 - 2017-07-11
- Expand HTMLMappers' attributes with a new default entry: `option: ['data-href']`

## 2.13.1 - 2017-06-20
### Changed
- When a move of redirect (301 or 302) is encountered the query string
  is now properly passed to the redirect response.
  Before: /old-public-url?foo=bar -> /new-public-url
  After:  /old-public-url?foo=bar -> /new-public-url?foo=bar

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

# `zicht/url-bundle`

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zicht/url-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zicht/url-bundle/?branch=master) 
[![Code Coverage](https://scrutinizer-ci.com/g/zicht/url-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/zicht/url-bundle/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/zicht/url-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/zicht/url-bundle/build-status/master)

This bundle is part of the [`zicht/cms`](https://github.com/zicht/cms) suite.

The ZichtUrlBundle provides the following features:

* URL "Aliasing" - use seo-friendly url's without routing. The principle is
  that any url can have an alias which is used to show "readable" url's to the
  user. The general approach works in a way that is interchangable: each aliased
  version of any url can be translated to the unaliased version and vice versa.
  HTML code in the content database should never refer to aliased urls, so this
  integrates tightly with TinyMCE in the admin.
* By implementing the ProviderInterface, any service can be turned into an
  object router; i.e.: link to "objects" in stead of paths. This is utilized in
  the `zicht/page-bundle` to be able to link to Page object
* Static references: named url's which can be injected anywhere in your project
* Validation constraints



## Validator Constraints usage
```
Zicht\Bundle\RcoSiteBundle\Entity\Page\ContentPage:
    getters:
        body:
            - Zicht\Bundle\UrlBundle\Validator\Constraints\ContainsValidUrls: ~
```

## Tinymce Form type extension

If the TinyMce form type is used from the admin bundle. 
The type is extended to transform external urls to internal urls.
No additional configuration is required

## Show public URLS of a page in the admin

To enable this feature, add the following to a page admin, and make sure that
form_theme.html.twig from the url-bundle is loaded.

```
public function configureFormFields(FormMapper $formMapper)
{
    parent::configureFormFields($formMapper);
    $formMapper
        ->tab('admin.tab.alias_overview')
            ->add('alias_overview', 'alias_overview_type', ['record' => $this->getSubject()])
        ->end()->end();
}
```

## Importing a csv with aliases

Use the command `php app/console zicht:url:import-aliases url_aliases_file.csv --skip-header --csv-delimiter ';'`

This command can parse csv files that follow the following syntax:

    PUBLICURL, INTERNALURL, TYPE, CONFLICTINGPUBLICURLSTRATEGY, CONFLICTINGINTERNALURLSTRATEGY
    /home, /nl/page/1
    /also-home, /nl/page/1

Note that the first line can be ignored using "--skip-header"
TYPE, CONFLICTINGPUBLICURLSTRATEGY, and CONFLICTINGINTERNALURLSTRATEGY are optional.'

## Events

### Sitemap 
There is an event that makes it possible to modify the resultset of the sitemap;

- `zicht_url.sitemap.filter`, which allows you to modify the result from the previous query and filter out items.

# Maintainers
* Philip Bergman <philip@zicht.nl>
* Boudewijn Schoon <boudewijn@zicht.nl>

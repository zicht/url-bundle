# `zicht/url-bundle`

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

## Show public URLS of a page in the admin ###

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

# Maintainer(s) 
* Rik van der Kemp <rik@zicht.nl>
* Muhammed Akbulut <muhammed@zicht.nl>


# ZichtUrlBundle

### Validator Constraints usage
```
Zicht\Bundle\RcoSiteBundle\Entity\Page\ContentPage:
    getters:
        body:
            - Zicht\Bundle\UrlBundle\Validator\Constraints\ContainsValidUrls: ~
```

### Tinymce Form type extension

If the TinyMce form type is used from the admin bundle. 
The type is extended to transform external urls to internal urls.
No additional configuration is required

### Show public URLS of a page in the admin ###

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

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
# 2.6.0 #
- The `shouldGenerateAlias` method was added to support different strategies for when to add and remove aliases
# 2.7.0 #
- Added Constraint which checks external urls for validity ` Zicht\Bundle\UrlBundle\Validator\Constraints\ContainsValidUrls `
# 2.8.0 #
- Added TinyMCE type extension for Zicht Admin Bundle TinyMCE type. Which transforms the public urls to internal urls.
- Extended the UrlType so the submitted public urls are saved as internal urls.
# 2.9.0 #
- Reworked the rewriting internals for alias processing by eliminating a lot of duplicated code.
- Add tests
# 2.10.0 #
- Adds AliasOverviewType which shows all public urls of a page in the CMS.

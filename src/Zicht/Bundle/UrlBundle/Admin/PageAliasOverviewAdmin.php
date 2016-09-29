<?php
/**
 * @author Boudewijn Schoon <boudewijn@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zicht\Bundle\PageBundle\Entity\Page;

class PageAliasOverviewAdmin extends AbstractType
{
    /** @var RegistryInterface */
    protected $doctrine;

    /**
     * @{inheritDoc}
     */
    function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @{inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired('page');
        $resolver->setDefault('virtual', true);
    }

    /**
     * @{inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['page'] = $options['page'];
        $view->vars['url_aliases'] = $this->getUrlAliases($options['page']);

//        dump($view->vars);
//        die(sprintf('%s:%d', __FILE__, __LINE__));
    }

    /**
     * Returns all UrlAlias entities associated to a Page entity
     * @param Page $page
     */
    protected function getUrlAliases(Page $page)
    {
        $internalUrl = sprintf('/%s/page/%d', $page->getLanguage(), $page->getId());
        return $this->doctrine->getRepository('ZichtUrlBundle:UrlAlias')->findAllByInternalUrl($internalUrl);
    }

    /**
     * @{inheritDoc}
     */
    public function getName()
    {
        return 'page_alias_overview_admin';
    }
}

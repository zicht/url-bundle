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
use Zicht\Bundle\UrlBundle\Url\Provider;

class AliasOverviewType extends AbstractType
{
    /** @var Provider */
    protected $provider;

    /** @var RegistryInterface */
    protected $doctrine;

    /**
     * AliasOverviewType constructor.
     *
     * @param Provider $provider
     * @param RegistryInterface $doctrine
     */
    function __construct(Provider $provider, RegistryInterface $doctrine)
    {
        $this->provider = $provider;
        $this->doctrine = $doctrine;
    }

    /**
     * @{inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired('record');
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'label' => 'admin.alias_overview_admin.label',
            'required' => false,
            'virtual' => true,
        ]);
    }

    /**
     * @{inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['record'] = $options['record'];
        $view->vars['url_aliases'] = $this->getUrlAliases($options['record']);

//        dump($view->vars);
//        die(sprintf('%s:%d', __FILE__, __LINE__));
    }

    /**
     * Returns all UrlAlias entities associated to a Page entity
     * @param Page $page
     */
    protected function getUrlAliases(Page $page)
    {
        $internalUrl = $this->provider->url($page);
//        $internalUrl = sprintf('/%s/page/%d', $page->getLanguage(), $page->getId());
        return $this->doctrine->getRepository('ZichtUrlBundle:UrlAlias')->findAllByInternalUrl($internalUrl);
    }

    /**
     * @{inheritDoc}
     */
    public function getName()
    {
        return 'alias_overview_type';
    }
}

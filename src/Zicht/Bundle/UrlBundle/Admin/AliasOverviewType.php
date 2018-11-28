<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Exception\UnsupportedException;
use Zicht\Bundle\UrlBundle\Url\Provider;

/**
 * Form type to render all available url aliases
 */
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
    public function __construct(Provider $provider, RegistryInterface $doctrine)
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
        $resolver->setDefaults(
            [
                'translation_domain' => 'admin',
                'label' => 'admin.alias_overview_admin.label',
                'required' => false,
                'inherit_data' => true,
            ]
        );
    }

    /**
     * @{inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $aliases =  $this->getUrlAliases($options['record']);
        $view->vars['record'] = $options['record'];
        $view->vars['url_aliases'] = $aliases;
        $view->vars['url_aliases_grouped'] = $this->groupByMode($aliases);
    }

    /**
     * @param UrlAlias[] $aliases
     * @return array|UrlAlias[][]
     */
    private function groupByMode($aliases)
    {
        $property = new \ReflectionProperty(UrlAlias::class, 'mode');
        $property->setAccessible(true);
        $grouped = [];
        foreach ($aliases as $alias) {
            $grouped[$property->getValue($alias)][] = $alias;
        }
        return $grouped;
    }

    /**
     * Returns all UrlAlias entities associated to an object
     *
     * @param mixed $object
     */
    protected function getUrlAliases($object)
    {
        try {
            $internalUrl = $this->provider->url($object);
        } catch (UnsupportedException $exception) {
            return [];
        }

        return $this
            ->doctrine
            ->getRepository('ZichtUrlBundle:UrlAlias')
            ->findAllByInternalUrl($internalUrl);
    }

    /**
     * @{inheritDoc}
     */
    public function getName()
    {
        return 'alias_overview_type';
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'alias_overview_type';
    }
}

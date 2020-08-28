<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType as SonataCollectionType;

/**
 * Admin implementation for static references
 *
 */
class StaticReferenceAdmin extends AbstractAdmin
{
    /**
     * {@inheritDoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('machine_name')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'edit'   => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        if ($this->getSubject()->getId()) {
            $form
                ->add('machine_name')
                ->add(
                    'translations',
                    SonataCollectionType::class,
                    [],
                    [
                        'edit'   => 'inline',
                        'inline' => 'table',
                    ]
                );
        } else {
            $form
                ->add('machine_name');
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('machine_name')
            ->add('url');
    }

    /**
     * {@inheritDoc}
     */
    public function prePersist($object)
    {
        $object->addMissingTranslations();
    }

    /**
     * {@inheritDoc}
     */
    public function preUpdate($object)
    {
        $object->addMissingTranslations();
    }
}

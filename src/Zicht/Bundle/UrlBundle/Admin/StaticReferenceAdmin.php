<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType as SonataCollectionType;

/**
 * Admin implementation for static references
 *
 */
class StaticReferenceAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('machine_name')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'show'   => [],
                        'edit'   => [],
                        'delete' => []
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('machine_name')
            ->add('url');
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $object->addMissingTranslations();
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $object->addMissingTranslations();
    }
}

<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\CollectionType as SonataCollectionType;
use Zicht\Bundle\UrlBundle\Entity\StaticReference;

/**
 * Admin implementation for static references
 *
 * @extends AbstractAdmin<StaticReference>
 */
class StaticReferenceAdmin extends AbstractAdmin
{
    public function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('machine_name')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    protected function configureFormFields(FormMapper $form): void
    {
        if ($this->getSubject()->getId()) {
            $form
                ->add('machine_name')
                ->add(
                    'translations',
                    SonataCollectionType::class,
                    [],
                    [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ]
                );
        } else {
            $form
                ->add('machine_name');
        }
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('machine_name')
            ->add('url');
    }

    public function prePersist(object $object): void
    {
        $object->addMissingTranslations();
    }

    public function preUpdate(object $object): void
    {
        $object->addMissingTranslations();
    }
}

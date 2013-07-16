<?php
/**
 * @author    Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Zicht\Bundle\UrlBundle\Entity\StaticReference;

class StaticReferenceAdmin extends Admin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('machine_name')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'view'   => array(),
                        'edit'   => array(),
                        'delete' => array()
                    )
                )
            );
    }

    protected function configureFormFields(FormMapper $form)
    {
        if ($this->getSubject()->getId()) {
            $form
                ->add('machine_name')
                ->add(
                    'translations',
                    'sonata_type_collection',
                    array(),
                    array(
                        'edit'   => 'inline',
                        'inline' => 'table',
                    )
                );
        } else {
            $form
                ->add('machine_name');
        }
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('machine_name')
            ->add('url');
    }

    public function prePersist($object)
    {
        $object->addMissingTranslations();
    }

    public function preUpdate($object)
    {
        $object->addMissingTranslations();
    }
}
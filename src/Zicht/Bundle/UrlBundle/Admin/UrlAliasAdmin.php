<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

class UrlAliasAdmin extends Admin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('public_url')
            ->add('internal_url')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'view' => array(),
                    'edit' => array(),
                    'delete' => array()
                )
            ))
        ;
    }


    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('public_url')
            ->add('internal_url')
            ->add('mode', 'choice', array(
                'choices' => array(
                    UrlAlias::ALIAS      => 'alias',
                    UrlAlias::MOVE       => 'move (301 redirect)',
                    UrlAlias::REWRITE    => 'rewrite (302 redirect)',
                )
            ))
        ;
    }



    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('public_url')
            ->add('internal_url')
            ->add('mode')
        ;
    }
}
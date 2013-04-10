<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;

class ErrorLogAdmin extends Admin
{
    protected $datagridValues = array(
        '_page'         => 1,
        '_sort_order'   => 'DESC',
        '_sort_by'      => 'date_created'
    );


    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('date_created', null, array('route' => array('name' => 'show')))
            ->add('status')
            ->add('url')
            ->add('ip')
            ->add('message')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'view' => array(),
                    'delete' => array()
                )
            ))
        ;
    }


    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('date_created')
            ->add('status')
            ->add('url')
            ->add('ip')
            ->add('referer')
            ->add('ua')
            ->add('message')
        ;
    }
}

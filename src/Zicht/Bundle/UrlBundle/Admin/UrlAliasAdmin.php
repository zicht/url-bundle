<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use \Sonata\AdminBundle\Datagrid\DatagridMapper;
use \Sonata\AdminBundle\Datagrid\ListMapper;
use \Sonata\AdminBundle\Show\ShowMapper;
use \Sonata\AdminBundle\Admin\Admin;
use \Sonata\AdminBundle\Form\FormMapper;
use \Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Admin for URL aliases
 *
 * @codeCoverageIgnore
 */
class UrlAliasAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC', // Descendant ordering (default = 'ASC')
    );

    /**
     * @{inheritDoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
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

    /**
     * @{inheritDoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('public_url')
            ->add('internal_url')
        ;
    }

    /**
     * @{inheritDoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('public_url')
            ->add('internal_url', 'zicht_url')
            ->add('mode', 'choice', array(
                'choices' => array(
                    UrlAlias::ALIAS   => 'alias (302 redirect)',
                    UrlAlias::MOVE    => 'move (301 redirect)',
                    UrlAlias::REWRITE => 'rewrite',
                )
            ))
        ;
    }


    /**
     * @{inheritDoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('public_url')
            ->add('internal_url')
            ->add('mode')
        ;
    }
}
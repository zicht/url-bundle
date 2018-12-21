<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;

/**
 * Admin for error logs.
 *
 */
class ErrorLogAdmin extends Admin
{
    protected $datagridValues = [
        '_page'         => 1,
        '_sort_order'   => 'DESC',
        '_sort_by'      => 'date_created'
    ];

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('date_created', null, ['route' => ['name' => 'show']])
            ->add('status')
            ->add('url')
            ->add('ip')
            ->add('message')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'show' => [],
                        'delete' => []
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('date_created')
            ->add('status')
            ->add('url')
            ->add('ip')
            ->add('referer')
            ->add('ua')
            ->add('message');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }
}

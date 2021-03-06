<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ErrorLogAdmin extends AbstractAdmin
{
    /** @var array */
    protected $datagridValues = [
        '_page'         => 1,
        '_sort_order'   => 'DESC',
        '_sort_by'      => 'date_created'
    ];

    /**
     * {@inheritDoc}
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
                        'delete' => [],
                    ],
                ]
            );
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }
}

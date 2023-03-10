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
    public function configureListFields(ListMapper $list)
    {
        $list
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

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);
        $sortValues[DatagridInterface::SORT_BY] = 'date_created';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
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
            ->add('message');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }
}

<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Type\UrlType;

/**
 * Admin for URL aliases
 *
 */
class UrlAliasAdmin extends Admin
{
    protected $datagridValues = [
        '_sort_order' => 'DESC', // Descendant ordering (default = 'ASC')
    ];

    /**
     * @{inheritDoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('public_url', 'string', ['template' => 'ZichtAdminBundle:CRUD:list_url.html.twig'])
            ->add('internal_url', 'string', ['template' => 'ZichtAdminBundle:CRUD:list_url.html.twig'])
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                        'delete' => []
                    ]
                ]
            );
    }

    /**
     * @{inheritDoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('public_url')
            ->add('internal_url');
    }

    /**
     * @{inheritDoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form->add('public_url')
            ->add('internal_url', UrlType::class, ['no_transform_public' => true])
            ->add(
                'mode',
                'choice',
                [
                    'choices' => array_flip(
                        [
                        UrlAlias::ALIAS   => 'alias (302 redirect)',
                        UrlAlias::MOVE    => 'move (301 redirect)',
                        UrlAlias::REWRITE => 'rewrite',
                        ]
                    )
                ]
            );
    }

    /**
     * @{inheritDoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('public_url')
            ->add('internal_url')
            ->add('mode');
    }
}

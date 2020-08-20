<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Type\UrlType;

/**
 * Admin for URL aliases
 *
 */
class UrlAliasAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'DESC', // Descendant ordering (default = 'ASC')
    ];

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('public_url', 'string', ['template' => '@ZichtAdmin/CRUD/list_url.html.twig'])
            ->add('internal_url', 'string', ['template' => '@ZichtAdmin/CRUD/list_url.html.twig'])
            ->add('mode', null, ['template' => '@ZichtUrl/CRUD/list_mode.html.twig'])
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
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $modeChoiceOptions = [
            'choice_translation_domain' => $this->translationDomain,
            'choices' => [
                'admin.alias_overview.mode_' . UrlAlias::ALIAS => UrlAlias::ALIAS,
                'admin.alias_overview.mode_' . UrlAlias::MOVE => UrlAlias::MOVE,
                'admin.alias_overview.mode_' . UrlAlias::REWRITE => UrlAlias::REWRITE,
            ],
        ];

        $filter
            ->add('public_url')
            ->add('internal_url')
            ->add('mode', null, [], ChoiceType::class, $modeChoiceOptions);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form->add('public_url')
            ->add('internal_url', UrlType::class, ['no_transform_public' => true])
            ->add(
                'mode',
                ChoiceType::class,
                [
                    'choices' => [
                        'Alias (302 redirect)' => UrlAlias::ALIAS,
                        'Move (301 redirect)' => UrlAlias::MOVE,
                        'Rewrite' => UrlAlias::REWRITE,
                    ],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('public_url')
            ->add('internal_url')
            ->add('mode');
    }
}

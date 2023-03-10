<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Type\UrlType;

class UrlAliasAdmin extends AbstractAdmin
{
    public function configureListFields(ListMapper $list)
    {
        $list
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
                        'delete' => [],
                    ],
                ]
            );
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

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
            ->add('public_url', null, ['show_filter' => true])
            ->add('internal_url')
            ->add('mode', null, ['field_type' => ChoiceType::class, 'field_options' => $modeChoiceOptions]);
    }

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

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('public_url')
            ->add('internal_url')
            ->add('mode');
    }
}

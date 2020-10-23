<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Zicht\Bundle\TranslationsBundle\Form\Type\LanguageType;

/**
 * Admin implementation for static reference translations
 *
 */
class StaticReferenceTranslationAdmin extends AbstractAdmin
{
    protected $parentAssociationMapping = 'static_reference';

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('url');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form->with('General');

        if (class_exists(LanguageType::class)) {
            $form->add('locale', LanguageType::class, ['required' => true]);
        } else {
            $form->add('locale', null, ['required' => true]);
        }

        $form
            ->add('url', null, ['required' => true])
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('url');
    }
}

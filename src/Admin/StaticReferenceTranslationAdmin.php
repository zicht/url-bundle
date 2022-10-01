<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Zicht\Bundle\TranslationsBundle\Form\Type\LanguageType;

/**
 * Admin implementation for static reference translations
 */
class StaticReferenceTranslationAdmin extends AbstractAdmin
{
    public function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('url');
    }

    protected function configureFormFields(FormMapper $form): void
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

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('url');
    }
}

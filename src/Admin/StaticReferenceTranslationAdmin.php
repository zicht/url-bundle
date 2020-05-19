<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Admin implementation for static reference translations
 *
 */
class StaticReferenceTranslationAdmin extends AbstractAdmin
{
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
        $form
            ->with('General')
            ->add('locale', null, ['required' => true])
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

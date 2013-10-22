<?php
/**
 * @author    Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use \Sonata\AdminBundle\Datagrid\ListMapper;
use \Sonata\AdminBundle\Show\ShowMapper;
use \Sonata\AdminBundle\Admin\Admin;
use \Sonata\AdminBundle\Form\FormMapper;
use \Zicht\Bundle\UrlBundle\Entity\StaticReference;

/**
 * Admin implementation for static reference translations
 *
 * @codeCoverageIgnore
 */
class StaticReferenceTranslationAdmin extends Admin
{
    protected $parentAssociationMapping = 'static_reference';

    /**
     * @{inheritDoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('url');
    }

    /**
     * @{inheritDoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('General')
            ->add('locale', null, array('required' => true))
            ->add('url', null, array('required' => true))
            ->end();
    }

    /**
     * @{inheritDoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('url');
    }
}
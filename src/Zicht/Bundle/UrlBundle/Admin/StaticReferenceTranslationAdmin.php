<?php
/**
 * @author    Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Zicht\Bundle\UrlBundle\Entity\StaticReference;

class StaticReferenceTranslationAdmin extends Admin
{
    protected $parentAssociationMapping = 'static_reference';

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('url');
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('General')
            ->add('locale', null, array('required' => true))
            ->add('url', null, array('required' => true))
            ->end();
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('url');
    }
}
<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class UrlType extends AbstractType
{
    /**
     * @{inheritDoc}
     */
    public function getName()
    {
        return 'zicht_url';
    }


    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        // TODO make the route name an option:
        $view->vars['url_suggest'] = '/admin/url/suggest';

        // TODO lookup the title using a yet-to-be-built service
        $view->vars['current_url_title'] = $view->vars['value'];
    }


    public function getParent()
    {
        return 'text';
    }
}
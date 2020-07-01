<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


/*
 * This file is part of Twig.
 *
 * (c) 2012 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Extension_StringLoader extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('template_from_string', 'twig_template_from_string', array('needs_environment' => true)),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'string_loader';
    }
}

/**
 * Loads a template from a string.
 *
 * <pre>
 * {{ include(template_from_string("Hello {{ name }}")) }}
 * </pre>
 *
 * @param Twig_Environment $env      A Twig_Environment instance
 * @param string           $template A template as a string
 *
 * @return Twig_Template A Twig_Template instance
 */
function twig_template_from_string(Twig_Environment $env, $template)
{
    return $env->createTemplate($template);
}

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
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface implemented by compiler classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @deprecated since 1.12 (to be removed in 3.0)
 */
interface Twig_CompilerInterface
{
    /**
     * Compiles a node.
     *
     * @param Twig_NodeInterface $node The node to compile
     *
     * @return Twig_CompilerInterface The current compiler instance
     */
    public function compile(Twig_NodeInterface $node);

    /**
     * Gets the current PHP code after compilation.
     *
     * @return string The PHP code
     */
    public function getSource();
}

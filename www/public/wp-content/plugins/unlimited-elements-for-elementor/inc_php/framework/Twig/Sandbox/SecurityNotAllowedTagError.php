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
 * Exception thrown when a not allowed tag is used in a template.
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
class Twig_Sandbox_SecurityNotAllowedTagError extends Twig_Sandbox_SecurityError
{
    private $tagName;

    public function __construct($message, $tagName, $lineno = -1, $filename = null, Exception $previous = null)
    {
        parent::__construct($message, $lineno, $filename, $previous);
        $this->tagName = $tagName;
    }

    public function getTagName()
    {
        return $this->tagName;
    }
}

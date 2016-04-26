<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\Util;

use Symfony\Component\Form\FormTypeInterface;

/**
 * @internal
 *
 * @author Thomas Schulz <mail@king2500.net>
 */
final class LegacyFormUtil
{
    private static $map = array(
        'Infinite\FormBundle\Form\Type\AttachmentType' => 'infinite_form_attachment',
        'Infinite\FormBundle\Form\Type\CheckboxGridType' => 'infinite_form_checkbox_grid',
        'Infinite\FormBundle\Form\Type\CheckboxRowType' => 'infinite_form_checkbox_row',
        'Infinite\FormBundle\Form\Type\EntityCheckboxGridType' => 'infinite_form_entity_checkbox_grid',
        'Infinite\FormBundle\Form\Type\EntitySearchType' => 'infinite_form_entity_search',
        'Infinite\FormBundle\Form\Type\PolyCollectionType' => 'infinite_form_polycollection',
        'Infinite\FormBundle\Tests\CheckboxGrid\Type\SalesmanType' => 'infinite_form_test_salesman',
        'Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType' => 'abstract_type',
        'Infinite\FormBundle\Tests\PolyCollection\Type\FirstType' => 'first_type',
        'Infinite\FormBundle\Tests\PolyCollection\Type\SecondType' => 'second_type',
        'Infinite\FormBundle\Tests\PolyCollection\Type\FourthType' => 'fourth_type',
        'Infinite\FormBundle\Tests\PolyCollection\Type\UnknownType' => 'unknown_type',
        'Symfony\Component\Form\Extension\Core\Type\FormType' => 'form',
        'Symfony\Component\Form\Extension\Core\Type\CheckboxType' => 'checkbox',
        'Symfony\Component\Form\Extension\Core\Type\FileType' => 'file',
        'Symfony\Component\Form\Extension\Core\Type\HiddenType' => 'hidden',
        'Symfony\Component\Form\Extension\Core\Type\NumberType' => 'number',
        'Symfony\Component\Form\Extension\Core\Type\TextType' => 'text',
    );

    /**
     * @param string|FormTypeInterface $type
     * @return string
     */
    public static function getType($type)
    {
        // Compat for SF 2.8+
        if (self::isFullClassNameRequired() && $type instanceof FormTypeInterface) {
            return get_class($type);
        }

        // BC for SF < 2.8 for internally used types
        if (!self::isFullClassNameRequired() && isset(self::$map[$type])) {
            return self::$map[$type];
        }

        return $type;
    }

    /**
     * Compatibility for Symfony 2.8+
     * Checks whether FQCN is required as type name.
     *
     * @return bool
     */
    public static function isFullClassNameRequired()
    {
        static $fqcnRequired = null;

        if ($fqcnRequired === null) {
            $fqcnRequired = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');
        }

        return $fqcnRequired;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}
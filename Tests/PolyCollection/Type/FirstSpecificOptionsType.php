<?php
/**
 * Created by PhpStorm.
 * User: pilou
 * Date: 09/07/16
 * Time: 11:05
 */

namespace Infinite\FormBundle\Tests\PolyCollection\Type;


use Symfony\Component\OptionsResolver\OptionsResolver;

class FirstSpecificOptionsType extends FirstType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired('first_option');
    }

}
<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Provides a checkbox grid for Doctrine entities
 */
class EntityCheckboxGridType extends AbstractType
{
    protected $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getName()
    {
        return 'infinite_form_entity_checkbox_grid';
    }

    public function getParent()
    {
        return 'infinite_form_checkbox_grid';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // X Axis defaults
        $defaultXClass = function (Options $options) {
            /** @var $em \Doctrine\ORM\EntityManager */
            $em = $options['em'];
            return $em->getClassMetadata($options['class'])->getAssociationTargetClass($options['x_path']);
        };

        $defaultXLoader = function (Options $options) {
            if ($options['x_query_builder'] !== null) {
                return new ORMQueryBuilderLoader($options['x_query_builder'], $options['em'], $options['x_class']);
            }

            return null;
        };

        $defaultXChoiceList = function (Options $options) {
            return new EntityChoiceList(
                $options['em'],
                $options['x_class'],
                $options['x_label_path'],
                $options['x_loader']
            );
        };

        // Y Axis defaults
        $defaultYClass = function (Options $options) {
            /** @var $em \Doctrine\ORM\EntityManager */
            $em = $options['em'];
            return $em->getClassMetadata($options['class'])->getAssociationTargetClass($options['y_path']);
        };

        $defaultYLoader = function (Options $options) {
            if ($options['y_query_builder'] !== null) {
                return new ORMQueryBuilderLoader($options['y_query_builder'], $options['em'], $options['y_class']);
            }

            return null;
        };

        $defaultYChoiceList = function (Options $options) {
            return new EntityChoiceList(
                $options['em'],
                $options['y_class'],
                $options['y_label_path'],
                $options['y_loader']
            );
        };

        $resolver->setDefaults(array(
            'em'              => null,

            'x_class'         => $defaultXClass,
            'x_query_builder' => null,
            'x_loader'        => $defaultXLoader,
            'x_choice_list'   => $defaultXChoiceList,
            'x_label_path'    => null,

            'y_class'         => $defaultYClass,
            'y_query_builder' => null,
            'y_loader'        => $defaultYLoader,
            'y_choice_list'   => $defaultYChoiceList,
            'y_label_path'    => null,

            'cell_filter'     => null,
        ));

        $resolver->setRequired(array(
            'class',
            'x_path',
            'y_path',
        ));

        if (version_compare(Kernel::VERSION, '2.6.0', '>=')) {
            $resolver->setNormalizer('em', $this->getEntityManagerNormalizer());
        } else {
            $resolver->setNormalizers(array(
                'em' => $this->getEntityManagerNormalizer(),
            ));
        }
    }

    // BC for SF < 2.7
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }
    
    private function getEntityManagerNormalizer()
    {
        $registry = $this->registry; // for closures

        // Entity manager 'normaliser' - turns an entity manager name into an entity manager instance
        return function (Options $options, $emName) use ($registry) {
            if ($emName !== null) {
                return $registry->getManager($emName);
            }

            $em = $registry->getManagerForClass($options['class']);

            if ($em === null) {
                throw new InvalidOptionsException(sprintf('"%s" is not a Doctrine entity', $options['class']));
            }

            return $em;
        };
    }
}

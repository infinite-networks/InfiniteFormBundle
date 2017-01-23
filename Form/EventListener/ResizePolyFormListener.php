<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * A Form Resize listener capable of coping with a polycollection.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class ResizePolyFormListener extends ResizeFormListener
{
    /**
     * Stores an array of Types with the Type name as the key.
     *
     * @var array
     */
    protected $typeMap = array();

    /**
     * Stores an array of types with the Data Class as the key.
     *
     * @var array
     */
    protected $classMap = array();

    /**
     * Name of the hidden field identifying the type.
     *
     * @var string
     */
    protected $typeFieldName;

    /**
     * Name of the index field on the given entity.
     *
     * @var null|string
     */
    protected $indexProperty;

    /**
     * Property Accessor.
     *
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var bool
     */
    protected $useTypesOptions;

    /**
     * @param array<FormInterface> $prototypes
     * @param array                $options
     * @param bool                 $allowAdd
     * @param bool                 $allowDelete
     * @param string               $typeFieldName
     * @param string               $indexProperty
     */
    public function __construct(array $prototypes, array $options = array(), $allowAdd = false, $allowDelete = false, $typeFieldName = '_type', $indexProperty = null, $useTypesOptions = false)
    {
        $this->typeFieldName = $typeFieldName;
        $this->indexProperty = $indexProperty;
        $this->useTypesOptions = $useTypesOptions;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $defaultType = null;

        foreach ($prototypes as $key => $prototype) {
            /** @var FormInterface $prototype */
            $modelClass = $prototype->getConfig()->getOption('model_class');
            $type = $prototype->getConfig()->getType()->getInnerType();

            if (null === $defaultType) {
                $defaultType = $type;
            }

            $this->typeMap[$key] = $type;
            $this->classMap[$modelClass] = $type;
        }

        parent::__construct($defaultType, $options, $allowAdd, $allowDelete);
    }

    /**
     * Returns the form type for the supplied object. If a specific
     * form type is not found, it will return the default form type.
     *
     * @param object $object
     *
     * @return string
     */
    protected function getTypeForObject($object)
    {
        $class = get_class($object);
        $class = ClassUtils::getRealClass($class);
        $type = $this->type;

        if (array_key_exists($class, $this->classMap)) {
            $type = $this->classMap[$class];
        }

        return LegacyFormUtil::getType($type);
    }

    /**
     * Checks the form data for a hidden _type field that indicates
     * the form type to use to process the data.
     *
     * @param array $data
     *
     * @return string|FormTypeInterface
     *
     * @throws \InvalidArgumentException when _type is not present or is invalid
     */
    protected function getTypeForData(array $data)
    {
        if (!array_key_exists($this->typeFieldName, $data) || !array_key_exists($data[$this->typeFieldName], $this->typeMap)) {
            throw new \InvalidArgumentException('Unable to determine the Type for given data');
        }

        return LegacyFormUtil::getType($this->typeMap[$data[$this->typeFieldName]]);
    }

    protected function getOptionsForType($type)
    {
        if ($this->useTypesOptions === true) {
            return isset($this->options[$type]) ? $this->options[$type] : [];
        } else {
            return $this->options;
        }
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        // First remove all rows
        foreach ($form as $name => $child) {
            $form->remove($name);
        }

        // Then add all rows again in the correct order for the incoming data
        foreach ($data as $name => $value) {
            $type = $this->getTypeForObject($value);
            $form->add($name, $type, array_replace(array(
                'property_path' => '['.$name.']',
            ), $this->getOptionsForType($type)));
        }
    }

    public function preBind(FormEvent $event)
    {
        $this->preSubmit($event);
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data || '' === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        // Process entries by IndexProperty
        if (!is_null($this->indexProperty)) {
            // Reindex the submit data by given index
            $indexedData = array();
            $unindexedData = array();
            $finalData = array();
            foreach ($data as $item) {
                if (isset($item[$this->indexProperty])) {
                    $indexedData[$item[$this->indexProperty]] = $item;
                } else {
                    $unindexedData[] = $item;
                }
            }

            // Add all additional rows to the end of the array
            $name = $form->count();
            foreach ($unindexedData as $item) {
                if ($this->allowAdd) {
                    $type = $this->getTypeForData($item);
                    $form->add($name, $type, array_replace(array(
                        'property_path' => '['.$name.']',
                    ), $this->getOptionsForType($type)));
                }

                // Add to final data array
                $finalData[$name] = $item;
                ++$name;
            }

            // Remove all empty rows
            if ($this->allowDelete) {
                foreach ($form as $name => $child) {
                    // New items will have null data. Skip these.
                    if (!is_null($child->getData())) {
                        $index = $this->propertyAccessor->getValue($child->getData(), $this->indexProperty);
                        if (!isset($indexedData[$index])) {
                            $form->remove($name);
                        } else {
                            $finalData[$name] = $indexedData[$index];
                        }
                    }
                }
            }

            // Replace submitted data with new form order
            $event->setData($finalData);
        } else {
            // Remove all empty rows
            if ($this->allowDelete) {
                foreach ($form as $name => $child) {
                    if (!isset($data[$name])) {
                        $form->remove($name);
                    }
                }
            }

            // Add all additional rows
            if ($this->allowAdd) {
                foreach ($data as $name => $value) {
                    if (!$form->has($name)) {
                        $type = $this->getTypeForData($value);
                        $form->add($name, $type, array_replace(array(
                            'property_path' => '['.$name.']',
                        ), $this->getOptionsForType($type)));
                    }
                }
            }
        }
    }
}

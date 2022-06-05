<?php

namespace Infinite\FormBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntitySearchTransformer implements DataTransformerInterface
{
    private $om;
    private $class;
    private $idField;
    private $nameField;
    private $accessor;
    private $allowNotFound = false;

    public function __construct(EntityManager $om, array $options)
    {
        $this->om = $om;

        if (!isset($options['class'])) {
            throw new \InvalidArgumentException('Class not specified in EntitySearchTransformer::setOptions');
        }

        if (!class_exists($options['class'])) {
            throw new \InvalidArgumentException(sprintf(
                'Class "%s" not found in EntitySearchTransformer::__construct',
                $options['class']
            ));
        }

        $this->class = $options['class'];

        if (isset($options['allow_not_found'])) {
            $this->allowNotFound = $options['allow_not_found'];
        }

        if (isset($options['name'])) {
            $this->nameField = $options['name'];
        } else {
            $this->nameField = 'name';
        }

        $idFieldArray = $om->getClassMetadata($this->class)->getIdentifierFieldNames();
        $this->idField = reset($idFieldArray);

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function transform($object): ?array
    {
        if ($object === null) {
            return null;
        }

        if (!$object instanceof $this->class) {
            throw new UnexpectedTypeException($object, $this->class);
        }

        return array(
            'id' => $this->accessor->getValue($object, $this->idField),
            'name' => $this->accessor->getValue($object, $this->nameField),
        );
    }

    /**
     * @return mixed
     */
    public function reverseTransform($value)
    {
        if (!isset($value['id'])) {
            $value['id'] = '';
        }

        if (!isset($value['name'])) {
            $value['name'] = '';
        }

        $repository = $this->om->getRepository($this->class);
        if ($value['id'] != '') {
            $object = $repository->find($value['id']);

            if (!$this->allowNotFound && !isset($object)) {
                throw new TransformationFailedException('Transformation failed - object not found');
            }
        } elseif ($value['name'] != '') {
            $object = $repository->findOneBy(array($this->nameField => $value['name']));

            if (!$this->allowNotFound && !isset($object)) {
                throw new TransformationFailedException('Transformation failed - object not found');
            }
        } else {
            $object = null;
        }

        return $object;
    }
}

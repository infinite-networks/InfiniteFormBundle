<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\DataTransformer;

use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Transforms a 1D array of data objects into a 2D array of booleans.
 */
class CheckboxGridTransformer implements DataTransformerInterface
{
    /** @var string */
    protected $class;

    /** @var ChoiceListInterface */
    protected $xChoiceList;
    /** @var ChoiceListInterface */
    protected $yChoiceList;

    /** @var PropertyPath */
    protected $xPath;
    /** @var PropertyPath */
    protected $yPath;

    public function __construct(array $options)
    {
        $this->xChoiceList = $options['x_choice_list'];
        $this->yChoiceList = $options['y_choice_list'];

        $this->xPath = new PropertyPath($options['x_path']);
        $this->yPath = new PropertyPath($options['y_path']);

        $this->class = $options['class'];
    }

    public function transform(mixed $value): array
    {
        if ($value === null) {
            return array();
        }

        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new TransformationFailedException('Checkbox grid transformer needs an array as input');
        }

        $vals = array();

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($value as $object) {
            $xChoice = $accessor->getValue($object, $this->xPath);
            $yChoice = $accessor->getValue($object, $this->yPath);

            $xValueMatch = $this->xChoiceList->getValuesForChoices(array($xChoice));
            $yValueMatch = $this->yChoiceList->getValuesForChoices(array($yChoice));

            if (!$xValueMatch || !$yValueMatch) {
                continue;
            }

            // Instead of setting the checkbox's state to true, set it to $object. This will still check the box,
            // but the checkbox-creation code (which runs after this!) can remember this information.
            $vals[$yValueMatch[0]][$xValueMatch[0]] = $object;
        }

        return $vals;
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (!is_array($value)) {
            throw new TransformationFailedException('Checkbox grid reverse-transformer needs an array as input');
        }

        $result = array();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($value as $yValue => $row) {
            if (!is_array($row)) {
                throw new TransformationFailedException('Checkbox grid reverse-transformer needs a 2D array');
            }

            $yChoiceMatch = $this->yChoiceList->getChoicesForValues(array($yValue));

            if (!$yChoiceMatch) {
                continue;
            }

            foreach ($row as $xValue => $checked) {
                $xChoiceMatch = $this->xChoiceList->getChoicesForValues(array($xValue));

                if ($xChoiceMatch && $checked) {
                    if (is_bool($checked)) {
                        if ($this->class === null) {
                            $object = array();
                        } else {
                            $object = new $this->class();
                        }
                    } else {
                        $object = $checked;
                    }

                    $accessor->setValue($object, $this->xPath, reset($xChoiceMatch));
                    $accessor->setValue($object, $this->yPath, reset($yChoiceMatch));

                    $result[] = $object;
                }
            }
        }

        return $result;
    }
}

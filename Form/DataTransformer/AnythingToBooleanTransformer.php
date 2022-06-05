<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms a checkbox's posted value back into an arbitrary object.
 */
class AnythingToBooleanTransformer implements DataTransformerInterface
{
    protected $anythingValue;

    public function __construct($anythingValue)
    {
        $this->anythingValue = $anythingValue;
    }

    public function transform($value): bool
    {
        return $value !== null;
    }

    /**
     * @return mixed
     */
    public function reverseTransform($value)
    {
        return empty($value) ? null : $this->anythingValue;
    }
}

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
    public function __construct(
        protected mixed $anythingValue
    )
    {
    }

    public function transform(mixed $value): bool
    {
        return $value !== null;
    }

    public function reverseTransform(mixed $value): mixed
    {
        return empty($value) ? null : $this->anythingValue;
    }
}

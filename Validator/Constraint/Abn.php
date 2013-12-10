<?php

/**
 * This file is part of the InfiniteFormBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Abn extends Constraint
{
    public $formatMessage = 'ABN must be in the format XX XXX XXX XXX';
    public $checkDigitMessage = 'ABN does not match the ABN check digit test';
}

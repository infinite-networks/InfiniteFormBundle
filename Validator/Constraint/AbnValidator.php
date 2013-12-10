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

use Infinite\FormBundle\Validator\Constraint\Abn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates ABN numbers.
 *
 * @author Jon Mclean <j.mclean@infinite.net.au
 */
class AbnValidator extends ConstraintValidator
{
    /**
     * Runs ABN validation on a value.
     *
     * @param mixed $value
     * @param Abn|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        // Accept blank values (can add a Required constraint if needed)
        if ($value == '') {
            return;
        }

        // Preferred format is 'XX XXX XXX XXX'
        // 'XXXXXXXXXXX' is also allowed even though the error message doesn't say so
        if (!preg_match('/^(\d\d \d\d\d \d\d\d \d\d\d|\d{11})$/', $value)) {
            $this->context->addViolation($constraint->formatMessage);

            return;
        }

        // Test ABN checksum
        $s = str_replace(' ', '', $value); // Strip spaces before calculating the checksum
        $sum = 0;
        $weights = array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);

        for ($i = 0; $i < 11; $i++) {
            $d = (int) substr($s, $i, 1);

            if ($i == 0) {
                $d--;
            }

            $sum += $d * $weights[$i];
        }

        if ($sum % 89) {
            $this->context->addViolation($constraint->checkDigitMessage);
        }
    }
}

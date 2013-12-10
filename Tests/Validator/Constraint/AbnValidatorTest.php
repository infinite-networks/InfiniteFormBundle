<?php

/**
 * This file is part of the InfiniteFormBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\Validator\Constraint;

use Infinite\FormBundle\Validator\Constraint\Abn;
use Infinite\FormBundle\Validator\Constraint\AbnValidator;

class AbnValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbnValidator
     */
    private $validator;

    /**
     * @var Abn
     */
    private $constraint;

    /**
     * @var \Symfony\Component\Validator\ExecutionContext
     */
    private $context;

    protected function setUp()
    {
        $this->constraint = new Abn;
        $this->context    = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator  = new AbnValidator;
        $this->validator->initialize($this->context);
    }

    public function testBlankAbn()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', $this->constraint);
    }

    public function testInvalidAbnFormat()
    {
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($this->constraint->formatMessage);

        $this->validator->validate('HELLO', $this->constraint);
    }

    /**
     * @dataProvider getValidAbns
     */
    public function testValidAbns($abn)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($abn, $this->constraint);
    }

    public function getValidAbns()
    {
        return array(
            array('80 097 243 055'),
            array('51824753556'),
            array('35 083 238 395'),
        );
    }

    /**
     * @dataProvider getInvalidAbns
     */
    public function testInvalidAbns($abn)
    {
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($this->constraint->checkDigitMessage);

        $this->validator->validate($abn, $this->constraint);
    }

    public function getInvalidAbns()
    {
        return array(
            array('80 097 243 056'),
            array('51824753557'),
            array('35 083 238 396'),
        );
    }
}

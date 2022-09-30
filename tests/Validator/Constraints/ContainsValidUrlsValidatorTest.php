<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Zicht\Bundle\UrlBundle\Service\UrlValidator;
use Zicht\Bundle\UrlBundle\Validator\Constraints\ContainsValidUrls;
use Zicht\Bundle\UrlBundle\Validator\Constraints\ContainsValidUrlsValidator;

/**
 * Class ContainsValidUrlsValidatorTest
 */
class ContainsValidUrlsValidatorTest extends TestCase
{
    /**
     * Testing the validation.
     */
    public function testValidatesUrls()
    {
        //Arrange
        $string = '<a href="http://www.google.nl"> bla bla </a>';

        $constraint = $this->getMockBuilder(ContainsValidUrls::class)->getMock();
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $urlValidator = $this->getMockBuilder(UrlValidator::class)->disableOriginalConstructor()->getMock();
        $context->expects($this->never())->method('addViolation');

        $urlValidator->expects($this->once())->method('validate')->with('http://www.google.nl')->willReturn(true);

        $validator = new ContainsValidUrlsValidator($urlValidator);
        $validator->initialize($context);
        //Act

        $validator->validate($string, $constraint);

        //Assert
    }

    /**
     * Testing the validation.
     */
    public function testValidatesUrlsWithoutProtocol()
    {
        //Arrange
        $string = '<a href="//www.google.nl"> bla bla </a>';

        $constraint = $this->getMockBuilder(ContainsValidUrls::class)->getMock();
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();

        $context->expects($this->never())->method('addViolation');

        $urlValidator = $this->getMockBuilder(UrlValidator::class)->disableOriginalConstructor()->getMock();
        $urlValidator->expects($this->once())->method('validate')->with('//www.google.nl')->willReturn(true);

        $validator = new ContainsValidUrlsValidator($urlValidator);
        $validator->initialize($context);

        //Act
        $validator->validate($string, $constraint);

        //Assert
    }

    /**
     * Testing the validation.
     */
    public function testValidatesUrlsMultiple()
    {
        //Arrange
        $string = '<a href="http://www.google.nl"> bla bla </a><a href="http://www.brokenlink.nl"> bla bla </a>
                    <a href="https://www.google.nl"> bla bla </a><a href="/url"> bla bla </a>';

        $constraint = $this->getMockBuilder(ContainsValidUrls::class)->getMock();
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();

        $context->expects($this->once())->method('addViolation')
            ->with($constraint->message, ['%string%' => 'http://www.brokenlink.nl']);

        $urlValidator = $this->getMockBuilder(UrlValidator::class)->disableOriginalConstructor()->getMock();
        $urlValidator->expects($this->at(0))->method('validate')->with('http://www.google.nl')->willReturn(true);
        $urlValidator->expects($this->at(1))->method('validate')->with('http://www.brokenlink.nl')->willReturn(false);
        $urlValidator->expects($this->at(2))->method('validate')->with('https://www.google.nl')->willReturn(true);

        $validator = new ContainsValidUrlsValidator($urlValidator);
        $validator->initialize($context);

        //Act
        $validator->validate($string, $constraint);

        //Assert
    }

    /**
     * Testing the validation.
     */
    public function testValidatesUrlsWithError()
    {
        //Arrange
        $string = '<a href="http://www.google.nl"> bla bla </a>';

        $constraint = $this->getMockBuilder(ContainsValidUrls::class)->getMock();
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();

        $context->expects($this->once())->method('addViolation');

        $urlValidator = $this->getMockBuilder(UrlValidator::class)->disableOriginalConstructor()->getMock();
        $urlValidator->expects($this->once())->method('validate')->with('http://www.google.nl')->willReturn(false);

        $validator = new ContainsValidUrlsValidator($urlValidator);
        $validator->initialize($context);

        //Act
        $validator->validate($string, $constraint);

        //Assert
    }
}

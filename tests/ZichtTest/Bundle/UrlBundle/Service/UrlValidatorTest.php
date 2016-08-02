<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;
use Zicht\Bundle\UrlBundle\Service\UrlValidator;

/**
 * Class UrlValidatorTest
 *
 * @package ZichtTest\Bundle\UrlBundle\Service
 */
class UrlValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests a wrong status code given.
     */
    public function testWrongUrl()
    {
        //Arrange
        $url = 'http://www.google.nl';

        $client = $this->getMockBuilder(Client::class)->getMock();
        $response = $this->getMockBuilder(Response::class)->getMock();

        $response->expects($this->once())->method('getStatusCode')->willReturn(500);
        $client->expects($this->once())->method('request')->with('GET', $url)->willReturn($response);

        $urlValidator = new UrlValidator($client);

        //Act
        $assertValue = $urlValidator->validate($url);

        //Assert
        $this->assertFalse($assertValue);
    }

    /**
     * Tests a good status code given.
     */
    public function testGoodUrl()
    {
        //Arrange
        $url = 'http://www.google.nl';

        $client = $this->getMockBuilder(Client::class)->getMock();
        $response = $this->getMockBuilder(Response::class)->getMock();

        $response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $client->expects($this->once())->method('request')->with('GET', $url)->willReturn($response);

        $urlValidator = new UrlValidator($client);

        //Act
        $assertValue = $urlValidator->validate($url);

        //Assert
        $this->assertTrue($assertValue);
    }
}

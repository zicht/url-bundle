<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Service;

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
     *
     * lynx -listonly -dump http://httpstat.us/ | awk '{ print $NF }' | egrep '/[0-9][0-9][0-9]$'
     */
    public function testWrongUrl()
    {
        $list = [
            'http://httpstat.us/300',
            // Disabled because it redirects to 200:OK
            // 'http://httpstat.us/301',
            // 'http://httpstat.us/302',
            // 'http://httpstat.us/303',
            'http://httpstat.us/304',
            'http://httpstat.us/305',
            'http://httpstat.us/306',
            // 'http://httpstat.us/307',
            // 'http://httpstat.us/308',
            'http://httpstat.us/400',
            'http://httpstat.us/401',
            'http://httpstat.us/402',
            'http://httpstat.us/403',
            'http://httpstat.us/404',
            'http://httpstat.us/405',
            'http://httpstat.us/406',
            'http://httpstat.us/407',
            'http://httpstat.us/408',
            'http://httpstat.us/409',
            'http://httpstat.us/410',
            'http://httpstat.us/411',
            'http://httpstat.us/412',
            'http://httpstat.us/413',
            'http://httpstat.us/414',
            'http://httpstat.us/415',
            'http://httpstat.us/416',
            'http://httpstat.us/417',
            'http://httpstat.us/418',
            'http://httpstat.us/422',
            'http://httpstat.us/428',
            'http://httpstat.us/429',
            'http://httpstat.us/431',
            'http://httpstat.us/451',
            'http://httpstat.us/500',
            'http://httpstat.us/501',
            'http://httpstat.us/502',
            'http://httpstat.us/503',
            'http://httpstat.us/504',
            'http://httpstat.us/505',
            'http://httpstat.us/511',
            'http://httpstat.us/520',
            'http://httpstat.us/522',
            'http://httpstat.us/524',
        ];

        $validator = new UrlValidator();

        foreach ($list as $url) {
            $this->assertFalse($validator->validate($url), $url);
        }

    }

    /**
     * Tests a good status code given.
     */
    public function testGoodUrl()
    {
        $list = [
            'http://httpstat.us/200',
            'http://httpstat.us/200',
            'http://httpstat.us/201',
            'http://httpstat.us/202',
            'http://httpstat.us/203',
            'http://httpstat.us/204',
            'http://httpstat.us/205',
            'http://httpstat.us/206',
        ];

        $validator = new UrlValidator();

        foreach ($list as $url) {
            $this->assertTrue($validator->validate($url));
        }
    }
}

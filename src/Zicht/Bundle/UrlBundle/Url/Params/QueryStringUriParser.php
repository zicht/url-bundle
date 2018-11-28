<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Url\Params;

/**
 * Parser for key/value pairs in the url.
 */
class QueryStringUriParser extends UriParser
{
    /**
     * Constructor; overridden to disable the separator parameters
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Compose an URI from the passed params with the local separators
     *
     * @param Params $params
     * @return string
     */
    public function composeUri($params)
    {
        $ret   = [];

        foreach ($params as $param => $values) {
            $internal = $param;
            if ($external = $this->translateKeyOutput($param)) {
                $param = $external;
            }
            $ret[$param] = [];
            foreach ($values as $value) {
                if ($external = $this->translateValueOutput($internal, $value)) {
                    $value = $external;
                }
                $ret[$param][]= $value;
            }
        }

        return http_build_query($ret);
    }
}

<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @package Aura.Http
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Cookie;

use Aura\Http\Cookie;

/**
 * 
 * Factory to create new Cookie objects.
 * 
 * @package Aura.Http
 * 
 */
class Factory
{
    /**
     * 
     * Base values for a single cookie.
     * 
     * @var array
     * 
     */
    protected $params = [
        'value'    => null,
        'expire'   => 0,
        'path'     => null,
        'domain'   => null,
        'secure'   => false,
        'httponly' => true,
    ];

    /**
     * 
     * Creates and returns a new Cookie object.
     * 
     * @param string $name Cookie name.
     * 
     * @param array $params An array of key-value pairs corresponding to
     * the remaining Cookies constructor params.
     * 
     * @return Aura\Http\Cookie
     * 
     */
    public function newInstance($name = null, array $params = array())
    {
        $params = array_merge($this->params, $params);
        return new Cookie(
            $name,
            $params['value'],
            $params['expire'],
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
}

<?php
/**
Copyright (c) 2011, Geoffroy Couprie
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list
of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this
list of conditions and the following disclaimer in the documentation and/or other
materials provided with the distribution.
Neither the name of the project nor the names of its contributors may be used to
endorse or promote products derived from this software without specific prior
written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
OF THE POSSIBILITY OF SUCH DAMAGE.
**/

class CurException extends Exception
{
    public $unsanitized=0;
    public $inexistant=1;
}

class Curator implements arrayaccess 
{
    private $input = array();
    private $curated = array();

    /**
     * Create a Curator object from an input array, like $_GET, $_POST or $_COOKIE
     * @param array $$params Input parameters to sanitize
     */
    public function __construct($params) 
    {
        $this->input = $params;

    }
    
    /**
     * Implements arrayaccess's method to set $value at $offset in an array
     * Don't call it directly
     */
    public function offsetSet($offset, $value) 
    {
        if (is_null($offset)) 
        {
            $this->container[] = $value;    
        } else 
        {                            
            $this->container[$offset] = $value;
        }
    }

    /**
     * Implements arrayaccess's method to answer to isset()
     * Don't call it directly
     */
    public function offsetExists($offset) 
    {
        return isset($this->container[$offset]);
    }
    
    /**
     * Implements arrayaccess's method to answer to unset()
     * Don't call it directly
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
    
    /**
     * Implements arrayaccess's method to get the value at $offset
     * Don't call it directly, use the [] operator
     * If the parameter was successfully sanitized, return it.
     * If not, throw an exception.
     */
    public function offsetGet($offset) 
    {
        if(isset($this->curated[$offset]))
            return $this->curated[$offset];
        else if (isset($this->input[$offset]))
            throw new CurException($offset." was not sanitized", 0);
        else
            throw new CurException("parameter ".$offset." doesn't exist", 1);
    }

    /**
     * Filter parameter at $key with function $callback
     * @param string $key parameter name
     * @param function $callback filtering function
     */
    public function sanitize($key, $callback)
    {
        $this->curated[$key] = $callback($this->input[$key]);
    }

    /**
     * Sanitize all the parameters in the $keys array
     * @param array $keys parameters
     * @param function $callback filtering function
     */
    public function sanitizeArray($keys, $callback)
    {
        foreach($keys as $key)
        {
            $this->sanitize($key, $callback);
        }
    }

}

/**
 * Unsigned integer validation function
 * @param string $val parameter to sanitize
 * @return unsigned integer $val, or null if not valid
 */
function valid_uint($val)
{
    if(ctype_digit($val))
        return intval($val);
}

/**
 * Alphanumeric values validation function
 * @return string $val if $val only contains alphanumeric characters
 */
function valid_alphanum($val)
{
    if (ctype_alnum($val))
         return $val;
}

/**
 * Array validation function
 * @param array $arr permitted values
 * @param string $val parameter to sanitize
 * @return string $val if $val belongs to the array
 */
function valid_array($arr, $val)
{
     if(array_search($val, $arr) !==FALSE)
         return $val;
}

?>

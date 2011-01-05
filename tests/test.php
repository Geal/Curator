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

require_once('simpletest/autorun.php');
require_once('../curator.php');

class TestOfCurator extends UnitTestCase 
{
    function testException() 
    {
        $ar = Array("a" => 1);
        $get = new Curator($ar);
        $this->expectException(new CurException("a was not sanitized", 0));
        $get['a'];

    }

    function testCustomValidator()
    {
        $ar = Array("a" => 1);
        $get = new Curator($ar);
        function valid($p){return $p;};
    
        $get->sanitize('a',  function($p){return $p;});
        
        $this->assertIdentical($get['a'], $ar['a']);
    }
}

class TestOfUInt extends UnitTestCase
{
    function setUp() 
    {
        $ar = Array("a" => "1", "b" => "0", "c" => "1.2", "d" => "-1", "e" => "a12", "f" => "'", "g" => null);
        $this->output = new Curator($ar);
        $this->output->sanitizeArray(Array("a","b","c","d","e","f","g"), "valid_uint");        
    }
    
    function testShouldRecognizeInts()
    {
        $this->assertIdentical( $this->output['a'],1);
        $this->assertIdentical( $this->output['b'],0);
    }

    function testDontAcceptFloats()
    {
        $this->expectException(new CurException("c was not sanitized", 0));
        $this->output['c'];
    }

    function testDontAcceptSignedInts()
    {
        $this->expectException(new CurException("d was not sanitized", 0));
        $this->output['d'];
    }

    function testDontAcceptLetters()
    {
        $this->expectException(new CurException("e was not sanitized", 0));
        $this->output['e'];
    }

    function testDontAcceptQuotes()
    {
        $this->expectException(new CurException("f was not sanitized", 0));
        $this->output['f'];
    }

    function testDontAcceptNull()
    {
        $this->expectException(new CurException("parameter g doesn't exist", 1));
        $this->output['g'];
    }

    function tearDown(){}
}

class TestOfAlphaNum extends UnitTestCase
{
    function setUp()
    {
        $i = Array("a" => "1", "b" => "abc", "c" => "ab2ef7", "d" => "a_3e", "e" => "abc'", "f"=>"AbC3e2Z");
        $this->o = new Curator($i);
        $this->o->sanitizeArray(Array("a","b","c","d","e", "f"), "valid_alphanum");
    }

    function testShouldRecognizeAlphaNum()
    {
        $this->assertIdentical( $this->o['a'],"1");
        $this->assertIdentical( $this->o['b'],"abc");
        $this->assertIdentical( $this->o['c'],"ab2ef7");
        $this->assertIdentical( $this->o['f'],"AbC3e2Z");
    }

    function testDontAcceptUnderscore()
    {
        $this->expectException(new CurException("d was not sanitized", 0));
        $this->o['d'];
    }

    function testDontAcceptQuote()
    {
        $this->expectException(new CurException("e was not sanitized", 0));
        $this->o['e'];
    }
}

class TestOfBelongToArray extends UnitTestCase
{
    function setUp()
    {
        $i=Array("a"=>"aaa", "b"=>"bbb", "c"=>"ddd", "d"=>"bbb", "e"=>null);
        $this->o = new Curator($i);
        $this->o->sanitizeArray(Array("a", "b", "c", "d", "e"), function($var){return valid_array(Array("aaa", "bbb"), $var);});
    }

    function testIsInArray()
    {
         $this->assertIdentical($this->o["a"], "aaa");
         $this->assertIdentical($this->o["b"], "bbb");
         $this->assertIdentical($this->o["d"], "bbb");
    }

    function testNotInArray()
    {
        $this->expectException(new CurException("c was not sanitized", 0));
        $this->o["c"];
    }
    function testDontAcceptNull()
    {
        $this->expectException(new CurException("parameter e doesn't exist", 1));
        $this->o['e'];
    }

}

?>

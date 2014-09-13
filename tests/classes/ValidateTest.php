<?php

class ValidateTest extends PHPUnit_Framework_TestCase
{
    public function testFilterBoolean()
    {
        $this->assertTrue(Validate::isValid(true, ['filter:boolean' => 'error']));
    }

    public function testFilterBooleanError()
    {
        $this->assertEquals(['error'], Validate::isValid(false, ['filter:boolean' => 'error']));
    }

    public function testFilterEmail()
    {
        $this->assertTrue(Validate::isValid('foo@bar.com', ['filter:email' => 'error']));
    }

    public function testFilterEmailError()
    {
        $this->assertEquals(['error'], Validate::isValid('invalid', ['filter:email' => 'error']));
    }

    public function testFilterFloat()
    {
        $this->assertTrue(Validate::isValid(2.231981, ['filter:float' => 'error']));
    }

    public function testFilterFloatError()
    {
        $this->assertEquals(['error'], Validate::isValid('invalid', ['filter:float' => 'error']));
    }

    public function testFilterInt()
    {
        $this->assertTrue(Validate::isValid(2231981, ['filter:int' => 'error']));
    }

    public function testFilterIntError()
    {
        $this->assertEquals(['error'], Validate::isValid('invalid', ['filter:int' => 'error']));
    }

    public function testFilterIP()
    {
        $this->assertTrue(Validate::isValid('2.23.19.81', ['filter:ip' => 'error']));
    }

    public function testFilterIPError()
    {
        $this->assertEquals(['error'], Validate::isValid('invalid', ['filter:ip' => 'error']));
    }

    public function testFilterURL()
    {
        $this->assertTrue(Validate::isValid('http://foo.com/bar?stuff', ['filter:url' => 'error']));
    }

    public function testFilterURLError()
    {
        $this->assertEquals(['error'], Validate::isValid('invalid', ['filter:url' => 'error']));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid validation rule, expected: "validate:boolean|email|float|int|ip|url".
     */
    public function testFilterVarInvalidRule()
    {
        Validate::isValid('value', ['filter' => 'foo']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid filter, expecting boolean, email, float, int, ip or url.
     */
    public function testFilterVarInvalidFilter()
    {
        Validate::isValid('value', ['filter:foo' => 'bar']);
    }

    public function testLengthLessThan()
    {
        $this->assertTrue(Validate::isValid('value', ['length:<:10' => 'error']));
    }

    public function testLengthLessThanError()
    {
        $this->assertEquals(['error'], Validate::isValid('value', ['length:<:1' => 'error']));
    }

    public function testLengthLessThanOrEqual()
    {
        $this->assertTrue(Validate::isValid('value', ['length:<=:10' => 'error']));
    }

    public function testLengthLessThanOrEqualError()
    {
        $this->assertEquals(['error'], Validate::isValid('value', ['length:<=:1' => 'error']));
    }

    public function testLengthEqual()
    {
        $this->assertTrue(Validate::isValid('value', ['length:==:5' => 'error']));
    }

    public function testLengthEqualError()
    {
        $this->assertEquals(['error'], Validate::isValid('value', ['length:==:1' => 'error']));
    }

    public function testLengthNotEqual()
    {
        $this->assertTrue(Validate::isValid('value', ['length:!=:1' => 'error']));
    }

    public function testLengthNotEqualError()
    {
        $this->assertEquals(['error'], Validate::isValid('value', ['length:!=:5' => 'error']));
    }

    public function testLengthGreaterThanOrEqual()
    {
        $this->assertTrue(Validate::isValid('value', ['length:>=:1' => 'error']));
    }

    public function testLengthGreaterThanOrEqualError()
    {
        $this->assertEquals(['error'], Validate::isValid('value', ['length:>=:10' => 'error']));
    }

    public function testLengthGreaterThan()
    {
        $this->assertTrue(Validate::isValid('value', ['length:>:1' => 'error']));
    }

    public function testLengthGreaterThanError()
    {
        $this->assertEquals(['error'], Validate::isValid('value', ['length:>:10' => 'error']));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid validation rule, expected: "length:<|<=|==|!=|>=|>:integer".
     */
    public function testLengthInvalidRule()
    {
        Validate::isValid('value', ['length:16' => 'bar']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid length value, expecting an integer.
     */
    public function testLengthInvalidLength()
    {
        Validate::isValid('value', ['length:==:foo' => 'bar']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid operator, expecting <, <=, ==, !=, >= or >.
     */
    public function testLengthInvalidOperator()
    {
        Validate::isValid('value', ['length:&&:10' => 'foo']);
    }

    public function testRegexIs()
    {
        $this->assertTrue(Validate::isValid('value', ['regex:is:/^va7ue$/' => 'error']));
    }

    public function testRegexIsError()
    {
        $this->assertEquals(['error'], Validate::isValid('value', ['regex:is:/^value$/' => 'error']));
    }

    public function testRegexNot()
    {
        $this->assertTrue(Validate::isValid('value', ['regex:not:/^value$/' => 'error']));
    }

    public function testRegexNotError()
    {
        $this->assertEquals(['error'], Validate::isValid('value', ['regex:not:/^va7ue$/' => 'error']));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid validation rule, expected: "regex:is|not:string".
     */
    public function testRegexInvalidRule()
    {
        Validate::isValid('value', ['regex:/foo/' => 'bar']);
    }
}


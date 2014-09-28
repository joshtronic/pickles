<?php

// class ValidateTest extends PHPUnit_Framework_TestCase
// {
//     public function testLengthLessThan()
//     {
//         $this->assertTrue(Validate::isValid('value', ['length:<:10' => 'error']));
//     }
// 
//     public function testLengthLessThanError()
//     {
//         $this->assertEquals(['error'], Validate::isValid('value', ['length:<:1' => 'error']));
//     }
// 
//     public function testLengthLessThanOrEqual()
//     {
//         $this->assertTrue(Validate::isValid('value', ['length:<=:10' => 'error']));
//     }
// 
//     public function testLengthLessThanOrEqualError()
//     {
//         $this->assertEquals(['error'], Validate::isValid('value', ['length:<=:1' => 'error']));
//     }
// 
//     public function testLengthEqual()
//     {
//         $this->assertTrue(Validate::isValid('value', ['length:==:5' => 'error']));
//     }
// 
//     public function testLengthEqualError()
//     {
//         $this->assertEquals(['error'], Validate::isValid('value', ['length:==:1' => 'error']));
//     }
// 
//     public function testLengthNotEqual()
//     {
//         $this->assertTrue(Validate::isValid('value', ['length:!=:1' => 'error']));
//     }
// 
//     public function testLengthNotEqualError()
//     {
//         $this->assertEquals(['error'], Validate::isValid('value', ['length:!=:5' => 'error']));
//     }
// 
//     public function testLengthGreaterThanOrEqual()
//     {
//         $this->assertTrue(Validate::isValid('value', ['length:>=:1' => 'error']));
//     }
// 
//     public function testLengthGreaterThanOrEqualError()
//     {
//         $this->assertEquals(['error'], Validate::isValid('value', ['length:>=:10' => 'error']));
//     }
// 
//     public function testLengthGreaterThan()
//     {
//         $this->assertTrue(Validate::isValid('value', ['length:>:1' => 'error']));
//     }
// 
//     public function testLengthGreaterThanError()
//     {
//         $this->assertEquals(['error'], Validate::isValid('value', ['length:>:10' => 'error']));
//     }
// 
//     /**
//      * @expectedException        Exception
//      * @expectedExceptionMessage Invalid validation rule, expected: "length:<|<=|==|!=|>=|>:integer".
//      */
//     public function testLengthInvalidRule()
//     {
//         Validate::isValid('value', ['length:16' => 'bar']);
//     }
// 
//     /**
//      * @expectedException        Exception
//      * @expectedExceptionMessage Invalid length value, expecting an integer.
//      */
//     public function testLengthInvalidLength()
//     {
//         Validate::isValid('value', ['length:==:foo' => 'bar']);
//     }
// 
//     /**
//      * @expectedException        Exception
//      * @expectedExceptionMessage Invalid operator, expecting <, <=, ==, !=, >= or >.
//      */
//     public function testLengthInvalidOperator()
//     {
//         Validate::isValid('value', ['length:&&:10' => 'foo']);
//     }
// 
//     public function testRegexIs()
//     {
//         $this->assertTrue(Validate::isValid('value', ['regex:is:/^va7ue$/' => 'error']));
//     }
// 
//     public function testRegexIsError()
//     {
//         $this->assertEquals(['error'], Validate::isValid('value', ['regex:is:/^value$/' => 'error']));
//     }
// 
//     public function testRegexNot()
//     {
//         $this->assertTrue(Validate::isValid('value', ['regex:not:/^value$/' => 'error']));
//     }
// 
//     public function testRegexNotError()
//     {
//         $this->assertEquals(['error'], Validate::isValid('value', ['regex:not:/^va7ue$/' => 'error']));
//     }
// 
//     /**
//      * @expectedException        Exception
//      * @expectedExceptionMessage Invalid validation rule, expected: "regex:is|not:string".
//      */
//     public function testRegexInvalidRule()
//     {
//         Validate::isValid('value', ['regex:/foo/' => 'bar']);
//     }
// }


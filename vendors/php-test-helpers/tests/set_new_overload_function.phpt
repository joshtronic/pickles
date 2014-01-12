--TEST--
A function can be registered as a callback with set_new_overload()
--SKIPIF--
<?php
if (!extension_loaded('test_helpers')) die('skip test_helpers extension not loaded');
?>
--FILE--
<?php
class Foo {}
class Bar {}

function callback($className) {
    return 'Foo';
}

var_dump(set_new_overload('callback'));

var_dump(get_class(new Bar));
--EXPECT--
bool(true)
string(3) "Foo"

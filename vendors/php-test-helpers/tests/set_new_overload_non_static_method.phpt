--TEST--
A non-static method can be registered as a callback with set_new_overload()
--SKIPIF--
<?php
if (!extension_loaded('test_helpers')) die('skip test_helpers extension not loaded');
?>
--FILE--
<?php
class Foo {}
class Bar {}

class CB {
    function callback($className) {
        if ($className == 'Bar') {
            return 'Foo';
        } else {
            return $className;
        }
    }
}

var_dump(set_new_overload(array(new CB(), 'callback')));

var_dump(get_class(new Bar));
--EXPECT--
bool(true)
string(3) "Foo"

--TEST--
A fatal error is triggered when the class returned by the callback does not exist
--SKIPIF--
<?php
if (!extension_loaded('test_helpers')) die('skip test_helpers extension not loaded');
?>
--FILE--
<?php
class Bar {}

function callback($className) {
    return 'Foo';
}

var_dump(set_new_overload('callback'));

var_dump(get_class(new Bar));
--EXPECTF--
bool(true)

Fatal error: Uncaught exception 'Exception' with message 'Class Foo does not exist' in %s:%d
Stack trace:
#0 {main}
  thrown in %s on line %d

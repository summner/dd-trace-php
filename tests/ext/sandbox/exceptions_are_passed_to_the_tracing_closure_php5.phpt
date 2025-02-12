--TEST--
Exceptions from original call are passed to tracing closure (PHP 5)
--SKIPIF--
<?php if (PHP_VERSION_ID < 50500) die('skip PHP 5.4 not supported'); ?>
<?php if (PHP_VERSION_ID >= 70000) die('skip PHP 7 tested in separate test'); ?>
--FILE--
<?php
use DDTrace\SpanData;

register_shutdown_function(function () {
    array_map(function($span) {
        echo $span['name'];
        if (isset($span['meta']['error.msg'])) {
            echo ' with exception: ' . $span['meta']['error.msg'];
        }
        echo PHP_EOL;
    }, dd_trace_serialize_closed_spans());
});

function testExceptionIsNull()
{
    echo "testExceptionIsNull()\n";
}

function testExceptionIsPassed()
{
    echo "testExceptionIsPassed()\n";
    throw new Exception('Oops!');
}

dd_trace_function('testExceptionIsNull', function (SpanData $span, array $args, $retval, $ex) {
    $span->name = 'TestNull';
    var_dump($ex === null);
});

dd_trace_function('testExceptionIsPassed', function (SpanData $span, array $args, $retval, $ex) {
    $span->name = 'TestEx';
    var_dump($ex instanceof Exception);
});

testExceptionIsNull();
testExceptionIsPassed();
echo "This line should not be run\n";
?>
--EXPECTF--
testExceptionIsNull()
bool(true)
testExceptionIsPassed()
bool(true)

Fatal error: Uncaught %sOops!%sin %s:%d
Stack trace:
#0 %s(%d): testExceptionIsPassed()
#1 %s(%d): unknown()
#2 {main}
  thrown in %s on line %d
TestEx with exception: Oops!
TestNull

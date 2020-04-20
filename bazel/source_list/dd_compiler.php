<?php
putenv("DD_TRACE_ENABLED=true");
putenv("DD_TRACE_CLI_ENABLED=true");

require_once $argv[1];
require_once $argv[2];

spl_autoload_register(['\DDTrace\Bridge\OptionalDepsAutoloader', 'load'], true, true);
spl_autoload_register(['\DDTrace\Bridge\RequiredDepsAutoloader', 'load'], true, true);

const DD_TRACE_VERSION = "123";

function dd_extension_loaded() {
    return true;
};

function dd_trace_internal_fn()
{
    // echo "dd_trace_internal_fn" . PHP_EOL;
};

function dd_trace_push_span_id()
{
    // echo "dd_trace_push_span_id" . PHP_EOL;
};

function dd_trace($a, $b)
{
    $key = array_search(__FUNCTION__, array_column(debug_backtrace(), 'function'));
    // var_dump(debug_backtrace()[$key]['file']);
    $callerFile = explode("/src/", debug_backtrace()[$key]['file'])[1];

    // echo "\"" . $clazz . ":" . $method . "\": \"" . $callerFile . "\", " . PHP_EOL;
    if (is_string($b)){
        echo implode(":", [$a, $b]) . PHP_EOL;
    } else {
        echo $a . PHP_EOL;
    }
}

function dd_trace_method($clazz, $method)
{
    $key = array_search(__FUNCTION__, array_column(debug_backtrace(), 'function'));
    // var_dump(debug_backtrace()[$key]['file']);
    $callerFile = explode("/src/", debug_backtrace()[$key]['file'])[1];
    echo implode(":", [$clazz, $method]) . PHP_EOL;
    // echo "\"" . $clazz . ":" . $method . "\": \"" . $callerFile . "\", " . PHP_EOL;
}

function dd_trace_function()
{
    // echo "dd_trace_function" . PHP_EOL;
}

function dd_trace_disable_in_request()
{
    // echo "dd_trace_disable_in_request" . PHP_EOL;
    return False;
}

function dd_trace_env_config()
{
    // echo "dd_trace_env_config" . PHP_EOL;
    return True;
}

require_once $argv[3];

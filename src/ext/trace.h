#ifndef DDTRACE_TRACE_H
#define DDTRACE_TRACE_H
#include <php.h>

#include "dispatch.h"

void ddtrace_trace_dispatch(ddtrace_dispatch_t *dispatch, zend_function *fbc,
                            zend_execute_data *execute_data TSRMLS_DC);

#endif  // DDTRACE_TRACE_H

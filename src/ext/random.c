#include "random.h"

#include <php.h>

#include <ext/standard/php_rand.h>

#include "configuration.h"
#include "ddtrace.h"
#include "third-party/mt19937-64.h"

ZEND_EXTERN_MODULE_GLOBALS(ddtrace);

void ddtrace_seed_prng(TSRMLS_D) {
    if (get_dd_trace_debug_prng_seed() > 0) {
        init_genrand64((unsigned long long)get_dd_trace_debug_prng_seed());
    } else {
        init_genrand64((unsigned long long)GENERATE_SEED());
    }
}

void ddtrace_init_span_id_stack(TSRMLS_D) {
    DDTRACE_G(root_span_id) = 0;
    DDTRACE_G(span_ids_top) = NULL;
}

void ddtrace_free_span_id_stack(TSRMLS_D) {
    while (DDTRACE_G(span_ids_top) != NULL) {
        ddtrace_span_ids_t *stack = DDTRACE_G(span_ids_top);
        DDTRACE_G(span_ids_top) = stack->next;
        efree(stack);
    }
}

uint64_t ddtrace_push_span_id(TSRMLS_D) {
    ddtrace_span_ids_t *stack = ecalloc(1, sizeof(ddtrace_span_ids_t));
    // Shift one bit to get 63-bit; add 1 since "0" can indicate a root span
    stack->id = (uint64_t)((genrand64_int64() >> 1) + 1);
    stack->next = DDTRACE_G(span_ids_top);
    DDTRACE_G(span_ids_top) = stack;
    // Assuming the first call this function is for the root span
    if (DDTRACE_G(root_span_id) == 0) {
        DDTRACE_G(root_span_id) = stack->id;
    }
    return stack->id;
}

uint64_t ddtrace_pop_span_id(TSRMLS_D) {
    if (DDTRACE_G(span_ids_top) == NULL) {
        return 0;
    }
    uint64_t id;
    ddtrace_span_ids_t *stack = DDTRACE_G(span_ids_top);
    DDTRACE_G(span_ids_top) = stack->next;
    id = stack->id;
    if (DDTRACE_G(span_ids_top) == NULL) {
        DDTRACE_G(root_span_id) = 0;
    }
    efree(stack);
    return id;
}

uint64_t ddtrace_peek_span_id(TSRMLS_D) {
    if (DDTRACE_G(span_ids_top) == NULL) {
        return 0;
    }
    return DDTRACE_G(span_ids_top)->id;
}

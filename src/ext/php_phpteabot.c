/* phpteabot.c */

#include <string.h>
#include "php_phpteabot.h"
#include "zend_exceptions.h"

ZEND_DECLARE_MODULE_GLOBALS(php_teabot);

static zend_class_entry *phpteabot_ce;

static PHP_METHOD(php_teabot, __construct) {
    char *code;
    size_t code_size;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STRING(code, code_size)
    ZEND_PARSE_PARAMETERS_END();

    zend_update_property_stringl(
        php_teabot_ce,
        getThis(),
        ZEND_STRL("code"),
        code,
        code_size
    );
}

static PHP_METHOD(php_teabot, execute) {
   
}

static zend_function_entry php_teabot_methods[] = {
    PHP_ME(php_teabot, __construct, NULL, ZEND_ACC_CTOR | ZEND_ACC_PRIVATE)
    PHP_ME(php_teabot, execute, NULL, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

static PHP_MINIT_FUNCTION(php_teabot) {
    zend_class_entry ce;
    INIT_CLASS_ENTRY(ce, "TeaBot\\Lang", php_teabot_methods);
    zend_register_internal_class(&ce);
    REGISTER_INI_ENTRIES();
    return SUCCESS;
}

static PHP_MSHUTDOWN_FUNCTION(php_teabot) {
    UNREGISTER_INI_ENTRIES();
    return SUCCESS;
}

static PHP_GINIT_FUNCTION(php_teabot) {
#if defined(COMPILE_DL_ASTKIT) && defined(ZTS)
    ZEND_TSRMLS_CACHE_UPDATE();
#endif
}

zend_module_entry php_teabot_module_entry = {
    STANDARD_MODULE_HEADER,
    "php_teabot",
    NULL, /* functions */
    PHP_MINIT(php_teabot),
    PHP_MSHUTDOWN(php_teabot),
    NULL, /* RINIT */
    NULL, /* RSHUTDOWN */
    NULL, /* MINFO */
    "0.1",
    PHP_MODULE_GLOBALS(php_teabot),
    PHP_GINIT(php_teabot),
    NULL, /* GSHUTDOWN */
    NULL, /* RPOSTSHUTDOWN */
    STANDARD_MODULE_PROPERTIES_EX
};

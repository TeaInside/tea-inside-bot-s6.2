
#include "lang.h"
#include "teabot.h"

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 6.2.0
 */
ZEND_DECLARE_MODULE_GLOBALS(teabot);

static zend_class_entry *teabot_class_entry;
#define teabot_ce teabot_class_entry

const zend_function_entry teabot_lang_class_methods[] = {
    PHP_ME(TeaBot_Lang, __construct, NULL, ZEND_ACC_PRIVATE | ZEND_ACC_CTOR)
    PHP_ME(TeaBot_Lang, init, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(TeaBot_Lang, setFallbackLang, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(TeaBot_Lang, get, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(TeaBot_Lang, getLang, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(TeaBot_Lang, getFallbackLang, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_FE_END
};

/**
 * Init.
 */
static PHP_MINIT_FUNCTION(teabot)
{
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, "TeaBot", "Lang", teabot_lang_class_methods);
    teabot_class_entry = zend_register_internal_class(&ce TSRMLS_CC);
    REGISTER_INI_ENTRIES();

    zend_declare_property_stringl(
        teabot_class_entry,
        "lang",
        sizeof("lang") - 1,
        "en",
        2,
        ZEND_ACC_PUBLIC | ZEND_ACC_STATIC TSRMLS_CC
    );

    zend_declare_property_stringl(
        teabot_class_entry,
        "fallbackLang",
        sizeof("fallbackLang") - 1,
        "en",
        2,
        ZEND_ACC_PUBLIC | ZEND_ACC_STATIC TSRMLS_CC
    );

    return SUCCESS;
}



/**
 * TeaBot\Lang::__construct
 */
PHP_METHOD(TeaBot_Lang, __construct)
{
    // This class should not be initialized.
}

/**
 * TeaBot\Lang::init
 *
 * @param string $lang
 * @return void
 */
PHP_METHOD(TeaBot_Lang, init)
{
    char *lang;
    size_t size;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STRING(lang, size)
    ZEND_PARSE_PARAMETERS_END();

    zend_update_static_property_stringl(
        teabot_ce,
        "lang",
        sizeof("lang")-1,
        lang,
        size TSRMLS_CC
    );
}

/**
 * TeaBot\Lang::init
 *
 * @param string $lang
 * @return void
 */
PHP_METHOD(TeaBot_Lang, setFallbackLang)
{
    char *lang;
    size_t size;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STRING(lang, size)
    ZEND_PARSE_PARAMETERS_END();

    zend_update_static_property_stringl(
        teabot_ce,
        "fallbackLang",
        sizeof("fallbackLang")-1,
        lang,
        size TSRMLS_CC
    );
}

/**
 * TeaBot\Lang::get
 *
 * @param string $key
 * @return string
 */
PHP_METHOD(TeaBot_Lang, get)
{
    char *key;
    char *ret;
    size_t size;
    zval *lang;
    zval *fallbackLang;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STRING(key, size)
    ZEND_PARSE_PARAMETERS_END();

    lang = zend_read_static_property(
        teabot_ce,
        "lang",
        sizeof("lang")-1,
        1 TSRMLS_CC
    );

    ret = NULL;

    // English.
    if (!strcmp(lang->value.str->val, "en")) {
        GET_LANG(en, ret, key);
    } else

    // Indonesia.
    if (!strcmp(lang->value.str->val, "id")) {
        GET_LANG(id, ret, key);
    }

    if (ret == NULL) {
        fallbackLang = zend_read_static_property(
            teabot_ce,
            "fallbackLang",
            sizeof("fallbackLang")-1,
            1 TSRMLS_CC
        );
        
        GET_LANG_FALLBACK(fallbackLang->value.str->val, ret, key)
    }

    if (ret != NULL) {
        RETURN_STRINGL(ret, strlen(ret))   
    }
}

/**
 * TeaBot\Lang::getLang
 *
 * @return string
 */
PHP_METHOD(TeaBot_Lang, getLang)
{
    zval *lang;

    lang = zend_read_static_property(
        teabot_ce,
        "lang",
        sizeof("lang")-1,
        1 TSRMLS_CC
    );

    RETURN_STRINGL(lang->value.str->val, strlen(lang->value.str->val))
}

/**
 * TeaBot\Lang::getFallbackLang
 *
 * @return string
 */
PHP_METHOD(TeaBot_Lang, getFallbackLang)
{
    zval *fallbackLang;

    fallbackLang = zend_read_static_property(
        teabot_ce,
        "fallbackLang",
        sizeof("fallbackLang")-1,
        1 TSRMLS_CC
    );

    RETURN_STRINGL(fallbackLang->value.str->val, strlen(fallbackLang->value.str->val))
}

/**
 * Shutdown.
 */
static PHP_MSHUTDOWN_FUNCTION(teabot)
{
    UNREGISTER_INI_ENTRIES();
    return SUCCESS;
}

/**
 * Global init.
 */
static PHP_GINIT_FUNCTION(teabot)
{
    #if defined(COMPILE_DL_ASTKIT) && defined(ZTS)
        ZEND_TSRMLS_CACHE_UPDATE();
    #endif
}

zend_module_entry teabot_module_entry = {
    STANDARD_MODULE_HEADER,
    "teabot",
    NULL, /* functions */
    PHP_MINIT(teabot),
    PHP_MSHUTDOWN(teabot),
    NULL, /* RINIT */
    NULL, /* RSHUTDOWN */
    NULL, /* MINFO */
    TEABOT_VERSION,
    PHP_MODULE_GLOBALS(teabot),
    PHP_GINIT(teabot),
    NULL, /* GSHUTDOWN */
    NULL, /* RPOSTSHUTDOWN */
    STANDARD_MODULE_PROPERTIES_EX
};

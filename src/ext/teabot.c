
#include "lang.h"
#include "teabot.h"

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 6.2.0
 */
#ifdef COMPILE_DL_TEABOT
    #ifdef ZTS
        ZEND_TSRMLS_CACHE_DEFINE()
    #endif
    ZEND_GET_MODULE(teabot)
#endif


ZEND_DECLARE_MODULE_GLOBALS(teabot);

zend_class_entry
    *teabot_lang_ce,
    *teabot_captchathread_ce;

const char langlist[][2] = {"en", "id", "jp"};

/**
 * TeaBot\Lang
 */
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
 * TeaBot\CaptchaThread
 */
const zend_function_entry teabot_captchathread_class_methods[] = {
    PHP_ME(TeaBot_CaptchaThread, __construct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_ME(TeaBot_CaptchaThread, run, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(TeaBot_CaptchaThread, dispatch, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(TeaBot_CaptchaThread, cancel, NULL, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/**
 * Init.
 */
static PHP_MINIT_FUNCTION(teabot)
{
    zend_class_entry ce1, ce2;

    INIT_NS_CLASS_ENTRY(ce1, "TeaBot", "Lang",
        teabot_lang_class_methods);
    teabot_lang_ce = zend_register_internal_class(&ce1 TSRMLS_CC);

    INIT_NS_CLASS_ENTRY(ce2, "TeaBot", "CaptchaThread",
        teabot_captchathread_class_methods);
    teabot_captchathread_ce = zend_register_internal_class(&ce2 TSRMLS_CC);

    REGISTER_INI_ENTRIES();

    /**
     * TeaBot\Lang
     */
    zend_declare_property_stringl(teabot_lang_ce, ZEND_STRL("lang"),
        "en", 2, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC TSRMLS_CC);

    zend_declare_property_stringl(teabot_lang_ce, ZEND_STRL("fallbackLang"),
        "en", 2, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC TSRMLS_CC);

    /**
     * TeaBot\CaptchaThread
     */
    zend_declare_property_long(teabot_captchathread_ce, ZEND_STRL("tid"),
        0, ZEND_ACC_PUBLIC TSRMLS_CC);

    zend_declare_property_stringl(teabot_captchathread_ce, ZEND_STRL("token"),
        NULL, 0, ZEND_ACC_PUBLIC TSRMLS_CC);

    zend_declare_property_stringl(teabot_captchathread_ce, ZEND_STRL("captcha_dir"),
        NULL, 0, ZEND_ACC_PUBLIC TSRMLS_CC);

    return SUCCESS;
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


#include "../lang.h"
#include "../teabot.h"

#include "../lang/en.h"
#include "../lang/id.h"

/**
 * @package \TeaBot
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 */

extern zend_class_entry *teabot_lang_ce;
extern const char langlist[][2];

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
        teabot_lang_ce,
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
        teabot_lang_ce,
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
        teabot_lang_ce,
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
            teabot_lang_ce,
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
        teabot_lang_ce,
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
        teabot_lang_ce,
        "fallbackLang",
        sizeof("fallbackLang")-1,
        1 TSRMLS_CC
    );

    RETURN_STRINGL(fallbackLang->value.str->val, strlen(fallbackLang->value.str->val))
}

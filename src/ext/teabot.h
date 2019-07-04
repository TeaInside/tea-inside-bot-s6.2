

#ifndef TEABOT_H
#define TEABOT_H

	#define TEABOT_VERSION "6.2"

	#include "php.h"

	#ifdef HAVE_CONFIG_H
		#include "config.h"
	#endif

	#ifdef ZTS
		#include "TSRM.h"
	#endif

	PHP_INI_BEGIN()
	PHP_INI_END()

	extern zend_module_entry teabot_module_entry;

	PHP_METHOD(TeaBot_Lang, __construct);
	PHP_METHOD(TeaBot_Lang, init);
	PHP_METHOD(TeaBot_Lang, get);


	ZEND_BEGIN_MODULE_GLOBALS(teabot)
	ZEND_END_MODULE_GLOBALS(teabot)
	ZEND_EXTERN_MODULE_GLOBALS(teabot)
	#define PHPNASMG(v) ZEND_MODULE_GLOBALS_ACCESSOR(teabot, v)


	#if defined(ZTS) && defined(COMPILE_DL_SAMPLE)
		ZEND_TSRMLS_CACHE_EXTERN()
	#endif

	#ifdef COMPILE_DL_TEABOT
	    #ifdef ZTS
	        ZEND_TSRMLS_CACHE_DEFINE()
	    #endif
	    ZEND_GET_MODULE(teabot)
	#endif

	#define phpext_teabot_ptr &teabot_module_entry

#endif

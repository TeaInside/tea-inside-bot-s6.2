
#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include <pthread.h>
#include "../teabot.h"

/**
 * @package \TeaBot
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 */

extern zend_class_entry *teabot_class_entry;

static void *run_queue(void *ptr);
static void teabot_zend_release_fcall_info_cache(zend_fcall_info_cache *fcc);

struct queue_ptr
{
	zend_fcall_info fci;
	zend_fcall_info_cache fci_cache;
};

/**
 * TeaBot\CaptchaThread::__construct
 */
PHP_METHOD(TeaBot_CaptchaThread, __construct)
{
}

/**
 * TeaBot\CaptchaThread::run
 */
PHP_METHOD(TeaBot_CaptchaThread, run)
{
	pthread_t thread;
}

/**
 * TeaBot\CaptchaThread::dispatch
 */
PHP_METHOD(TeaBot_CaptchaThread, dispatch)
{
	zval *obj, *tid, rv;
	zend_long rtid;
	struct queue_ptr qw;
	pthread_t queue_thread;

	qw.fci = empty_fcall_info;
	qw.fci_cache = empty_fcall_info_cache;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_FUNC(qw.fci, qw.fci_cache)
	ZEND_PARSE_PARAMETERS_END();

	obj = getThis();
	tid = zend_read_property(
		teabot_class_entry,
		obj,
		ZEND_STRL("tid"),
		0,
		&rv
	);

	pthread_create(&queue_thread, NULL, run_queue, (void *)&qw);
	pthread_detach(queue_thread);

	sleep(1);

	zend_update_property_long(
		teabot_class_entry,
		obj,
		ZEND_STRL("tid"),
		tid->value.lval + 1
		TSRMLS_CC
	);

	RETURN_LONG(tid->value.lval)
}


static void *run_queue(void *ptr)
{
	zval retval;
	struct queue_ptr qw;

	memcpy(&qw, ptr, sizeof(qw));

	qw.fci.no_separation = 0;
	qw.fci.retval = &retval;

	zend_call_function(&(qw.fci), &(qw.fci_cache));
	teabot_zend_release_fcall_info_cache(&(qw.fci_cache));
	printf("Cleaned up\n");
}

static void teabot_zend_release_fcall_info_cache(zend_fcall_info_cache *fcc) {
	if (fcc->function_handler &&
		(fcc->function_handler->common.fn_flags & ZEND_ACC_CALL_VIA_TRAMPOLINE)) {
		if (fcc->function_handler->common.function_name) {
			zend_string_release_ex(fcc->function_handler->common.function_name, 0);
		}
		zend_free_trampoline(fcc->function_handler);
	}
	fcc->function_handler = NULL;
}

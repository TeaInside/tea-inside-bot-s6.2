
#include <stdio.h>
#include <stdint.h>
#include <unistd.h>
#include <string.h>
#include <pthread.h>
#include <stdbool.h>
#include "../teabot.h"

/**
 * @package \TeaBot
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 */

extern zend_class_entry *teabot_captchathread_ce;

struct captcha_queue {
	bool busy;
	bool cancel;
	zend_long tid;
	zend_long sleep_time;
	zend_fcall_info fci;
	zend_fcall_info_cache fci_cache;
};

static void *run_queue(struct captcha_queue *qw);
static void teabot_zend_release_fcall_info_cache(zend_fcall_info_cache *fcc);

static uint16_t qptr = 0;
static struct captcha_queue queues[300] = {0};

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
}

/**
 * TeaBot\CaptchaThread::dispatch
 */
PHP_METHOD(TeaBot_CaptchaThread, dispatch)
{
	uint16_t rqptr;
	zval *obj, *tid, rv;
	pthread_t queue_thread;

	zend_fcall_info ifci = empty_fcall_info;
	zend_fcall_info_cache ifci_cache = empty_fcall_info_cache;

	rqptr = qptr;
	queues[rqptr].busy = true;
	queues[rqptr].cancel = false;
	queues[rqptr].fci = empty_fcall_info;
	queues[rqptr].fci_cache = empty_fcall_info_cache;

	printf("%d\n", rqptr);

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(queues[rqptr].sleep_time)
		Z_PARAM_FUNC(ifci, ifci_cache)
	ZEND_PARSE_PARAMETERS_END();

	memcpy(&(queues[rqptr].fci), &ifci, sizeof(ifci));
	memcpy(&(queues[rqptr].fci_cache), &ifci_cache, sizeof(ifci_cache));

	obj = getThis();
	tid = zend_read_property(
		teabot_captchathread_ce,
		obj,
		ZEND_STRL("tid"),
		0,
		&rv
	);

	queues[rqptr].tid = tid->value.lval;
	queues[rqptr].fci.no_separation = 1;

	pthread_create(&queue_thread, NULL, (void* (*)(void *))run_queue, (void *)&(queues[qptr]));
	pthread_detach(queue_thread);

	qptr++;

	zend_update_property_long(
		teabot_captchathread_ce,
		obj,
		ZEND_STRL("tid"),
		tid->value.lval + 1
		TSRMLS_CC
	);

	RETURN_LONG(rqptr)
}

/**
 * TeaBot\CaptchaThread::cancel
 */
PHP_METHOD(TeaBot_CaptchaThread, cancel)
{
	zend_long rqptr;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(rqptr)
	ZEND_PARSE_PARAMETERS_END();

	queues[rqptr].cancel = true;
}


static void *run_queue(struct captcha_queue *qw)
{
	zval retval;

	qw->fci.retval = &retval;

	sleep(qw->sleep_time);

	if (!qw->cancel) {
		zend_call_function(&(qw->fci), &(qw->fci_cache));
	} else {
		printf("Job cancelled!\n");
	}

	qw->busy = false;
	qw->cancel = false;

	teabot_zend_release_fcall_info_cache(&(qw->fci_cache));
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

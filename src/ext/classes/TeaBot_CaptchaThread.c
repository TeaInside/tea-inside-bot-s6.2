
#include <stdio.h>
#include <dirent.h>
#include <stdlib.h>
#include <stdint.h>
#include <unistd.h>
#include <string.h>
#include <pthread.h>
#include <stdbool.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <curl/curl.h>

#include "../teabot.h"

/**
 * @package \TeaBot
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 */

extern zend_class_entry *teabot_ce;

#define DEBUG_CT 0
#if DEBUG_CT
    #define debug_print(...) printf(__VA_ARGS__)
#else
    #define debug_print(...)
#endif
#define MAX_QUEUE 300

typedef struct {
    pthread_t thread;
    bool busy;
    bool cancel;
    char *type;
    size_t type_len;
    zend_long sleep_time;
    zend_long user_id;
    char *chat_id;
    size_t chat_id_len;
    zend_long join_msg_id;
    zend_long captcha_msg_id;
    zend_long welcome_msg_id;
    char *banned_hash;
    size_t banned_hash_len;
    char *mention;
    size_t mention_len;
} captcha_queue;

typedef struct {
    char *data;
    size_t len;
    size_t allocated;
} tgcurl_res;

static const unsigned char hexchars[] = "0123456789ABCDEF";

static char *token, *captcha_dir;
static size_t token_len, captcha_dir_len;

static uint16_t qpos = 0;
static captcha_queue queues[MAX_QUEUE];

static zend_fcall_info fci_exe;
static zend_fcall_info_cache fci_cache_exe;

static void clear_del_queue(captcha_queue *qw);
static tgcurl_res tgExe(char *method, char *payload);
static void *calculus_queue_dispatch(captcha_queue *qw);
static void teabot_zend_release_fcall_info_cache(zend_fcall_info_cache *fcc);
static unsigned char *teabot_urlencode(const char *s, size_t len);


/**
 * TeaBot\CaptchaThread::__construct
 */
PHP_METHOD(TeaBot_CaptchaThread, __construct)
{
    fci_exe = empty_fcall_info;
    fci_cache_exe = empty_fcall_info_cache;

    ZEND_PARSE_PARAMETERS_START(3, 3)
        Z_PARAM_STRING(token, token_len)
        Z_PARAM_STRING(captcha_dir, captcha_dir_len)
        Z_PARAM_FUNC(fci_exe, fci_cache_exe)
    ZEND_PARSE_PARAMETERS_END();

    zend_update_property_stringl(teabot_ce, getThis(), ZEND_STRL("token"),
        token, token_len TSRMLS_CC);

    zend_update_property_stringl(teabot_ce, getThis(), ZEND_STRL("captcha_dir"),
        captcha_dir, captcha_dir_len TSRMLS_CC);
}

/**
 * TeaBot\CaptchaThread::dispatch
 */
PHP_METHOD(TeaBot_CaptchaThread, dispatch)
{
    register void* (*handler)(void *) = NULL;
    register uint16_t cpos = qpos++;

    ZEND_PARSE_PARAMETERS_START(9, 9)
        Z_PARAM_STRING(queues[cpos].type, queues[cpos].type_len)
        Z_PARAM_LONG(queues[cpos].sleep_time)
        Z_PARAM_LONG(queues[cpos].user_id)
        Z_PARAM_STRING(queues[cpos].chat_id, queues[cpos].chat_id_len)
        Z_PARAM_LONG(queues[cpos].join_msg_id)
        Z_PARAM_LONG(queues[cpos].captcha_msg_id)
        Z_PARAM_LONG(queues[cpos].welcome_msg_id)
        Z_PARAM_STRING(queues[cpos].banned_hash, queues[cpos].banned_hash_len)
        Z_PARAM_STRING(queues[cpos].mention, queues[cpos].mention_len)
    ZEND_PARSE_PARAMETERS_END();

    if (!strcmp(queues[cpos].type, "calculus")) {
        handler = (void* (*)(void *))calculus_queue_dispatch;
    }

    if (handler) {
        queues[cpos].busy = true;
        queues[cpos].cancel = false;
        qpos = qpos % MAX_QUEUE;
        pthread_create(&(queues[cpos].thread), NULL,
            (void* (*)(void *))handler, (void *)&(queues[cpos]));
        pthread_detach(queues[cpos].thread);
        RETURN_LONG(cpos)
    } else {
        RETURN_LONG(-1)
    }
}


/**
 * TeaBot\CaptchaThread::run
 */
PHP_METHOD(TeaBot_CaptchaThread, run)
{
}


/**
 * TeaBot\CaptchaThread::cancel
 */
PHP_METHOD(TeaBot_CaptchaThread, cancel)
{
    zend_long index;
    ZEND_PARSE_PARAMETERS_START(1, 1)
         Z_PARAM_LONG(index)
    ZEND_PARSE_PARAMETERS_END();

    if ((index >= 0) && (index < MAX_QUEUE)) {
        queues[index].cancel = true;
    }
}

static const char fdc_form[] = "%s/%s/%d";
static const char calculus_lock_form[] = "/tmp/telegram/calculus_lock/%s";

static void *calculus_queue_dispatch(captcha_queue *qw)
{
    tgcurl_res res;
    register char *ectmp;
    char fdc[256];

    zend_object fci_obj;
    zend_fcall_info fci = empty_fcall_info;
    zend_fcall_info_cache fci_cache = empty_fcall_info_cache;

    debug_print("qw->type = %s\n", qw->type);
    debug_print("qw->sleep_time = %d\n", (int)qw->sleep_time);
    debug_print("qw->user_id = %d\n", (int)qw->user_id);
    debug_print("qw->chat_id = %s\n", qw->chat_id);
    debug_print("qw->join_msg_id = %d\n", (int)qw->join_msg_id);
    debug_print("qw->captcha_msg_id = %d\n", (int)qw->captcha_msg_id);
    debug_print("qw->welcome_msg_id = %d\n", (int)qw->welcome_msg_id);
    debug_print("qw->banned_hash = %s\n", qw->banned_hash);
    debug_print("qw->mention = %s\n", qw->mention);
    debug_print("Sleeping for %d...\n", (int)qw->sleep_time);

    if (qw->sleep_time < 0) {
        qw->sleep_time *= -1;
    }

    while (qw->sleep_time--) {
        sleep(1);
        if (qw->cancel) break;
    }

    debug_print("Sleep done!\n");

    if (!qw->cancel) {
        struct stat st;
        char *ptrx, *ptry, *ptrz,
            kick_msg_id[64],
            tmp[2048],
            payload[sizeof(tmp) + 4096];

        sprintf(fdc, fdc_form, captcha_dir, qw->chat_id, qw->user_id);
        debug_print("Checking fdc file %s...\n", fdc);

        // if (stat(fdc, &st) < 0) {
        //     debug_print("File does not exist\n");
        //     goto ret;
        // } else {
        //     debug_print("File exist\n");
        // }


        // Kick user from the group.
        sprintf(payload, "chat_id=%s&user_id=%d",
            qw->chat_id, (int)qw->user_id);
        res = tgExe("kickChatMember", payload);
        debug_print("kickChatMember: %s\n", res.data);
        free(res.data);


        // Delete fdc file.
        unlink(fdc);


        // Send kick messgae.
        sprintf(tmp,
            "%s has been kicked from the group due to failed to answer the captcha.",
            qw->mention);
        ectmp = teabot_urlencode(tmp, strlen(tmp));
        sprintf(payload, "chat_id=%s&parse_mode=HTML&text=%s", qw->chat_id, ectmp);
        res = tgExe("sendMessage", payload);
        printf("Kicked message: %s\n", res.data);
        free(ectmp);


        // Get kick message id.
        if (!(ptrx = strstr(res.data, "message_id\":"))) goto ret_clr_res;
        ptrx += 12;
        if (!(ptry = strstr(ptrx, ",")))  goto ret_clr_res;
        *ptry = '\0';
        strcpy(kick_msg_id, ptrx);
        free(res.data);

        clear_del_queue(qw);

        goto ret;

        ret_clr_res:
        free(res.data);

    } else {
        debug_print("Job cancelled!\n");
    }


ret:
    if (qw->banned_hash) {
        sprintf(fdc, calculus_lock_form, qw->banned_hash);
        debug_print("Deleting calculus banned hash %s...\n", fdc);
        unlink(fdc);
    }

    return NULL;
}


static bool isinum(char *str)
{
    bool ret = true;

    while (*str) {
        if (((*str) < '0') || ((*str) > '9')) {
            ret = false;
            break;
        }
        str++;
    }

    return ret;
}


struct del_msg_qw
{
    bool busy;
    pthread_t thread;
    struct dirent *file;
    char *dir;
    captcha_queue *qw;
};

static void *del_exmsg(void *ptr)
{
    #define ww ((struct del_msg_qw *)ptr)

    tgcurl_res res;
    char payload[1024];

    sprintf(payload, "chat_id=%s&message_id=%s",
        ww->qw->chat_id, ww->file->d_name);
    res = tgExe("deleteMessage", payload);

    printf("delete_message: %s\n", res.data);

    sprintf(payload, "%s/%s", ww->dir, ww->file->d_name);
    unlink(payload);

    free(res.data);
    free(ww->file);

    ww->busy = false;

    return NULL;
    #undef ww
}


static void clear_del_queue(captcha_queue *qw)
{
    #define del_thread_amt 3

    bool got_ch;
    int i, n, p = 0;
    char cmpt[1024], delMsgDir[1024] = "testdir";
    struct del_msg_qw qww[del_thread_amt];
    struct dirent **namelist;

    // sprintf(delMsgDir, "%s/%s/delete_msg_queue/%d",
    //     captcha_dir, qw->chat_id, qw->user_id);

    printf("Dir: %s\n", delMsgDir);
    
    memset(&qww, 0, sizeof(qww));

    n = scandir(delMsgDir, &namelist, NULL, alphasort);
    if (n == -1) return;

    while (n--) {
        if ((n > 1) && (isinum(namelist[n]->d_name))) {

            got_ch = false;

            while (!got_ch) {
                for (i = 0; i < del_thread_amt; ++i) {
                    if (!qww[i].busy) {
                        qww[i].busy = true;
                        got_ch = true;
                        break;
                    }
                }
                usleep(10000);
            }

            qww[i].dir = delMsgDir;
            qww[i].file = namelist[n];
            qww[i].qw = qw;

            printf("%s\n", namelist[n]->d_name);

            pthread_create(&(qww[i].thread), NULL,
                (void * (*)(void *))del_exmsg, (void *)&(qww[i]));
            pthread_detach(qww[i].thread);
        } else {

            sprintf(cmpt, "%s/%s", delMsgDir, namelist[n]->d_name);
            unlink(cmpt);

            free(namelist[n]);
        }
    }

    free(namelist);

    #undef del_thread_amt
}


static size_t internalTgExeWrite(void *content, size_t sz, size_t nmemb, void *ctx)
{
    register tgcurl_res *res = (tgcurl_res *)ctx;
    register size_t op = res->len, rsize = sz * nmemb;

    res->len += rsize;
    if ((res->len + 2048) >= res->allocated) {
        res->data = (char *)realloc(res->data,
            res->allocated + 2048 + rsize);
        res->allocated += 2048 + rsize;
    }

    memcpy(&(res->data[op]), content, rsize);
    return rsize;
}


static tgcurl_res tgExe(char *method, char *payload)
{
    register CURL *curl;
    CURLcode ret;
    char url[1024];
    tgcurl_res res;

    curl = curl_easy_init();
    if (curl) {

        res.len = 0;
        res.allocated = 8096;
        res.data = (char *)malloc(res.allocated);

        sprintf(url, "https://api.telegram.org/bot%s/%s", token, method);

        curl_easy_setopt(curl, CURLOPT_URL, url);
        curl_easy_setopt(curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_easy_setopt(curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, internalTgExeWrite);
        curl_easy_setopt(curl, CURLOPT_WRITEDATA, &res);
        curl_easy_setopt(curl, CURLOPT_POSTFIELDS, payload);
        curl_easy_setopt(curl, CURLOPT_POSTFIELDSIZE, (long)strlen(payload));
        
        ret = curl_easy_perform(curl);

        if (ret != CURLE_OK) {
            printf("curl_easy_perform() failed: %s\n",
                curl_easy_strerror(ret));
        }

        curl_easy_cleanup(curl);
    } else {
        memset(&res, 0, sizeof(res));
        printf("Error: cannot initialize curl thread\n");
    }

    res.data[res.len] = 0;
    return res;
}


static void teabot_zend_release_fcall_info_cache(zend_fcall_info_cache *fcc)
{
    if (fcc->function_handler &&
        (fcc->function_handler->common.fn_flags & ZEND_ACC_CALL_VIA_TRAMPOLINE)) {
        if (fcc->function_handler->common.function_name) {
            zend_string_release_ex(fcc->function_handler->common.function_name, 0);
        }
        zend_free_trampoline(fcc->function_handler);
    }
    fcc->function_handler = NULL;
}


static unsigned char *teabot_urlencode(const char *s, size_t len)
{
    register unsigned char c;
    unsigned char *to, *start;
    unsigned char const *from, *end;

    if (len == 0) {
        len = strlen(s);
    }

    from = (unsigned char *)s;
    end = (unsigned char *)s + len;
    to = (unsigned char *)malloc((len * 3) + 1);
    start = to;

    while (from < end) {
        c = *from++;
        if (c == ' ') {
            *to++ = '+';
#ifndef CHARSET_EBCDIC
        } else if ((c < '0' && c != '-' && c != '.') ||
                   (c < 'A' && c > '9') ||
                   (c > 'Z' && c < 'a' && c != '_') ||
                   (c > 'z')) {
            to[0] = '%';
            to[1] = hexchars[c >> 4];
            to[2] = hexchars[c & 15];
            to += 3;
#else /*CHARSET_EBCDIC*/
        } else if (!isalnum(c) && strchr("_-.", c) == NULL) {
            /* Allow only alphanumeric chars and '_', '-', '.'; escape the rest */
            to[0] = '%';
            to[1] = hexchars[os_toascii[c] >> 4];
            to[2] = hexchars[os_toascii[c] & 15];
            to += 3;
#endif /*CHARSET_EBCDIC*/
        } else {
            *to++ = c;
        }
    }
    *to = '\0';

    return start;
}

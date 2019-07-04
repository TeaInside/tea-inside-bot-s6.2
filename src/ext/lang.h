
#ifndef TEABOT_LANG_H
#define TEABOT_LANG_H

typedef struct _lang_entry {
	char *name;
	char *value;
} lang_entry;

#define ADD_LANG_ENTRY(_name, _value) \
	{	\
		.name = _name,	\
		.value = _value	\
	}

#include <string.h>
#include "lang/en.h"
#include "lang/id.h"

const char langlist[][2] = {"en", "id", "jp"};

#define GET_LANG(lang, target, key) \
	for (size_t i = 0; i < (sizeof(lang##_lang_entry)/sizeof(lang##_lang_entry[0])); i++) { \
		if (!strcmp(key, lang##_lang_entry[i].name)) { \
			target = lang##_lang_entry[i].value; \
			break; \
		} \
	}

#define GET_LANG_FALLBACK(lang, target, key) \
	for (size_t i = 0; i < (sizeof(lang##_lang_entry)/sizeof(lang##_lang_entry[0])); i++) { \
		if (!strcmp(key, lang##_lang_entry[i].name)) { \
			target = lang##_lang_entry[i].value; \
			break; \
		} \
	}

#endif

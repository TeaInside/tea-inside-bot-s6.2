
#ifndef TEABOT_LANG_H
#define TEABOT_LANG_H

typedef struct _lang_entry {
	char *name;
	char *value;
} lang_entry;

#define ADD_LE(_name, _value) \
	{	\
		.name = _name,	\
		.value = _value	\
	}

#include <string.h>

#define GET_LANG(lang, target, key) \
	for (size_t i = 0; i < (sizeof(lang##_lang_entry)/sizeof(lang##_lang_entry[0])); i++) { \
		if (!strcmp(key, lang##_lang_entry[i].name)) { \
			target = lang##_lang_entry[i].value; \
			break; \
		} \
	}

#define GET_LANG_FALLBACK(lang, target, key) \
	if (!strcmp(lang, "en")) { \
		GET_LANG(en, target, key) \
	} else \
	if (!strcmp(lang, "id")) { \
		GET_LANG(id, target, key) \
	}

#endif

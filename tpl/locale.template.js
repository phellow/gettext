var __OBJECT_NAME__ = {
	code: __LOCALE_CODE__,
	data: __LOCALE_DATA__,
	rep: '__REPLACEMENT_REGEX__',

	replace: function (text, replacements) {
		if (replacements) {
			for (var s in replacements) {
				var regex = new RegExp(this.rep.replace('[var]', s.replace(/[-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")));
				text = text.replace(regex, replacements[s]);
			}
		}
		return text;
	},

	__TRANSLATE_FUNCTION_NAME__: function (text, replacements) {
		if (this.data[text] != undefined) {
			text = this.data[text];
			if (typeof text == 'object') {
				text = text[0];
			}
		}
		return this.replace(text, replacements);
	},

	__TRANSLATE_PLURALS_FUNCTION_NAME__: function (singularText, pluralText, n, replacements) {
		var nplurals = 0;
		var plural = 0;
		var text = '';

		__PLURAL_FORMS__;

		if (typeof plural == 'boolean') {
			plural = plural ? 1 : 0;
		}
		if (plural < 0) {
			plural = 0;
		}
		if (plural > nplurals - 1) {
			plural = nplurals - 1;
		}

		if (this.data[singularText] != undefined && this.data[singularText][plural] != undefined) {
			text = this.data[singularText][plural];
		} else {
			text = plural > 0 ? pluralText : singularText;
		}

		return this.replace(text, replacements);
	}
};
$(document).foundation();

WebFont.load({
	google: {
		families: ['Droid Sans']
	},
	custom: {
		families: ['FontAwesome'],
		urls: [baseUrl + '/assets/css/font-awesome.min.css']
	}
});

var UI = {
	form: {
		element: null,
		errors: {
			add: function(text) {
				if (!UI.form.element) {
					return;
				}

				if (!UI.form.element.parent().find('li.errors').length) {
					UI.form.element.parent().addClass('has-error').append('<ul class="errors" />');
				}

				var errorId = UI.form.element.parent().find('ul.errors li').length++;
				$('<li />').attr('id', errorId).text(text).appendTo(UI.form.element.next('ul.errors'));

				return errorId;
			},
			remove: function(id) {
				if (!UI.form.element) {
					return;
				}

				if (id instanceof Array) {
					for (var i = id.length - 1; i >= 0; i--) {
						UI.form.element.parent().find('ul.errors li#' + id[i]).remove();
					};

					return;
				}

				UI.form.element.parent().find('ul.errors li#' + id).remove();

				if (!UI.form.element.parent().find('ul.errors li').length) {
					UI.form.element.parent().removeClass('has-error').find('ul.errors').remove();
				}
			}
		}
	}
}

$('[href^="#void"]').click(function(e) {
	e.preventDefault();
});

$('[data-remote-submit]').click(function(e) {
	e.preventDefault();

	$($(this).data('remote-submit')).submit();
});

$('[data-confirm-redirect]').click(function(e) {
	e.preventDefault();

	if (confirm($(this).data('confirm-redirect'))) {
		window.location = $(this).attr('href');
	}
});

if ($('.form-with-slug').length) {
	var nameField = $('.form-with-slug input[name="name"]');
	var slugField = $('.form-with-slug input[name="slug"]');
	var errorId = null;

	UI.form.element = slugField;

	nameField.on('input', function() {
		var generatedSlug = nameField.val().toLowerCase().replace(/[^a-zA-Z0-9]+/g,'-');

		if (generatedSlug.slice(-1) == '-') {
			generatedSlug = generatedSlug.slice(0, -1)
		}

		while ($.inArray(generatedSlug, existing_slugs) !== -1) {
			var lastSlugNumber = parseFloat(generatedSlug.slice(-1));

			if (!isNaN(lastSlugNumber)) {
				lastSlugNumber++;
				generatedSlug = generatedSlug.slice(0, -2);
			}

			generatedSlug += '-' + (lastSlugNumber || '1');
		}

		slugField.val(generatedSlug);
		UI.form.errors.remove(errorId);
	});

	slugField.on('input', function() {
		var slug = slugField.val();

		if ($.inArray(slug, existing_slugs) === -1) {
			UI.form.errors.remove(errorId);
			return;
		}

		errorId = UI.form.errors.add('Slug already exists');
	});
}
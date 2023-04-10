/* eslint-disable no-console */
(function ($) {
	'use strict';

	// Trigger form submit
	$('#search-form-filter').on('change', function () {
		const filter = $('#search-form-filter');
		const result = $('.latest-posts-block');
		$.ajax({
			url: filter.attr('action'),
			data: filter.serialize(),
			type: filter.attr('method'),
			success(response) {
				result.html('');
				const res = JSON.parse(response);
				for (let i = 0; i < res.length; i++) {
					const div = $('<div />', {
						class: 'latest-post'
					});
					const permalink = $('<a />', {
						href: res[i].permalink,
						target: '_blank'
					});
					const title = $('<h4 />');
					title.text(res[i].title);
					const thumbnail = $('<img />', {
						src: res[i].thumbnail
					});
					const excerpt = $('<p />');
					excerpt.text(res[i].excerpt);
					permalink.append(title);
					div.append(permalink, thumbnail, excerpt);
					result.append(div);
				}
			},
			error(request, status, error) {
				console.log(request.responseText);
				console.log(error);
				console.log(status);
			},
		}).done(function () {
			console.log('ajax completed');
		});
	});
	// eslint-disable-next-line no-undef
})(jQuery);

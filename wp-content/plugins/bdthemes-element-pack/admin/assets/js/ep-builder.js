/* eslint-disable prettier/prettier */
!(function ($) {
	'use strict';

	function showModal() {
		$('#bdthemes-templates-builder-modal').show();
	}

	function hideModal() {
		$('#bdthemes-templates-builder-modal').hide();
	}

	function resetModalForm() {
		$('#bdthemes-templates-builder-modal form')[0].reset();
		$('#bdthemes-templates-builder-modal form .template_id').val('');
	}

	function setSubmitBtn(string) {
		$('#bdthemes-templates-builder-modal form .bdt-modal-submit-btn').val(
			string,
		);
	}

	function setError($this) {
		$this.addClass('input-error');
	}

	function removeError($this) {
		$('.input-error').removeClass('input-error');
	}

	$(document).on(
		'click',
		'#bdthemes-templates-builder-modal .bdt-modal-close-button',
		function (e) {
			hideModal();
		},
	);

	$(document).on(
		'click',
		'body.post-type-bdt-template-builder a.page-title-action',
		function (e) {
			e.preventDefault();
			resetModalForm();
			setSubmitBtn('Create Template');
			showModal();
		},
	);

	$(document).on(
		'submit',
		'#bdthemes-templates-builder-modal form',
		function (e) {
			e.preventDefault();
			var $serialized = $(this).serialize();
			removeError();

			$.ajax({
				url: ajaxurl,
				dataType: 'json',
				method: 'post',
				cache: false,
				data: {
					action: 'bdthemes_builder_create_template',
					data: $serialized,
				},
				success: function (response) {
					window.location.href = response.data.redirect;
				},
				error: function (errorThrown) {
					if (errorThrown.status == 422) {
						$.each(
							errorThrown.responseJSON.data.errors_arr,
							function (index, value) {
								setError($('#bdthemes-templates-builder-modal #' + index));
							},
						);
					}
				},
			});
		},
	);

	$(document).on(
		'click',
		'body.post-type-bdt-template-builder .row-actions .bdt-edit-action a',
		function (e) {
			e.preventDefault();
			removeError();
			resetModalForm();
			setSubmitBtn('Update Template');

			var templateId = 0;
			var parentColumn = $(this).parents(".column-title");


			$.ajax({
				url: ajaxurl,
				dataType: 'json',
				method: 'post',
				data: {
					action: 'bdthemes_builder_get_edit_template',
					template_id: $(this).data('id'),
				},
				success: function (response) {
					if (response.success) {
						$('#bdthemes-templates-builder-modal form .template_id')
							.val(response.data.id)
							.change();
						$('#bdthemes-templates-builder-modal form #template_name')
							.val(response.data.name)
							.change();
						$('#bdthemes-templates-builder-modal form #template_type')
							.val(response.data.type)
							.change();
						$('#bdthemes-templates-builder-modal form #template_status')
							.val(response.data.status)
							.change();

						// if #template_type is themes|header or themes|footer then show the .bdt-header-footer-option-container
						if (response.data.type === 'themes|header' || response.data.type === 'themes|footer') {
							$('.bdt-header-footer-option-container').show();
							$('.bdt-template-modalinput-condition_a').val(response.data.condition_a).change();
							$('.bdt-template-modalinput-condition_singular').val(response.data.condition_singular).change();
						}
					}

					templateId = parentColumn.find(".hidden").attr("id").split("_")[1];

					var singularIdInput = $(".bdt-template-modalinput-condition_singular_id");
					$.ajax({
						url: window.ElementPackConfigBuilder.resturl + 'get-singular-list',
						dataType: "json",
						data: { ids: String(response.data.condition_singular_id) || "" },
					}).then(function (response) {
						if (response !== null && response.results.length > 0) {
							singularIdInput.html(" ");
							$.each(response.results, function (index, item) {
								var option = new Option(item.text, item.id, true, true);
								singularIdInput.append(option).trigger("change");
							});
							// singularIdInput.trigger({ type: "select2:select", params: { data: response } });
						}
					});

					showModal();
				},
				error: function (errorThrown) {
					console.log(errorThrown);
					if (errorThrown.status == 422) {
					}
				},
			});
		},
	);

	$(document).ready(function () {

		$('#bdthemes-templates-builder-modal form #template_type').on('change', function () {
			if ($(this).val() === 'themes|header' || $(this).val() === 'themes|footer') {
				$('.bdt-header-footer-option-container').show();
			} else {
				$('.bdt-header-footer-option-container').hide();
			}
		});

		$(".bdt-template-modalinput-condition_singular_id").select2({
			ajax: {
				url: window.ElementPackConfigBuilder.resturl + 'get-singular-list',
				dataType: "json",
				data: function (params) {
					return { s: params.term };
				},
			},
			cache: true,
			placeholder: "--",
			dropdownParent: $(".bdt-template-modalinput-condition_singular-container"),
		});

		$(document).on('change', '.bdt-template-modalinput-condition_a', function (e) {
			var selectedCondition = $(this).val();
			var singularContainer = $(".bdt-template-modalinput-condition_singular-container");

			if (selectedCondition === "singular") {
				singularContainer.show();
			} else {
				singularContainer.hide();
			}
		});

		$(".bdt-template-modalinput-condition_singular").on("change", function () {
			var selectedConditionSingular = $(this).val();
			var singularIdContainer = $(".bdt-template-modalinput-condition_singular_id-container");

			if (selectedConditionSingular === "selective") {
				singularIdContainer.show();
			} else {
				singularIdContainer.hide();
			}
		});


	});

})(jQuery);

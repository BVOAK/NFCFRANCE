(function ($) {
    const megaMenu = {
      body: $("body"),
      element: document.getElementById("menu-to-edit"),
      settingModal: $("#bdt-ep-megamenu-modal"),
      init() {
        this.initBodyEvent();
        this.initPlugins();
        this.megaMenuSettingEvent();
        this.onSaveSettings();
        this.openElementorEditor();
        this.closeElementorEditor();

      },
      megaMenuModal(selector = "") {
        return $("#bdt-ep-megamenu-modal " + selector);
      },
      elementorEditor(selector = "") {
        return $("#ep-megamenu-content " + selector);
      },
      elementorEditorConfirmation(selector = "") {
        return $("#ep-megamenu-editor-confirmation " + selector);
      },
      initPlugins() {
        //color picker
        megaMenu.megaMenuModal(".ep-menu-colorpicker").wpColorPicker();
        AestheticIconPicker({
          selector: "#icon-picker-wrap",
          onClick: "#select-icon",
        });
      },
      megaMenuSettingEvent() {
        // Add class on active Item
        megaMenu.element.addEventListener("cardupdate", function (megamenu) {
          if (megamenu.detail.isEnabled) {
            $("#menu-item-" + megamenu.detail.menuID).addClass(
              "ep-megamenu-active"
            );
          } else {
            $("#menu-item-" + megamenu.detail.menuID).removeClass(
              "ep-megamenu-active"
            );
          }
        });

        $.each(megaMenuBuilder.items, function (megamenu, menuItem) {
          $(menuItem).length && $(menuItem).addClass("ep-megamenu-active");
        });

        megaMenu.megaMenuModal("#bdt-item-enable").on("change", function () {
          if ($(this).is(":checked")) {
            megaMenu.megaMenuModal("#ep-content-trigger").prop("disabled", !1);
            megaMenu
              .megaMenuModal("#ep-megamenu-toggler")
              .addClass("ep_megamenu_enabled");
          } else {
            megaMenu
              .megaMenuModal("#ep-megamenu-toggler")
              .removeClass("ep_megamenu_enabled");
            megaMenu.megaMenuModal("#ep-content-trigger").prop("disabled", !0);
          }
        });

        megaMenu
          .megaMenuModal("#ep-content-width-type")
          .on("change", function () {
            if (this.value === "custom_width") {
              megaMenu.megaMenuModal("#ep-megamenu-custom-content").show();
            } else {
              megaMenu.megaMenuModal("#ep-megamenu-custom-content").hide();
            }
          });
      },

      onSaveSettings() {
        // save menu item
        megaMenu.megaMenuModal(".ep-item-save").on("click", function () {
          var spinner = $(this).parent().find(".spinner"),
            dataSettings = {
              action: "ep_save_menu_item_settings",
              nonce: megaMenuBuilder.nonce,
              settings: {
                menu_id: megaMenu.megaMenuModal("#ep-modal-menu-id").val(),
                menu_has_child: megaMenu.megaMenuModal("#ep-has-child").val(),
                menu_enable: megaMenu
                  .megaMenuModal("#bdt-item-enable:checked")
                  .val(),
                menu_trigger_effect: megaMenu
                  .megaMenuModal("#mega-menu-trigger-effect")
                  .val(),
                menu_mobile_content_type: megaMenu
                  .megaMenuModal("#mobile_submenu_content_type")
                  .val(),
                menu_width_type: megaMenu
                  .megaMenuModal("#ep-content-width-type")
                  .val(),
                custom_menu_position: megaMenu
                  .megaMenuModal("#ep-megamenu-custom-position-value")
                  .val(),
                menu_badge_label: megaMenu
                  .megaMenuModal("#ep-badge-text-field")
                  .val(),
                menu_custom_width: megaMenu
                  .megaMenuModal("#ep-custom-width-value")
                  .val(),
                megamenu_badge_color: megaMenu
                  .megaMenuModal("#ep-badge-text-color")
                  .val(),
                menu_badge_bgcolor: megaMenu
                  .megaMenuModal("#ep-badge-text-bgcolor")
                  .val(),
                megamenu_icon: megaMenu.megaMenuModal("#icon_value").val(),
                megamenu_icon_library: megaMenu
                  .megaMenuModal("#icon_library")
                  .val(),
                megamenu_icon_color: megaMenu
                  .megaMenuModal("#ep-icon-color")
                  .val(),
              },
              nocache: Math.floor(Date.now() / 1e3),
            };

          spinner.addClass("loading");

          $.ajax({
            url: ajaxurl,
            type: "post",
            data: dataSettings,
            success: function (response) {
              megaMenu.megaMenuModal(".ep-save-notice").fadeIn("slow");
              megaMenu
                .megaMenuModal(".ep-save-notice")
                .html(response["message"]);

              setTimeout(function () {
                megaMenu.megaMenuModal(".ep-save-notice").fadeOut("slow");
              }, 1500);

              spinner.removeClass("loading");
            },
          });
          dataSettings = {
            menuID: $("#ep-modal-menu-id").val(),
            isEnabled: $("#bdt-item-enable:checked").val(),
          };

          megaMenu.element.dispatchEvent(
            new CustomEvent("cardupdate", { detail: dataSettings })
          );
        });
      },

      openElementorEditor() {
        // OPEN ELEMENTOR EDITOR ON IFRAME
        megaMenu.megaMenuModal("#ep-content-trigger").on("click", function () {
          var iframeURL,
            menu_id = megaMenu.megaMenuModal("#ep-modal-menu-id").val();
          $.ajax({
            url: ajaxurl,
            type: "post",
            data: {
              action: "ep_get_content_editor",
              key: menu_id,
            },
            success: function (response) {
              iframeURL = response;
              megaMenu.elementorEditor("#ep-megamenu-iframe").empty();
              megaMenu
                .elementorEditor("#ep-megamenu-iframe")
                .attr("src", iframeURL);
              bdtUIkit.modal(megaMenu.elementorEditor()).show();
            },
          });
        });
      },

      closeElementorEditor() {
        megaMenu
          .elementorEditor(".close-mega-menu-modal")
          .on("click", function (e) {
            e.preventDefault();
            const megamenuIframe =
              document.getElementById("ep-megamenu-iframe");
            const elementorContent =
              megamenuIframe.contentWindow || megamenuIframe.contentDocument;

            if (
              !elementorContent
                .jQuery("#elementor-panel-saver-button-publish")
                .hasClass("elementor-disabled")
            ) {
              bdtUIkit
                .modal(megaMenu.elementorEditorConfirmation(), { stack: true })
                .show();
            } else {
              setTimeout(function () {
                elementorContent.jQuery(elementorContent).off("beforeunload");
                bdtUIkit
                  .modal(megaMenu.elementorEditorConfirmation(), {
                    stack: true,
                  })
                  .hide();
                bdtUIkit.modal(megaMenu.elementorEditor()).hide();
              }, 400);
            }
          });

        megaMenu
          .elementorEditorConfirmation(".confirmation-ok")
          .on("click", function (e) {
            e.preventDefault();
            const megamenuIframe =
              document.getElementById("ep-megamenu-iframe");
            const elementorContent =
              megamenuIframe.contentWindow || megamenuIframe.contentDocument;
            elementorContent
              .jQuery("#elementor-panel-saver-button-publish")
              .trigger("click");

            setTimeout(function () {
              elementorContent.jQuery(elementorContent).off("beforeunload");
              bdtUIkit
                .modal(megaMenu.elementorEditorConfirmation(), { stack: true })
                .hide();
              bdtUIkit.modal(megaMenu.elementorEditor()).hide();
            }, 1000);
          });

        megaMenu
          .elementorEditorConfirmation(".confirmation-cancel")
          .on("click", function (e) {
            e.preventDefault();
            const megamenuIframe =
              document.getElementById("ep-megamenu-iframe");
            const elementorContent =
              megamenuIframe.contentWindow || megamenuIframe.contentDocument;

            setTimeout(function () {
              elementorContent.jQuery(elementorContent).off("beforeunload");
              bdtUIkit
                .modal(megaMenu.elementorEditorConfirmation(), { stack: true })
                .hide();
              bdtUIkit.modal(megaMenu.elementorEditor()).hide();
            }, 400);
          });
      },
      initBodyEvent() {
        const $navmenuHeader = $("#nav-menu-header");
        const $menuToEdit = $("#menu-to-edit");

        const toggleMegaMenu = () => {
          const isEnabled = $("#ep-is-metabox-enabled").is(":checked");
          megaMenu.body
            .toggleClass("ep_megamenu_enabled", isEnabled)
            .toggleClass("ep_megamenu_disabled", !isEnabled);
        };

        // Observe changes in the nav menu header
        const observer = new MutationObserver(() =>
          setTimeout(toggleMegaMenu, 200)
        );
        if ($navmenuHeader.length)
          observer.observe($navmenuHeader[0], {
            childList: true,
            subtree: true,
          });

        // Observe changes in the menu items
        const menuObserver = new MutationObserver(() => {
          $menuToEdit.find("li.menu-item").each(function () {
            if ($(this).find(".ep-menu-trigger-btn").length === 0) {
              $(this).find(".item-title").append(`
                    <a class="ep-menu-trigger-btn" href="#bdt-ep-megamenu-modal">
                        <span class="ep-megeamenu">Edit</span>
                        <span class="ep-megeamenu-edit">Edit</span>
                    </a>
                `);
            }
          });
        });

        if ($menuToEdit.length)
          menuObserver.observe($menuToEdit[0], {
            childList: true,
            subtree: true,
          });

        // Toggle megamenu based on checkbox state
        $navmenuHeader.on(
          "change.ekit",
          "#ep-is-metabox-enabled",
          toggleMegaMenu
        );

        // Insert mega menu trigger button and trigger change event
        $(window.ep_mega_menu_trigger_button)
          .insertAfter("#nav-menu-header #menu-name")
          .parent()
          .find("#ep-megamenu-switcher")
          .trigger("change.ekit");

        // Handle click event on mega menu trigger button
        $menuToEdit.on("click", ".ep-menu-trigger-btn", function (e) {
          e.preventDefault();

          const $menuItem = $(this).closest("li.menu-item");
          const menuId = parseInt($menuItem.attr("id").match(/\d+/)[0], 10);
          const isDepthZero = $menuItem.hasClass("menu-item-depth-0");

          // Toggle modal tabs based on depth
          megaMenu.settingModal.toggleClass("ep-menu-has-child", !isDepthZero);
          const tabSuffix = isDepthZero ? "content" : "icon";
          $(".ep_menu_control_nav > li, .attr-tab-pane").removeClass(
            "attr-active"
          );
          $(`#attr_${tabSuffix}_nav, #attr_${tabSuffix}_tab`).addClass(
            "attr-active"
          );

          // Update modal with menu item data
          $("#ep-modal-menu-id").val(menuId);
          $("#ep-has-child").val(isDepthZero ? 0 : 1);

          const requestData = {
            action: "ep_get_menu_item_settings",
            menu_id: menuId,
            menu_width_type: megaMenu
              .megaMenuModal("#ep-content-width-type")
              .val(),
            custom_menu_position: megaMenu
              .megaMenuModal("#ep-megamenu-custom-position-value")
              .val(),
            menu_badge_label: megaMenu
              .megaMenuModal("#ep-badge-text-field")
              .val(),
            menu_custom_width: megaMenu
              .megaMenuModal("#ep-custom-width-value")
              .val(),
            megamenu_badge_color: megaMenu
              .megaMenuModal("#ep-badge-text-color")
              .val(),
            menu_badge_bgcolor: megaMenu
              .megaMenuModal("#ep-badge-text-bgcolor")
              .val(),
            megamenu_icon: megaMenu.megaMenuModal("#icon_value").val(),
            megamenu_icon_library: megaMenu
              .megaMenuModal("#icon_library")
              .val(),
            megamenu_icon_color: megaMenu.megaMenuModal("#ep-icon-color").val(),
            nocache: Math.floor(Date.now() / 1e3),
          };

          // Fetch menu item settings via AJAX
          $.post(
            ajaxurl,
            requestData,
            (response) => {
              // Update modal fields with response data
              const fieldsToUpdate = [
                {
                  selector: "#ep-badge-text-field",
                  value: response.menu_badge_label,
                },
                {
                  selector: "#ep-custom-width-value",
                  value: response.menu_custom_width,
                },
                {
                  selector: "#ep-badge-text-color",
                  value: response.megamenu_badge_color,
                },
                {
                  selector: "#ep-badge-text-bgcolor",
                  value: response.menu_badge_bgcolor,
                },
                { selector: "#icon_value", value: response.megamenu_icon },
                {
                  selector: "#icon_library",
                  value: response.megamenu_icon_library,
                },
                {
                  selector: "#ep-icon-color",
                  value: response.megamenu_icon_color,
                },
                {
                  selector: "#mega-menu-trigger-effect",
                  value: response.menu_trigger_effect,
                },
                {
                  selector: "#mobile_submenu_content_type",
                  value: response.menu_mobile_content_type,
                },
              ];

              fieldsToUpdate.forEach((field) =>
                megaMenu.megaMenuModal(field.selector).val(field.value)
              );

              // Update width type and position
              megaMenu
                .megaMenuModal("#ep-content-width-type")
                .val(response.menu_width_type || "default_width")
                .change();
              megaMenu
                .megaMenuModal("#ep-megamenu-custom-position-value")
                .val(response.custom_menu_position || "bottom-left")
                .change();

              // Toggle menu enable switcher
              megaMenu
                .megaMenuModal("#bdt-item-enable")
                .prop("checked", !!response.menu_enable)
                .trigger("change");
            },
            "json"
          );

          // Show the modal
          bdtUIkit.modal(megaMenu.settingModal).show();
        });
      },
    };

    megaMenu.init();

})(jQuery, bdtUIkit);

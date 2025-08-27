/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/frontend/js/woocommerce/swatches.js":
/*!********************************************************!*\
  !*** ./assets/src/frontend/js/woocommerce/swatches.js ***!
  \********************************************************/
/***/ (() => {

eval("window.addEventListener('DOMContentLoaded', function () {\n  window.uicore_swatch_action = function (event) {\n    //get the current attribute's name, value and store it\n    var attribute_name = jQuery(event.currentTarget).data('attribute-name');\n\n    //get the value of the clicked swatch child\n    var value = jQuery(event.currentTarget).data('value');\n\n    //set the select with the id of the clicked swatch attribute to same value\n    jQuery('select[id=\"' + attribute_name + '\"]').val(value).trigger('change');\n\n    //set the active class to the clicked swatch and remove from siblings\n    jQuery(event.currentTarget).addClass('selected').siblings().removeClass('selected');\n  };\n\n  // Listen to click on swatches list\n  jQuery('.uicore-swatch').on('click', uicore_swatch_action);\n});\n\n//# sourceURL=webpack://uicore-framework/./assets/src/frontend/js/woocommerce/swatches.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./assets/src/frontend/js/woocommerce/swatches.js"]();
/******/ 	
/******/ })()
;
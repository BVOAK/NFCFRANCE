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

/***/ "./assets/src/frontend/js/woocommerce/product-tabs.js":
/*!************************************************************!*\
  !*** ./assets/src/frontend/js/woocommerce/product-tabs.js ***!
  \************************************************************/
/***/ (() => {

eval("/**\r\n *  Product Tabs Accordion script\r\n */\n\ndocument.addEventListener('click', function (e) {\n  if (e.target.classList.contains('ui-accordion-header')) {\n    var panel = e.target.nextElementSibling;\n    var isOpen = panel.style.display === 'block';\n\n    // Close all panels\n    document.querySelectorAll('.woocommerce-Tabs-panel').forEach(function (p) {\n      p.style.display = 'none';\n    });\n\n    // Remove active class from all headers\n    document.querySelectorAll('.ui-accordion-header').forEach(function (h) {\n      h.classList.remove('ui-active');\n    });\n\n    // Open the clicked panel\n    if (!isOpen) {\n      panel.style.display = 'block';\n      e.target.classList.add('ui-active');\n    }\n  }\n});\n\n//# sourceURL=webpack://uicore-framework/./assets/src/frontend/js/woocommerce/product-tabs.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./assets/src/frontend/js/woocommerce/product-tabs.js"]();
/******/ 	
/******/ })()
;
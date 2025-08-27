/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
var dummy;
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/settings/main.js":
/*!*************************************!*\
  !*** ./assets/src/settings/main.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.esm.js\");\n/* harmony import */ var _App_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./App.vue */ \"./assets/src/settings/App.vue\");\n\n\n// import router from './router'\n\nvue__WEBPACK_IMPORTED_MODULE_1__[\"default\"].config.productionTip = false;\n\n/* eslint-disable no-new */\nnew vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]({\n  el: '#uianim-settings',\n  render: function render(h) {\n    return h(_App_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]);\n  }\n});\n\n//# sourceURL=webpack://dummy/./assets/src/settings/main.js?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/App.vue?vue&type=script&lang=js":
/*!**************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/App.vue?vue&type=script&lang=js ***!
  \**************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _components_select_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/select.vue */ \"./assets/src/settings/components/select.vue\");\n/* harmony import */ var vue_color__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue-color */ \"./node_modules/vue-color/dist/vue-color.min.js\");\n/* harmony import */ var vue_color__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(vue_color__WEBPACK_IMPORTED_MODULE_1__);\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  name: 'App',\n  components: {\n    SelectComponent: _components_select_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n    colorPicker: vue_color__WEBPACK_IMPORTED_MODULE_1__.Chrome\n  },\n  data: function data() {\n    return {\n      disable: window.uianim.disable == 'true' ? true : false,\n      style: window.uianim.style,\n      debounce: null,\n      scroll: window.uianim.scroll == 'true' ? true : false,\n      connect: '',\n      notification: '',\n      animations_page: window.uianim.animations_page,\n      animations_page_duration: window.uianim.animations_page_duration,\n      animations_page_color: window.uianim.animations_page_color,\n      animations_page_color_picker: window.uianim.animations_page_color,\n      animations_preloader: window.uianim.animations_preloader,\n      animations_preloader_text: window.uianim.animations_preloader_text,\n      animations_preloader_color: window.uianim.animations_preloader_color,\n      animations_preloader_text_color: window.uianim.animations_preloader_text_color,\n      animations_preloader_words: window.uianim.animations_preloader_words,\n      isError: false,\n      durations: [{\n        name: 'Normal',\n        value: 'normal'\n      }, {\n        name: 'Fast',\n        value: 'fast'\n      }, {\n        name: 'Slow',\n        value: 'slow'\n      }],\n      preloaderList: window.uianim.preloaderList,\n      pageTransitions: [{\n        name: 'None',\n        value: 'none'\n      }, {\n        name: 'Fade',\n        value: 'fade'\n      }, {\n        name: 'Fade In',\n        value: 'fade in'\n      }, {\n        name: 'Reveal',\n        value: 'reveal'\n      }, {\n        name: 'Fade and Reveal',\n        value: 'fade and reveal'\n      }, {\n        name: 'Columns',\n        value: 'columns'\n      }, {\n        name: 'Multilayer',\n        value: 'multilayer'\n      }],\n      styleList: [{\n        name: 'Default',\n        value: ''\n      }, {\n        name: 'Creative',\n        value: 'style1'\n      }, {\n        name: 'Snappy',\n        value: 'style2'\n      }, {\n        name: 'Soft',\n        value: 'style3'\n      }, {\n        name: 'Laser',\n        value: 'style4'\n      }, {\n        name: 'Elastic',\n        value: 'style5'\n      }, {\n        name: 'Linear',\n        value: 'style6'\n      }, {\n        name: 'Magic',\n        value: 'style7'\n      }, {\n        name: 'SCI-FI',\n        value: 'style8'\n      }]\n    };\n  },\n  beforeMount: function beforeMount() {\n    console.log(this.animations_preloader);\n    if (this.animations_page_color == 'Primary') {\n      this.animations_page_color = '#262529'; //\n    }\n  },\n  watch: {\n    disable: function disable(val) {\n      this.updateSettings('uianim_disable', val);\n    },\n    style: function style(val) {\n      this.updateSettings('uianim_style', val);\n    },\n    scroll: function scroll(val) {\n      this.updateSettings('uianim_scroll', val);\n    },\n    animations_page: function animations_page(val) {\n      this.updateSettings('animations_page', val);\n    },\n    animations_page_duration: function animations_page_duration(val) {\n      this.updateSettings('animations_page_duration', val);\n    },\n    animations_page_color: function animations_page_color(val) {\n      this.updateSettings('animations_page_color', val);\n    },\n    animations_preloader: function animations_preloader(val) {\n      this.updateSettings('animations_preloader', val);\n    },\n    animations_preloader_text: function animations_preloader_text(val) {\n      this.updateSettings('animations_preloader_text', val);\n    },\n    animations_preloader_color: function animations_preloader_color(val) {\n      this.updateSettings('animations_preloader_color', val);\n    },\n    animations_preloader_text_color: function animations_preloader_text_color(val) {\n      this.updateSettings('animations_preloader_text_color', val);\n    },\n    animations_preloader_words: function animations_preloader_words(val) {\n      this.updateSettings('animations_preloader_words', val);\n    }\n  },\n  methods: {\n    updateFromPicker: function updateFromPicker(color, prop) {\n      var _this = this;\n      var val = null;\n      if (color.rgba.a == 1) {\n        val = color.hex;\n      } else {\n        val = 'rgba(' + color.rgba.r + ', ' + color.rgba.g + ', ' + color.rgba.b + ', ' + color.rgba.a + ')';\n      }\n      //set the value but with a custom debounce that will save the value after 5 seconds\n      clearTimeout(this.debounce);\n      this.debounce = setTimeout(function () {\n        _this[prop] = val;\n      }, 500);\n    },\n    updateSettings: function updateSettings(setName, setVal) {\n      var _this2 = this;\n      var finalValue = setVal;\n      //transform boolean to string\n      if (typeof setVal === 'boolean') {\n        finalValue = setVal ? 'true' : 'false';\n      }\n      this.notification = 'Your settings are being saved...';\n      var requestData = {\n        headers: {\n          'Content-Type': 'application/json',\n          'X-WP-Nonce': uianim.nonce\n        },\n        method: 'POST',\n        body: JSON.stringify({\n          name: setName,\n          value: finalValue\n        })\n      };\n      fetch(uianim.rest, requestData).then(function (res) {\n        return res.json();\n      }).then(function (res) {\n        _this2.notification = '';\n        _this2.notification = res.message;\n        if (!res.success) {\n          _this2.isError = true;\n        }\n        setTimeout(function () {\n          _this2.notification = '';\n          _this2.isError = false;\n        }, 5000);\n      })[\"catch\"](function (err) {\n        _this2.notification = '';\n        _this2.notification = 'Something went wrong!';\n        _this2.isError = true;\n        setTimeout(function () {\n          _this2.notification = '';\n        }, 5000);\n      });\n    },\n    connectToUiCoreAnimate: function connectToUiCoreAnimate() {\n      window.location.href = 'https://my.uicore.co/connect/?ui_connect=true&ui_connect_url=' + uianim.root + '&ui_free_prod=Animate';\n    }\n  }\n});\n\n//# sourceURL=webpack://dummy/./assets/src/settings/App.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/components/select.vue?vue&type=script&lang=js":
/*!****************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/components/select.vue?vue&type=script&lang=js ***!
  \****************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  props: {\n    options: {\n      type: Array,\n      required: true\n    },\n    mode: {\n      type: String,\n      \"default\": 'pair'\n    },\n    value: {\n      type: Object,\n      // Adjust the type according to your needs\n      \"default\": null\n    }\n  },\n  data: function data() {\n    return {\n      selectedValue: this.mode == 'value' ? this.value : this.value.value\n    };\n  },\n  watch: {\n    selectedValue: function selectedValue(e) {\n      console.log(e);\n      var selectedOption = this.options.find(function (option) {\n        return option.value === e;\n      });\n      if (this.mode == 'value') {\n        this.$emit('input', e);\n      } else {\n        this.$emit('input', selectedOption);\n      }\n    }\n  }\n});\n\n//# sourceURL=webpack://dummy/./assets/src/settings/components/select.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/App.vue?vue&type=template&id=7908b764":
/*!*************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/App.vue?vue&type=template&id=7908b764 ***!
  \*************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render),\n/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)\n/* harmony export */ });\nvar render = function render() {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c('div', {\n    attrs: {\n      \"id\": \"uicore-animate\"\n    }\n  }, [_c('div', {\n    directives: [{\n      name: \"show\",\n      rawName: \"v-show\",\n      value: _vm.notification,\n      expression: \"notification\"\n    }],\n    staticClass: \"uianim-notification\",\n    \"class\": [{\n      'uianim-error': _vm.isError\n    }]\n  }, [_vm._v(\"\\n      \" + _vm._s(_vm.notification) + \"\\n    \")]), _vm._m(0), _vm.connect === 'none' ? _c('div', [_c('h5', [_vm._v(\"Connect to UiCore Animate\")]), _c('p', [_vm._v(\"Connect to UiCore Animate to get access to all the features.\")]), _c('div', {\n    staticClass: \"uianim-btn\",\n    on: {\n      \"click\": _vm.connectToUiCoreAnimate\n    }\n  }, [_vm._v(\"Connect\")])]) : _c('div', {\n    staticClass: \"uianim-settings\"\n  }, [_c('label', {\n    attrs: {\n      \"for\": \"uianim_split_type\"\n    }\n  }, [_vm._v(\"Animation style\")]), _c('select-component', {\n    attrs: {\n      \"id\": \"uianim_split_type\",\n      \"options\": _vm.styleList\n    },\n    model: {\n      value: _vm.style,\n      callback: function callback($$v) {\n        _vm.style = $$v;\n      },\n      expression: \"style\"\n    }\n  }), _c('label', {\n    staticClass: \"uianim-checkbox\"\n  }, [_vm._v(\"\\n        Disable UiCore Animate from Editor\\n        \"), _c('input', {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.disable,\n      expression: \"disable\"\n    }],\n    attrs: {\n      \"type\": \"checkbox\",\n      \"id\": \"disable\"\n    },\n    domProps: {\n      \"checked\": Array.isArray(_vm.disable) ? _vm._i(_vm.disable, null) > -1 : _vm.disable\n    },\n    on: {\n      \"change\": function change($event) {\n        var $$a = _vm.disable,\n          $$el = $event.target,\n          $$c = $$el.checked ? true : false;\n        if (Array.isArray($$a)) {\n          var $$v = null,\n            $$i = _vm._i($$a, $$v);\n          if ($$el.checked) {\n            $$i < 0 && (_vm.disable = $$a.concat([$$v]));\n          } else {\n            $$i > -1 && (_vm.disable = $$a.slice(0, $$i).concat($$a.slice($$i + 1)));\n          }\n        } else {\n          _vm.disable = $$c;\n        }\n      }\n    }\n  }), _c('div', {\n    staticClass: \"uianim-check\"\n  })]), _c('label', {\n    staticClass: \"uianim-checkbox\"\n  }, [_vm._v(\"\\n        Enable Smooth Scroll\\n        \"), _c('input', {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.scroll,\n      expression: \"scroll\"\n    }],\n    attrs: {\n      \"type\": \"checkbox\",\n      \"id\": \"disable\"\n    },\n    domProps: {\n      \"checked\": Array.isArray(_vm.scroll) ? _vm._i(_vm.scroll, null) > -1 : _vm.scroll\n    },\n    on: {\n      \"change\": function change($event) {\n        var $$a = _vm.scroll,\n          $$el = $event.target,\n          $$c = $$el.checked ? true : false;\n        if (Array.isArray($$a)) {\n          var $$v = null,\n            $$i = _vm._i($$a, $$v);\n          if ($$el.checked) {\n            $$i < 0 && (_vm.scroll = $$a.concat([$$v]));\n          } else {\n            $$i > -1 && (_vm.scroll = $$a.slice(0, $$i).concat($$a.slice($$i + 1)));\n          }\n        } else {\n          _vm.scroll = $$c;\n        }\n      }\n    }\n  }), _c('div', {\n    staticClass: \"uianim-check\"\n  })]), _c('label', {\n    attrs: {\n      \"for\": \"uianim_pt\"\n    }\n  }, [_vm._v(\"Page Transition\")]), _c('select-component', {\n    attrs: {\n      \"id\": \"uianim_pt\",\n      \"options\": _vm.pageTransitions,\n      \"mode\": \"value\"\n    },\n    model: {\n      value: _vm.animations_page,\n      callback: function callback($$v) {\n        _vm.animations_page = $$v;\n      },\n      expression: \"animations_page\"\n    }\n  }), _vm.animations_page != 'none' && _vm.animations_page != 'fade' && _vm.animations_page != 'fade in' ? _c('div', [_c('label', {\n    attrs: {\n      \"for\": \"uianim_ptd\"\n    }\n  }, [_vm._v(\"Page Transition Duration\")]), _c('select-component', {\n    attrs: {\n      \"id\": \"uianim_ptd\",\n      \"options\": _vm.durations,\n      \"mode\": \"value\"\n    },\n    model: {\n      value: _vm.animations_page_duration,\n      callback: function callback($$v) {\n        _vm.animations_page_duration = $$v;\n      },\n      expression: \"animations_page_duration\"\n    }\n  }), _c('label', [_vm._v(\"Page Transition Color\")]), _c('color-picker', {\n    on: {\n      \"input\": function input($event) {\n        return _vm.updateFromPicker($event, 'animations_page_color_picker');\n      }\n    },\n    model: {\n      value: _vm.animations_page_color_picker,\n      callback: function callback($$v) {\n        _vm.animations_page_color_picker = $$v;\n      },\n      expression: \"animations_page_color_picker\"\n    }\n  }), _c('label', {\n    attrs: {\n      \"for\": \"uiadmin_preloader\"\n    }\n  }, [_vm._v(\"Preloader\")]), _c('select-component', {\n    attrs: {\n      \"id\": \"uiadmin_preloader\",\n      \"options\": _vm.preloaderList,\n      \"mode\": \"value\"\n    },\n    model: {\n      value: _vm.animations_preloader,\n      callback: function callback($$v) {\n        _vm.animations_preloader = $$v;\n      },\n      expression: \"animations_preloader\"\n    }\n  }), _vm.animations_preloader != 'none' ? _c('div', [_c('label', [_vm._v(\"Preloader Color\")]), _c('color-picker', {\n    on: {\n      \"input\": function input($event) {\n        return _vm.updateFromPicker($event, 'animations_preloader_color');\n      }\n    },\n    model: {\n      value: _vm.animations_preloader_color,\n      callback: function callback($$v) {\n        _vm.animations_preloader_color = $$v;\n      },\n      expression: \"animations_preloader_color\"\n    }\n  }), _vm.animations_preloader && _vm.animations_preloader.indexOf('text') !== -1 ? _c('div', [_c('label', {\n    attrs: {\n      \"for\": \"uiadmin_preloader_text\"\n    }\n  }, [_vm._v(\"Preloader Text\")]), _c('input', {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.animations_preloader_text,\n      expression: \"animations_preloader_text\"\n    }],\n    attrs: {\n      \"type\": \"text\",\n      \"id\": \"uiadmin_preloader_text\"\n    },\n    domProps: {\n      \"value\": _vm.animations_preloader_text\n    },\n    on: {\n      \"input\": function input($event) {\n        if ($event.target.composing) return;\n        _vm.animations_preloader_text = $event.target.value;\n      }\n    }\n  }), _c('label', [_vm._v(\"Preloader Text Color\")]), _c('color-picker', {\n    on: {\n      \"input\": function input($event) {\n        return _vm.updateFromPicker($event, 'animations_preloader_text_color');\n      }\n    },\n    model: {\n      value: _vm.animations_preloader_text_color,\n      callback: function callback($$v) {\n        _vm.animations_preloader_text_color = $$v;\n      },\n      expression: \"animations_preloader_text_color\"\n    }\n  })], 1) : _vm._e(), _vm.animations_preloader == 'intro-words' ? _c('div', [_c('label', {\n    attrs: {\n      \"for\": \"uiadmin_preloader_words\"\n    }\n  }, [_vm._v(\"Preloader Words\")]), _c('input', {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.animations_preloader_words,\n      expression: \"animations_preloader_words\"\n    }],\n    attrs: {\n      \"type\": \"text\",\n      \"id\": \"uiadmin_preloader_words\"\n    },\n    domProps: {\n      \"value\": _vm.animations_preloader_words\n    },\n    on: {\n      \"input\": function input($event) {\n        if ($event.target.composing) return;\n        _vm.animations_preloader_words = $event.target.value;\n      }\n    }\n  })]) : _vm._e()], 1) : _vm._e()], 1) : _vm._e()], 1), _c('h3', [_vm._v(\"Faq\")]), _c('p', {\n    staticStyle: {\n      \"margin-bottom\": \"20px\"\n    }\n  }, [_vm._v(\"\\n      If you can’t find what you’re looking for, please don’t hesitate to contact our customer support team. We’re always here to help.\\n    \")]), _vm._m(1), _c('p', [_vm._v(\"Not at all. The plugin only replaces the CSS animations from Elementor so it has zero impact on performance.\")]), _vm._m(2), _c('p', [_vm._v(\"Yes, you can use it with any theme that supports Elementor Page Builder.\")]), _vm._m(3), _c('p', [_vm._v(\"No. You cannot use UiCore Animate without Elementor.\")]), _vm._m(4), _c('p', [_vm._v(\"Yes, absolutely.\")]), _vm._m(5), _c('p', {\n    staticStyle: {\n      \"margin-bottom\": \"0\"\n    }\n  }, [_vm._v(\"No. This is a lightweight plugin and the chances of affecting the site are minimal.\")])]);\n};\nvar staticRenderFns = [function () {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c('div', {\n    staticClass: \"uianim-header-title\"\n  }, [_c('h2', [_vm._v(\"UiCore Animate\")]), _c('p', [_vm._v(\"UiCore Animate is a plugin that allows you to add animation to your website.\")])]);\n}, function () {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c('p', [_c('b', [_vm._v(\"Does UiCore Animate affect my website performance?\")])]);\n}, function () {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c('p', [_c('b', [_vm._v(\"Can I use UiCore Animate with any theme?\")])]);\n}, function () {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c('p', [_c('b', [_vm._v(\"Is this a standalone plugin?\")])]);\n}, function () {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c('p', [_c('b', [_vm._v(\"Does it work with Elementor PRO?\")])]);\n}, function () {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c('p', [_c('b', [_vm._v(\"Will UiCore Animate break my site after an update?\")])]);\n}];\nrender._withStripped = true;\n\n\n//# sourceURL=webpack://dummy/./assets/src/settings/App.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet%5B1%5D.rules%5B2%5D!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/components/select.vue?vue&type=template&id=98fcf4bc":
/*!***************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/components/select.vue?vue&type=template&id=98fcf4bc ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render),\n/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)\n/* harmony export */ });\nvar render = function render() {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c('select', {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.selectedValue,\n      expression: \"selectedValue\"\n    }],\n    on: {\n      \"change\": function change($event) {\n        var $$selectedVal = Array.prototype.filter.call($event.target.options, function (o) {\n          return o.selected;\n        }).map(function (o) {\n          var val = \"_value\" in o ? o._value : o.value;\n          return val;\n        });\n        _vm.selectedValue = $event.target.multiple ? $$selectedVal : $$selectedVal[0];\n      }\n    }\n  }, _vm._l(_vm.options, function (option, index) {\n    return _c('option', {\n      key: index,\n      domProps: {\n        \"value\": option.value\n      }\n    }, [_vm._v(\"\\n    \" + _vm._s(option.name) + \"\\n  \")]);\n  }), 0);\n};\nvar staticRenderFns = [];\nrender._withStripped = true;\n\n\n//# sourceURL=webpack://dummy/./assets/src/settings/components/select.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet%5B1%5D.rules%5B2%5D!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js!./node_modules/css-loader/index.js!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/sass-loader/dist/cjs.js??clonedRuleSet-2.use[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/App.vue?vue&type=style&index=0&id=7908b764&lang=scss":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js!./node_modules/css-loader/index.js!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/sass-loader/dist/cjs.js??clonedRuleSet-2.use[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/App.vue?vue&type=style&index=0&id=7908b764&lang=scss ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://dummy/./assets/src/settings/App.vue?./node_modules/mini-css-extract-plugin/dist/loader.js!./node_modules/css-loader/index.js!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/sass-loader/dist/cjs.js??clonedRuleSet-2.use%5B2%5D!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./assets/src/settings/App.vue":
/*!*************************************!*\
  !*** ./assets/src/settings/App.vue ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _App_vue_vue_type_template_id_7908b764__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./App.vue?vue&type=template&id=7908b764 */ \"./assets/src/settings/App.vue?vue&type=template&id=7908b764\");\n/* harmony import */ var _App_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./App.vue?vue&type=script&lang=js */ \"./assets/src/settings/App.vue?vue&type=script&lang=js\");\n/* harmony import */ var _App_vue_vue_type_style_index_0_id_7908b764_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./App.vue?vue&type=style&index=0&id=7908b764&lang=scss */ \"./assets/src/settings/App.vue?vue&type=style&index=0&id=7908b764&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n;\n\n\n/* normalize component */\n\nvar component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(\n  _App_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _App_vue_vue_type_template_id_7908b764__WEBPACK_IMPORTED_MODULE_0__.render,\n  _App_vue_vue_type_template_id_7908b764__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"assets/src/settings/App.vue\"\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);\n\n//# sourceURL=webpack://dummy/./assets/src/settings/App.vue?");

/***/ }),

/***/ "./assets/src/settings/components/select.vue":
/*!***************************************************!*\
  !*** ./assets/src/settings/components/select.vue ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _select_vue_vue_type_template_id_98fcf4bc__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./select.vue?vue&type=template&id=98fcf4bc */ \"./assets/src/settings/components/select.vue?vue&type=template&id=98fcf4bc\");\n/* harmony import */ var _select_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./select.vue?vue&type=script&lang=js */ \"./assets/src/settings/components/select.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n;\nvar component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _select_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _select_vue_vue_type_template_id_98fcf4bc__WEBPACK_IMPORTED_MODULE_0__.render,\n  _select_vue_vue_type_template_id_98fcf4bc__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"assets/src/settings/components/select.vue\"\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);\n\n//# sourceURL=webpack://dummy/./assets/src/settings/components/select.vue?");

/***/ }),

/***/ "./assets/src/settings/App.vue?vue&type=script&lang=js":
/*!*************************************************************!*\
  !*** ./assets/src/settings/App.vue?vue&type=script&lang=js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./App.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/App.vue?vue&type=script&lang=js\");\n /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack://dummy/./assets/src/settings/App.vue?");

/***/ }),

/***/ "./assets/src/settings/components/select.vue?vue&type=script&lang=js":
/*!***************************************************************************!*\
  !*** ./assets/src/settings/components/select.vue?vue&type=script&lang=js ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_index_js_vue_loader_options_select_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./select.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/components/select.vue?vue&type=script&lang=js\");\n /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_index_js_vue_loader_options_select_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack://dummy/./assets/src/settings/components/select.vue?");

/***/ }),

/***/ "./assets/src/settings/App.vue?vue&type=template&id=7908b764":
/*!*******************************************************************!*\
  !*** ./assets/src/settings/App.vue?vue&type=template&id=7908b764 ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_template_id_7908b764__WEBPACK_IMPORTED_MODULE_0__.render),\n/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_template_id_7908b764__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_template_id_7908b764__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./App.vue?vue&type=template&id=7908b764 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/App.vue?vue&type=template&id=7908b764\");\n\n\n//# sourceURL=webpack://dummy/./assets/src/settings/App.vue?");

/***/ }),

/***/ "./assets/src/settings/components/select.vue?vue&type=template&id=98fcf4bc":
/*!*********************************************************************************!*\
  !*** ./assets/src/settings/components/select.vue?vue&type=template&id=98fcf4bc ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_select_vue_vue_type_template_id_98fcf4bc__WEBPACK_IMPORTED_MODULE_0__.render),\n/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_select_vue_vue_type_template_id_98fcf4bc__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1_use_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_select_vue_vue_type_template_id_98fcf4bc__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./select.vue?vue&type=template&id=98fcf4bc */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1.use!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/components/select.vue?vue&type=template&id=98fcf4bc\");\n\n\n//# sourceURL=webpack://dummy/./assets/src/settings/components/select.vue?");

/***/ }),

/***/ "./assets/src/settings/App.vue?vue&type=style&index=0&id=7908b764&lang=scss":
/*!**********************************************************************************!*\
  !*** ./assets/src/settings/App.vue?vue&type=style&index=0&id=7908b764&lang=scss ***!
  \**********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_node_modules_css_loader_index_js_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_sass_loader_dist_cjs_js_clonedRuleSet_2_use_2_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_style_index_0_id_7908b764_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/mini-css-extract-plugin/dist/loader.js!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/sass-loader/dist/cjs.js??clonedRuleSet-2.use[2]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./App.vue?vue&type=style&index=0&id=7908b764&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js!./node_modules/css-loader/index.js!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/sass-loader/dist/cjs.js??clonedRuleSet-2.use[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./assets/src/settings/App.vue?vue&type=style&index=0&id=7908b764&lang=scss\");\n\n\n//# sourceURL=webpack://dummy/./assets/src/settings/App.vue?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	(() => {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"settings": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["uianimJsonp"] = self["uianimJsonp"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["vendor"], () => (__webpack_require__("./assets/src/settings/main.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	dummy = __webpack_exports__;
/******/ 	
/******/ })()
;
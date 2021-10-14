"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_components_admin_adminComponent_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/admin/adminComponent.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/admin/adminComponent.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
var importexport = function importexport() {
  return __webpack_require__.e(/*! import() */ "resources_js_components_admin_importexport_vue").then(__webpack_require__.bind(__webpack_require__, /*! ./importexport */ "./resources/js/components/admin/importexport.vue"));
}; // const users = () => import("./users");
// const statusLid = () => import("./statusLid");
// const providers = () => import("./providers");
// const mlids = () => import("../manager/mlids");
// const lids = () => import("../crmanager/lids");


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: ['user'],
  data: function data() {
    return {
      drawer: null,
      selectedItem: 0,
      items: [{
        text: "Импорт экспорт",
        name: "importexport",
        icon: "mdi-progress-upload"
      }, {
        text: "Пользователи",
        name: "users",
        icon: "mdi-account"
      }, {
        text: "Статусы лидов",
        name: "statusLid",
        icon: "mdi-format-list-checks"
      }, {
        text: "Поставщики",
        name: "providers",
        icon: "mdi-contact-phone-outline"
      }, // { text: "Рабочие места", name: "workPlaces", icon: "mdi-sitemap" },
      {
        text: "Распределение",
        name: "lids",
        icon: "mdi-account-arrow-left"
      }, {
        text: "Управление",
        name: "mlids",
        icon: "mdi-phone-log-outline"
      }],
      adminMenu: "importexport"
    };
  },
  computed: {
    adminComponent: function adminComponent() {
      if (this.adminMenu == "importexport") return importexport; //   if (this.adminMenu == "users") return users;
      //   if (this.adminMenu == "statusLid") return statusLid;
      //   if (this.adminMenu == "providers") return providers;
      //   if (this.adminMenu == "mlids") return mlids;
      //   if (this.adminMenu == "lids") return lids;
    }
  },
  mounted: function mounted() {},
  methods: {}
});

/***/ }),

/***/ "./resources/js/components/admin/adminComponent.vue":
/*!**********************************************************!*\
  !*** ./resources/js/components/admin/adminComponent.vue ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _adminComponent_vue_vue_type_template_id_9a9fc20e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./adminComponent.vue?vue&type=template&id=9a9fc20e& */ "./resources/js/components/admin/adminComponent.vue?vue&type=template&id=9a9fc20e&");
/* harmony import */ var _adminComponent_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./adminComponent.vue?vue&type=script&lang=js& */ "./resources/js/components/admin/adminComponent.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _adminComponent_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _adminComponent_vue_vue_type_template_id_9a9fc20e___WEBPACK_IMPORTED_MODULE_0__.render,
  _adminComponent_vue_vue_type_template_id_9a9fc20e___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/admin/adminComponent.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/components/admin/adminComponent.vue?vue&type=script&lang=js&":
/*!***********************************************************************************!*\
  !*** ./resources/js/components/admin/adminComponent.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_adminComponent_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./adminComponent.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/admin/adminComponent.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_adminComponent_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/admin/adminComponent.vue?vue&type=template&id=9a9fc20e&":
/*!*****************************************************************************************!*\
  !*** ./resources/js/components/admin/adminComponent.vue?vue&type=template&id=9a9fc20e& ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_adminComponent_vue_vue_type_template_id_9a9fc20e___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_adminComponent_vue_vue_type_template_id_9a9fc20e___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_adminComponent_vue_vue_type_template_id_9a9fc20e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./adminComponent.vue?vue&type=template&id=9a9fc20e& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/admin/adminComponent.vue?vue&type=template&id=9a9fc20e&");


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/admin/adminComponent.vue?vue&type=template&id=9a9fc20e&":
/*!********************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/admin/adminComponent.vue?vue&type=template&id=9a9fc20e& ***!
  \********************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "v-app",
    { attrs: { id: "inspire" } },
    [
      _c(
        "v-app-bar",
        { attrs: { app: "" } },
        [
          _c("v-app-bar-nav-icon", {
            on: {
              click: function($event) {
                _vm.drawer = !_vm.drawer
              }
            }
          }),
          _vm._v(" "),
          _c("v-toolbar-title", [
            _vm._v("Пользователь в системе: " + _vm._s(_vm.user.fio))
          ]),
          _vm._v(" "),
          _c("v-spacer"),
          _vm._v(" "),
          _c(
            "v-btn",
            {
              on: {
                click: function($event) {
                  return _vm.$emit("login", {})
                }
              }
            },
            [_vm._v("ВЫХОД")]
          )
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "v-navigation-drawer",
        {
          attrs: { fixed: "", temporary: "" },
          on: {
            click: function($event) {
              _vm.drawer = !_vm.drawer
            }
          },
          model: {
            value: _vm.drawer,
            callback: function($$v) {
              _vm.drawer = $$v
            },
            expression: "drawer"
          }
        },
        [
          _c(
            "v-card",
            {
              staticClass: "mx-auto",
              attrs: { "max-width": "300", tile: "" },
              on: {
                click: function($event) {
                  _vm.drawer = !_vm.drawer
                }
              }
            },
            [
              _c(
                "v-list",
                [
                  _c("v-subheader", [_vm._v("MENU")]),
                  _vm._v(" "),
                  _c(
                    "v-list-item-group",
                    {
                      attrs: { color: "primary" },
                      model: {
                        value: _vm.selectedItem,
                        callback: function($$v) {
                          _vm.selectedItem = $$v
                        },
                        expression: "selectedItem"
                      }
                    },
                    _vm._l(_vm.items, function(item, i) {
                      return _c(
                        "v-list-item",
                        {
                          key: i,
                          on: {
                            click: function($event) {
                              _vm.adminMenu = item.name
                            }
                          }
                        },
                        [
                          _c(
                            "v-list-item-icon",
                            [
                              _c("v-icon", {
                                domProps: { textContent: _vm._s(item.icon) }
                              })
                            ],
                            1
                          ),
                          _vm._v(" "),
                          _c(
                            "v-list-item-content",
                            [
                              _c("v-list-item-title", {
                                domProps: { textContent: _vm._s(item.text) }
                              })
                            ],
                            1
                          )
                        ],
                        1
                      )
                    }),
                    1
                  )
                ],
                1
              )
            ],
            1
          )
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "v-main",
        { staticClass: "grey lighten-2" },
        [
          _c(
            "v-container",
            { attrs: { fluid: "" } },
            [
              _c(_vm.adminComponent, {
                tag: "component",
                attrs: { user: _vm.$props.user }
              })
            ],
            1
          )
        ],
        1
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ })

}]);
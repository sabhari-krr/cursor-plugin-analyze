"use strict";
/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(self["webpackChunkadmin_ui"] = self["webpackChunkadmin_ui"] || []).push([["Manage"],{

/***/ "./src/components/Manage/Manage.tsx":
/*!******************************************!*\
  !*** ./src/components/Manage/Manage.tsx ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var react_router_dom__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react-router-dom */ \"./node_modules/react-router/dist/index.js\");\n/* harmony import */ var react_router_dom__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react-router-dom */ \"./node_modules/react-router-dom/dist/index.js\");\n/* harmony import */ var _zustand_localState__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../zustand/localState */ \"./src/zustand/localState.ts\");\n/* harmony import */ var _Program_CustomizeFormEditor_helper__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Program/CustomizeFormEditor/helper */ \"./src/components/Manage/Program/CustomizeFormEditor/helper.ts\");\n\n\n\n\nconst Manage = () => {\n    const location = (0,react_router_dom__WEBPACK_IMPORTED_MODULE_3__.useLocation)();\n    const { localState } = (0,_zustand_localState__WEBPACK_IMPORTED_MODULE_1__.useLocalState)();\n    const labels = localState.labels?.programs;\n    return (react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", { className: \"manage-nav-section\" },\n        react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", { className: \" rwt-my-4 rwt-mx-5\" },\n            react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", { className: 'rwt-flex rwt-justify-between' },\n                react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", { className: 'rwt-flex rwt-justify-between lg:rwt-gap-8 rwt-items-center md:rwt-gap-8 rwt-gap-4' },\n                    react__WEBPACK_IMPORTED_MODULE_0___default().createElement(react_router_dom__WEBPACK_IMPORTED_MODULE_4__.NavLink, { className: 'rwt-flex rwt-gap-2 rwt-items-center focus:!rwt-shadow-none focus:!rwt-text-transparent', to: '/manage/programs' },\n                        react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", { className: `lg:rwt-text-xl md:rwt-text-lg rwt-text-sm rwt-font-semibold ${location.pathname == \"/manage/programs\" ? 'rwt-text-primary' : 'rwt-text-gray-500'}` }, (0,_Program_CustomizeFormEditor_helper__WEBPACK_IMPORTED_MODULE_2__.echo)(labels, 'programs', 'Programs')),\n                        react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"i\", { className: 'rwp rwp-video rwt-text-lg  rwt-text-grayprimary' }))),\n                react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", { className: 'rwt-flex rwt-justify-between lg:rwt-gap-10 md:rwt-gap-10  rwt-gap-5' },\n                    react__WEBPACK_IMPORTED_MODULE_0___default().createElement(react_router_dom__WEBPACK_IMPORTED_MODULE_4__.Link, { type: 'button', to: `/manage/programs/0`, className: 'lg:rwt-w-100% lg:rwt-h-100% md:rwt-w-auto rwt-w-auto md:rwt-h-100% rwt-border rwt-rounded-lg rwt-whitespace-nowrap rwt-bg-primary rwt-flex rwt-justify-center rwt-items-center lg:rwt-gap-2 md:rwt-gap-2 rwt-gap-1 lg:rwt-py-3 md:rwt-px-3 md:rwt-py-2 rwt-px-2 rwt-py-1.5 ' },\n                        react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"i\", { className: 'rwp rwp-add-circle rwt-text-xl  rwt-text-secondary' }),\n                        react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", { className: \"rwt-text-secondary\" }, (0,_Program_CustomizeFormEditor_helper__WEBPACK_IMPORTED_MODULE_2__.echo)(labels, 'create_program', 'Create Program')))))),\n        react__WEBPACK_IMPORTED_MODULE_0___default().createElement(react_router_dom__WEBPACK_IMPORTED_MODULE_3__.Outlet, null)));\n};\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Manage);\n\n\n//# sourceURL=webpack://admin-ui/./src/components/Manage/Manage.tsx?");

/***/ }),

/***/ "./src/components/Manage/index.ts":
/*!****************************************!*\
  !*** ./src/components/Manage/index.ts ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _Manage__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _Manage__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Manage */ \"./src/components/Manage/Manage.tsx\");\n\n\n\n\n//# sourceURL=webpack://admin-ui/./src/components/Manage/index.ts?");

/***/ })

}]);
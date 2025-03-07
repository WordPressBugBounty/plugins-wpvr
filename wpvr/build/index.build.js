/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\nfunction _unsupportedIterableToArray(r, a) { if (r) { if (\"string\" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return \"Object\" === t && r.constructor && (t = r.constructor.name), \"Map\" === t || \"Set\" === t ? Array.from(r) : \"Arguments\" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }\nfunction _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }\nfunction _iterableToArrayLimit(r, l) { var t = null == r ? null : \"undefined\" != typeof Symbol && r[Symbol.iterator] || r[\"@@iterator\"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t[\"return\"] && (u = t[\"return\"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }\nfunction _arrayWithHoles(r) { if (Array.isArray(r)) return r; }\n/**\n * WPVR Gutenberg Block\n * \n * A modern implementation of the WPVR tour block using ES6 and WordPress Block API\n */\n\nvar __ = wp.i18n.__;\nvar registerBlockType = wp.blocks.registerBlockType;\nvar _wp$element = wp.element,\n  useEffect = _wp$element.useEffect,\n  useState = _wp$element.useState;\nvar _wp$blockEditor = wp.blockEditor,\n  InspectorControls = _wp$blockEditor.InspectorControls,\n  useBlockProps = _wp$blockEditor.useBlockProps;\nvar _wp$components = wp.components,\n  TextControl = _wp$components.TextControl,\n  SelectControl = _wp$components.SelectControl,\n  ColorPalette = _wp$components.ColorPalette,\n  PanelBody = _wp$components.PanelBody,\n  RangeControl = _wp$components.RangeControl,\n  UnitControl = _wp$components.__experimentalUnitControl,\n  NumberControl = _wp$components.__experimentalNumberControl;\nvar _wp = wp,\n  apiFetch = _wp.apiFetch;\n\n// Block icon as SVG\nvar blockIcon = /*#__PURE__*/React.createElement(\"svg\", {\n  width: \"20\",\n  height: \"20\",\n  viewBox: \"0 0 20 20\"\n}, /*#__PURE__*/React.createElement(\"path\", {\n  d: \"M16.1,16.6h-2.5c-1,0-1.9-0.6-2.4-1.5L11,14.5c-0.2-0.4-0.5-0.6-0.9-0.6c-0.4,0-0.8,0.2-0.9,0.6l-0.3,0.6 c-0.4,0.9-1.3,1.5-2.4,1.5H3.9c-2.2,0-3.9-1.8-3.9-3.9V7.3c0-2.2,1.8-3.9,3.9-3.9h12.2c2.2,0,3.9,1.8,3.9,3.9v1.5 c0,0.4-0.3,0.8-0.8,0.8c-0.4,0-0.8-0.3-0.8-0.8V7.3c0-1.3-1.1-2.3-2.3-2.3H3.9C2.6,4.9,1.6,6,1.6,7.3v5.4c0,1.3,1.1,2.3,2.3,2.3 h2.6c0.4,0,0.8-0.2,0.9-0.6l0.3-0.6c0.4-0.9,1.3-1.5,2.4-1.5c1,0,1.9,0.6,2.4,1.5l0.3,0.6c0.2,0.4,0.5,0.6,0.9,0.6h2.5 c1.3,0,2.3-1.1,2.3-2.3c0-0.4,0.3-0.8,0.8-0.8c0.4,0,0.8,0.3,0.8,0.8C20,14.9,18.2,16.6,16.1,16.6L16.1,16.6z M16.7,9.4 c0-1.3-1.1-2.3-2.3-2.3C13,7.1,12,8.1,12,9.4s1.1,2.3,2.3,2.3C15.6,11.7,16.7,10.7,16.7,9.4L16.7,9.4z M15.1,9.4 c0,0.4-0.4,0.8-0.8,0.8c-0.4,0-0.8-0.4-0.8-0.8s0.4-0.8,0.8-0.8C14.8,8.6,15.1,9,15.1,9.4L15.1,9.4z M8,9.4C8,8.1,7,7.1,5.7,7.1 S3.3,8.1,3.3,9.4s1.1,2.3,2.3,2.3S8,10.7,8,9.4L8,9.4z M6.4,9.4c0,0.4-0.4,0.8-0.8,0.8c-0.4,0-0.8-0.4-0.8-0.8s0.4-0.8,0.8-0.8 C6.1,8.6,6.4,9,6.4,9.4L6.4,9.4z M6.4,9.4\"\n}));\n\n// Available colors for the border\nvar BORDER_COLORS = [{\n  name: 'red',\n  color: '#f00'\n}, {\n  name: 'white',\n  color: '#fff'\n}, {\n  name: 'blue',\n  color: '#00f'\n}, {\n  name: 'black',\n  color: '#000'\n}, {\n  name: 'gray',\n  color: '#888'\n}];\n\n// Border style options\nvar BORDER_STYLES = [{\n  value: 'none',\n  label: __('None', 'wpvr')\n}, {\n  value: 'solid',\n  label: __('Solid', 'wpvr')\n}, {\n  value: 'dotted',\n  label: __('Dotted', 'wpvr')\n}, {\n  value: 'dashed',\n  label: __('Dashed', 'wpvr')\n}, {\n  value: 'double',\n  label: __('Double', 'wpvr')\n}];\n\n// Width unit options\nvar WIDTH_UNITS = [{\n  value: 'px',\n  label: 'px'\n}, {\n  value: '%',\n  label: '%'\n}, {\n  value: 'vw',\n  label: 'vw'\n}, {\n  value: 'fullwidth',\n  label: __('Fullwidth', 'wpvr')\n}];\n\n// Height unit options\nvar HEIGHT_UNITS = [{\n  value: 'px',\n  label: 'px'\n}, {\n  value: 'vh',\n  label: 'vh'\n}];\n\n// Radius unit options\nvar RADIUS_UNITS = [{\n  value: 'px',\n  label: 'px'\n}, {\n  value: '%',\n  label: '%'\n}];\n\n/**\n * WPVR Block Edit component using functional component and hooks\n */\nfunction WpvrEdit(_ref) {\n  var attributes = _ref.attributes,\n    setAttributes = _ref.setAttributes;\n  var _useState = useState([{\n      value: \"0\",\n      label: __(\"None\", \"wpvr\")\n    }]),\n    _useState2 = _slicedToArray(_useState, 2),\n    tourOptions = _useState2[0],\n    setTourOptions = _useState2[1];\n\n  // Fetch tour data when component mounts\n  useEffect(function () {\n    apiFetch({\n      path: 'wpvr/v1/panodata'\n    }).then(function (data) {\n      if (data && Array.isArray(data)) {\n        setTourOptions(data);\n      }\n    })[\"catch\"](function (error) {\n      console.error('Error fetching WPVR tour data:', error);\n    });\n  }, []);\n\n  // Handle width unit change\n  var handleWidthUnitChange = function handleWidthUnitChange(value) {\n    if (value === 'fullwidth') {\n      setAttributes({\n        width: 'fullwidth',\n        width_unit: ''\n      });\n    } else {\n      setAttributes({\n        width_unit: value\n      });\n    }\n  };\n\n  // Get block props with custom styles\n  var blockProps = useBlockProps({\n    className: 'wpvr-block-preview',\n    style: {\n      width: attributes.width_unit ? \"\".concat(attributes.width).concat(attributes.width_unit) : attributes.width === 'fullwidth' ? '100%' : undefined,\n      height: \"\".concat(attributes.height).concat(attributes.height_unit),\n      borderRadius: attributes.radius ? \"\".concat(attributes.radius).concat(attributes.radius_unit) : undefined,\n      borderStyle: attributes.border_style,\n      borderColor: attributes.border_color,\n      borderWidth: attributes.border_width ? \"\".concat(attributes.border_width, \"px\") : undefined\n    }\n  });\n  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {\n    title: __('Tour Settings', 'wpvr'),\n    initialOpen: true\n  }, /*#__PURE__*/React.createElement(SelectControl, {\n    label: __('Select Tour', 'wpvr'),\n    value: attributes.id,\n    options: tourOptions,\n    onChange: function onChange(value) {\n      return setAttributes({\n        id: value\n      });\n    }\n  }), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-dimension-control\"\n  }, /*#__PURE__*/React.createElement(\"p\", null, __('Tour Width', 'wpvr')), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-dimension-inputs\"\n  }, /*#__PURE__*/React.createElement(TextControl, {\n    value: attributes.width,\n    onChange: function onChange(value) {\n      return setAttributes({\n        width: value\n      });\n    },\n    disabled: attributes.width === 'fullwidth'\n  }), /*#__PURE__*/React.createElement(SelectControl, {\n    value: attributes.width === 'fullwidth' ? 'fullwidth' : attributes.width_unit,\n    options: WIDTH_UNITS,\n    onChange: handleWidthUnitChange\n  }))), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-dimension-control\"\n  }, /*#__PURE__*/React.createElement(\"p\", null, __('Tour Height', 'wpvr')), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-dimension-inputs\"\n  }, /*#__PURE__*/React.createElement(NumberControl, {\n    value: attributes.height,\n    onChange: function onChange(value) {\n      return setAttributes({\n        height: value\n      });\n    }\n  }), /*#__PURE__*/React.createElement(SelectControl, {\n    value: attributes.height_unit,\n    options: HEIGHT_UNITS,\n    onChange: function onChange(value) {\n      return setAttributes({\n        height_unit: value\n      });\n    }\n  }))), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-dimension-control\"\n  }, /*#__PURE__*/React.createElement(\"p\", null, __('Tour Mobile Height', 'wpvr')), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-dimension-inputs\"\n  }, /*#__PURE__*/React.createElement(NumberControl, {\n    value: attributes.mobile_height,\n    onChange: function onChange(value) {\n      return setAttributes({\n        mobile_height: value\n      });\n    }\n  }), /*#__PURE__*/React.createElement(SelectControl, {\n    value: attributes.mobile_height_unit,\n    options: HEIGHT_UNITS,\n    onChange: function onChange(value) {\n      return setAttributes({\n        mobile_height_unit: value\n      });\n    }\n  })))), /*#__PURE__*/React.createElement(PanelBody, {\n    title: __('Appearance', 'wpvr'),\n    initialOpen: false\n  }, /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-dimension-control\"\n  }, /*#__PURE__*/React.createElement(\"p\", null, __('Tour Border Radius', 'wpvr')), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-dimension-inputs\"\n  }, /*#__PURE__*/React.createElement(NumberControl, {\n    value: attributes.radius,\n    onChange: function onChange(value) {\n      return setAttributes({\n        radius: value\n      });\n    }\n  }), /*#__PURE__*/React.createElement(SelectControl, {\n    value: attributes.radius_unit,\n    options: RADIUS_UNITS,\n    onChange: function onChange(value) {\n      return setAttributes({\n        radius_unit: value\n      });\n    }\n  }))), /*#__PURE__*/React.createElement(RangeControl, {\n    label: __('Tour Border Width', 'wpvr'),\n    value: parseInt(attributes.border_width) || 0,\n    onChange: function onChange(value) {\n      return setAttributes({\n        border_width: value.toString()\n      });\n    },\n    min: 0,\n    max: 20\n  }), /*#__PURE__*/React.createElement(SelectControl, {\n    label: __('Tour Border Style', 'wpvr'),\n    value: attributes.border_style,\n    options: BORDER_STYLES,\n    onChange: function onChange(value) {\n      return setAttributes({\n        border_style: value\n      });\n    }\n  }), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-color-control\"\n  }, /*#__PURE__*/React.createElement(\"p\", null, __('Tour Border Color', 'wpvr')), /*#__PURE__*/React.createElement(ColorPalette, {\n    colors: BORDER_COLORS,\n    value: attributes.border_color,\n    onChange: function onChange(value) {\n      return setAttributes({\n        border_color: value\n      });\n    },\n    disableCustomColors: false\n  })))), /*#__PURE__*/React.createElement(\"div\", blockProps, /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-block-content\"\n  }, attributes.id !== \"0\" ? /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-tour-preview\"\n  }, /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-tour-placeholder\"\n  }, __('WPVR Tour Preview', 'wpvr'), /*#__PURE__*/React.createElement(\"p\", {\n    className: \"wpvr-tour-details\"\n  }, \"ID: \", attributes.id, /*#__PURE__*/React.createElement(\"br\", null), __('Width', 'wpvr'), \": \", attributes.width, attributes.width_unit, /*#__PURE__*/React.createElement(\"br\", null), __('Height', 'wpvr'), \": \", attributes.height, attributes.height_unit, /*#__PURE__*/React.createElement(\"br\", null), __('Mobile Height', 'wpvr'), \": \", attributes.mobile_height, attributes.mobile_height_unit, /*#__PURE__*/React.createElement(\"br\", null), attributes.radius && \"\".concat(__('Radius', 'wpvr'), \": \").concat(attributes.radius).concat(attributes.radius_unit)))) : /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wpvr-no-tour-selected\"\n  }, __('Please select a tour from the block settings panel.', 'wpvr')))));\n}\n\n// Register the block\nregisterBlockType('wpvr/wpvr-block', {\n  title: __('WPVR Tour', 'wpvr'),\n  description: __('Add a virtual reality tour to your content.', 'wpvr'),\n  icon: blockIcon,\n  category: 'media',\n  keywords: [__('vr', 'wpvr'), __('tour', 'wpvr'), __('panorama', 'wpvr'), __('virtual reality', 'wpvr')],\n  attributes: {\n    id: {\n      type: 'string',\n      \"default\": '0'\n    },\n    width: {\n      type: 'string',\n      \"default\": '600'\n    },\n    width_unit: {\n      type: 'string',\n      \"default\": 'px'\n    },\n    height: {\n      type: 'string',\n      \"default\": '400'\n    },\n    height_unit: {\n      type: 'string',\n      \"default\": 'px'\n    },\n    mobile_height: {\n      type: 'string',\n      \"default\": '300'\n    },\n    mobile_height_unit: {\n      type: 'string',\n      \"default\": 'px'\n    },\n    radius: {\n      type: 'string',\n      \"default\": '0'\n    },\n    radius_unit: {\n      type: 'string',\n      \"default\": 'px'\n    },\n    border_width: {\n      type: 'string',\n      \"default\": '0'\n    },\n    border_style: {\n      type: 'string',\n      \"default\": 'none'\n    },\n    border_color: {\n      type: 'string',\n      \"default\": ''\n    }\n  },\n  edit: WpvrEdit,\n  save: function save() {\n    // Return null to use PHP render callback\n    return null;\n  }\n});\n\n//# sourceURL=webpack:///./src/index.js?");

/***/ })

/******/ });
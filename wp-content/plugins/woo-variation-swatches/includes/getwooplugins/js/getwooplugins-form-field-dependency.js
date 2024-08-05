/**
 * jQuery Form Field Dependency ( dependsOn ) JavaScript Library
 * Version: 2.0.0
 * Author: Emran Ahmed (emran.bd.08@gmail.com)
 * Website: https://github.com/EmranAhmed/Form-Field-Dependency
 * Docs: https://github.com/EmranAhmed/Form-Field-Dependency
 * Repo: https://github.com/EmranAhmed/Form-Field-Dependency
 * Issues: https://github.com/EmranAhmed/Form-Field-Dependency/issues
 * Copyright OpenJS Foundation and other contributors
 * Released under the MIT license
 */

(function (window) {

    'use strict'

    const Plugin = (($) => {

        return class {

            DEFAULTS = {
                attribute : 'data-gwp_dependency',
            }

            constructor(element, options, name) {
                this.name     = name
                this.$element = $(element)
                this.settings = $.extend(true, {}, this.DEFAULTS, options)
                this.init();
                this.action();
            }

            init() {
                this.$element.addClass('has-dependency-data');

                let attribute = this.settings.attribute.trim()

                let conditionString = this.$element.attr(attribute).replace(/'/g, '"')

                this.conditions = JSON.parse(conditionString);

                let success = this.check();
                this.showHide(success);
            }

            showHide(success) {
                if (success) {
                    this.$element.removeClass('dependency-show').addClass('dependency-show')
                    return true;
                }
                else {
                    this.$element.removeClass('dependency-show')
                    return false;
                }
            }

            check() {
                return this.conditions.every((conditionObj) => {

                    let selectors = Object.keys(conditionObj);

                    return selectors.every((selector) => {

                        let condition = conditionObj[selector];

                        return this.decision(selector, condition)
                    })
                })
            }

            action() {
                this.conditions.forEach((rules) => {

                    for (const [selector, rule] of Object.entries(rules)) {
                        // @TODO: Some SelectBox like select2 doesn't trigger input event
                        $(document.body).on('input.dependency change.dependency', selector, (event) => {
                            let success = this.check();
                            this.showHide(success);
                        })
                    }
                })
            }

            getValue(selector) {

                let values = []

                if (selector && $(selector).length > 0) {

                    let inputType = $(selector).prop('type').toLowerCase()

                    let currentSelector = selector;

                    if (['checkbox', 'radio'].includes(inputType)) {
                        currentSelector = `${selector}:checked`
                    }

                    if ('select-multiple' === inputType) {
                        currentSelector = `${selector} option:selected`
                    }

                    $(currentSelector).each((index, element) => {
                        let value = $(element).val().trim();
                        values.push(value)
                    })
                }

                return values.filter(value => value !== '');
            }

            decision(selector, condition) {

                let type         = condition['type'];
                let currentValue = this.getValue(selector)

                let checkValue = (typeof condition['value'] === 'undefined') ? false : condition['value'];

                let minValue = (typeof condition['min'] === 'undefined') ? false : parseInt(condition['min']);
                let maxValue = (typeof condition['max'] === 'undefined') ? false : parseInt(condition['max']);

                let allowEmpty = (typeof condition['empty'] === 'undefined') ? false : condition['empty'];
                let isEmpty    = (!allowEmpty && currentValue.length < 1)

                let likeSelector      = (typeof condition['like'] === 'undefined') ? false : condition['like'];
                let likeSelectorValue = this.getValue(likeSelector)

                let regExpPattern  = (typeof condition['pattern'] === 'undefined') ? false : condition['pattern'];
                let regExpModifier = (typeof condition['modifier'] === 'undefined') ? 'gi' : condition['modifier'];
                let sign           = (typeof condition['sign'] === 'undefined') ? false : condition['sign'];
                let strict         = (typeof condition['strict'] === 'undefined') ? false : condition['strict'];

                let emptyTypes    = ['empty', 'blank']
                let notEmptyTypes = ['!empty', 'notEmpty', 'not-empty', 'notempty']

                let equalTypes    = ['equal', '=', '==', '===']
                let notEqualTypes = ['!equal', '!=', '!==', '!===', 'notEqual', 'not-equal', 'notequal']

                let regularExpressionTypes = ['regexp', 'exp', 'expression', 'match']

                // if empty return true
                if (emptyTypes.includes(type)) {
                    return (currentValue.length < 1);
                }

                // if not empty return true
                if (notEmptyTypes.includes(type)) {
                    return (currentValue.length > 0)
                }

                // if equal return true
                if (equalTypes.includes(type)) {

                    if (isEmpty) {
                        return false
                    }

                    // Match two selector value/s
                    if (likeSelector) {

                        if (strict) {
                            return likeSelectorValue.every((value) => {
                                return currentValue.includes(value)
                            })
                        }
                        else {
                            return likeSelectorValue.some((value) => {
                                return currentValue.includes(value)
                            })
                        }
                    }

                    // Match pre-defined value/s
                    if (strict) {

                        if (checkValue && Array.isArray(checkValue)) {
                            return checkValue.every((value) => {
                                return currentValue.includes(value);
                            })
                        }

                        if (checkValue && !Array.isArray(checkValue)) {
                            return currentValue.includes(checkValue);
                        }
                    }

                    else {

                        if (checkValue && Array.isArray(checkValue)) {
                            return checkValue.some((value) => {
                                return currentValue.includes(value);
                            })
                        }

                        if (checkValue && !Array.isArray(checkValue)) {

                            /*return currentValue.find(value => {
                                return value.toLowerCase() === checkValue.toLowerCase();
                            });*/

                            return currentValue.includes(checkValue);
                        }
                    }
                }

                // if not equal return true
                if (notEqualTypes.includes(type)) {

                    if (isEmpty) {
                        return false
                    }

                    // Match two selector value/s
                    if (likeSelector) {

                        if (strict) {
                            return likeSelectorValue.every((value) => {
                                return !currentValue.includes(value)
                            })
                        }
                        else {
                            return likeSelectorValue.some((value) => {
                                return !currentValue.includes(value)
                            })
                        }
                    }

                    // Match pre-defined value/s
                    if (strict) {

                        if (checkValue && Array.isArray(checkValue)) {
                            return checkValue.every((value) => {
                                return !currentValue.includes(value);
                            })
                        }

                        if (checkValue && !Array.isArray(checkValue)) {
                            return !currentValue.includes(checkValue);
                        }

                    }
                    else {

                        if (checkValue && Array.isArray(checkValue)) {
                            return checkValue.some((value) => {
                                return !currentValue.includes(value);
                            })
                        }

                        if (checkValue && !Array.isArray(checkValue)) {
                            return !currentValue.includes(checkValue);
                        }
                    }
                }

                // if regexp match
                if ((regularExpressionTypes.includes(type)) && regExpPattern) {

                    if (isEmpty) {
                        return false
                    }

                    let exp = new RegExp(regExpPattern, regExpModifier)
                    return currentValue.every((value) => {
                        return exp.test(value)
                    })
                }

                // if length
                if ('length' === type) {

                    if (isEmpty) {
                        return false
                    }

                    if (checkValue && Array.isArray(checkValue)) {
                        minValue = parseInt(checkValue[0]);
                        maxValue = (typeof checkValue[1] === 'undefined') ? false : parseInt(checkValue[1]);
                    }

                    if (checkValue && !Array.isArray(checkValue)) {
                        minValue = parseInt(checkValue);
                        maxValue = false;
                    }

                    return currentValue.every((value) => {
                        if (!maxValue) {
                            return value.length >= minValue;
                        }

                        if (!minValue) {
                            return value.length <= maxValue;
                        }

                        return value.length >= minValue && value.length <= maxValue;

                    })
                }

                // if range
                if ('range' === type) {

                    if (isEmpty) {
                        return false
                    }

                    if (checkValue && Array.isArray(checkValue)) {
                        minValue = parseInt(checkValue[0]);
                        maxValue = (typeof checkValue[1] === 'undefined') ? false : parseInt(checkValue[1]);
                    }

                    return currentValue.every((value) => {
                        if (!maxValue) {
                            return parseInt(value) > minValue;
                        }

                        if (!minValue) {
                            return parseInt(value) < maxValue;
                        }

                        return parseInt(value) > minValue && parseInt(value) < maxValue;

                    })
                }

                // if compare
                if ('compare' === type && sign && checkValue) {

                    if (isEmpty) {
                        return false
                    }

                    checkValue = parseInt(checkValue)

                    switch (sign) {
                        case '<':
                            return currentValue.every((value) => {
                                return parseInt(value) < checkValue;
                            })
                            break;

                        case '<=':
                            return currentValue.every((value) => {
                                return parseInt(value) <= checkValue;
                            })
                            break;

                        case '>':
                            return currentValue.every((value) => {
                                return parseInt(value) > checkValue;
                            })
                            break;

                        case '>=':
                            return currentValue.every((value) => {
                                return parseInt(value) >= checkValue;
                            })
                            break;

                        case '=':
                        case '==':
                            return currentValue.every((value) => {
                                return parseInt(value) === checkValue;
                            })
                            break;
                    }

                }

                // $( document.body ).triggerHandler( 'depends-on',[selector, condition, this])

            }

            destroy() {
                this.$element.removeData(this.name)
            }
        }

    })(jQuery)

    const jQueryPlugin = (($) => {

        return (PluginName, ClassName) => {

            $.fn[PluginName] = function (options, ...args) {
                return this.each((index, element) => {

                    let $element = $(element)
                    let data     = $element.data(PluginName)

                    if (!data) {
                        data = new ClassName($element, $.extend({}, options), PluginName)
                        $element.data(PluginName, data)
                    }

                    if (typeof options === 'string') {

                        if (typeof data[options] === 'object') {
                            return data[options]
                        }

                        if (typeof data[options] === 'function') {
                            return data[options](...args)
                        }
                    }

                    return this
                })
            }

            // Constructor
            $.fn[PluginName].Constructor = ClassName

            // Short Hand
            $[PluginName] = (options, ...args) => $({})[PluginName](options, ...args)

            // No Conflict
            $.fn[PluginName].noConflict = () => $.fn[PluginName]
        }

    })(jQuery)

    jQueryPlugin('GWPFormFieldDependency', Plugin)

})(window);
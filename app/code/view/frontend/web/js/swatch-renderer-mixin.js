/**
 * @copyright Copyright (c) Spaarne Webdesign, Haarlem, The Netherlands
 * @author Enno Stuurman <enno@spaarnewebdesign.nl>
 */

/**
 * Mixin to inject stock level into PLP and PDP swatches renderer
 *
 * Note for learning and education:
 * Using _Rebuild function from the swatch renderer widget to inject our functionality
 * almost as if we're observing an event here and hook into it.
 * This is better than creating separate custom logic with custom event handlers,
 * because this mixin will work auto-magically on PLP, PDP and widgets too!
 */
define([
    'jquery',
    'underscore',
    'mage/translate'
], ($, _, $t) => {

    const swatchRendererMixin = {
        _Rebuild: function() {
            const $widget = this;
            const controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[data-attribute-id]');
            const selected = controls.filter('[data-option-selected]');
            const controlsArray = Array.from(controls);
            const selectedArray = Array.from(selected);
            const stockLevels = this.options.jsonConfig.stockLevels;
            const productIdAttributeMapping = this.options.jsonConfig.index
            const stockLevelContainer = '[data-js=stock-level]';

            /**
             * Executing when a simple is selected
             */
            if (isSimpleSelected()) {
                insertStockLevelHtml();
            }

            /**
             * Insert product stock level html into to the stockLevel container
             *
             * Ideally we would insert this into html defined in a phtml template (separation of concerns).
             * However inserting with JS automatically will work for PLP, PDP and widgets without having
             * override a core template. Widgets is the culprit here, as we have to override the
             * full grid.phtml to insert stock levels.
             * Wanting to avoid that in this module because it would create a tight dependency and we prefer
             * loose coupling.
             */
            function insertStockLevelHtml() {
                const stockLevel =  getProductStockLevel(getSelectedProductIdByOptions());
                $widget.element.find(stockLevelContainer).remove();
                $widget.element
                    .append(
                        `<div data-js="stock-level" class="stock-level">
                            <div class="stock-level__text">
                                ${$t('Stock')}:&nbsp;${stockLevel}
                            </div>
                            <div class="stock-level__bar" aria-hidden="true">
                                <div class="stock-level-fill stock-level-fill--${stockLevel}"></div>
                            </div>
                        </div>
                    `);
            }

            /**
             * Check if simple product is selected (valid swatch combinations selected, e.g. size and color)
             * @returns {*|boolean}
             */
            function isSimpleSelected() {
                return _.isEqual(controlsArray, selectedArray);
            }

            /**
             * Return stockLevels (low. medium, high) for simple product using jsonConfig.stockLevels object
             * "stockLevels":{"1217":"medium",
             * @param productId
             * @returns {*}
             */
            function getProductStockLevel(productId) {
                for (const property in stockLevels) {
                     if (property === productId) {
                         // returns productId: stockLevel
                         // return `${property}: ${stockLevels[property]}`;
                         return stockLevels[property];
                     }
                }
            }

            /**
             * Return simple product id, comparing selected options with the configurable product jsonConfig.index object
             * which contains simple product id and option combinations, e.g. "index":{"1217":{"143":"167","93":"50"},
             * @returns {string}
             */
            function getSelectedProductIdByOptions() {
                for (const property in productIdAttributeMapping) {
                    if (_.isEqual(productIdAttributeMapping[property], getSelectedProductOptions())) {
                        return property;
                    }
                }
            }

            /**
             * Return attributeId and optionSelected values from selected options
             * as object, i.e. { 93: "50", 143: "167" }
             * @returns {{}}
             */
            function getSelectedProductOptions() {
                return selectedArray
                    .map(element => {
                        const {attributeId, optionSelected} = element.dataset;
                        return {
                            attributeId,
                            optionSelected
                        };
                    })
                    .reduce((acc, current) => {
                        acc[current.attributeId] = current.optionSelected;
                        return acc;
                    }, {});
            }

            return this._super();
        }
    };

    return function (targetWidget)  {
        $.widget('mage.SwatchRenderer', targetWidget, swatchRendererMixin);

        return $.mage.SwatchRenderer;
    }
});

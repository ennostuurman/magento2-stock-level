<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) Spaarne Webdesign, Haarlem, The Netherlands
 * @author Enno Stuurman <enno@spaarnewebdesign.nl>
 */

namespace Spaarne\StockLevel\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterfaceFactory;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class GetStockLevel
{
    private const STOCK_QUANTITY_HIGH = 'catalog/spaarne_stocklevel/stock_level_qty_high';

    private const STOCK_QUANTITY_MODERATE = 'catalog/spaarne_stocklevel/stock_level_qty_moderate';

    private const STOCK_LEVEL_LABEL_HIGH = 'high';

    private const STOCK_LEVEL_LABEL_MODERATE = 'moderate';

    private const STOCK_LEVEL_LABEL_LOW = 'low';


    /**
     * @var GetProductSalableQtyInterface
     */
    private GetProductSalableQtyInterface $productSalableQty;

    /**
     * @var DefaultStockProviderInterfaceFactory
     */
    private DefaultStockProviderInterfaceFactory $defaultStockProviderInterface;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * AddStockLevelsToJsonConfig constructor.
     * @param GetProductSalableQtyInterface $productSalableQty
     * @param DefaultStockProviderInterfaceFactory $defaultStockProviderInterface
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        GetProductSalableQtyInterface $productSalableQty,
        DefaultStockProviderInterfaceFactory $defaultStockProviderInterface,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->productSalableQty = $productSalableQty;
        $this->defaultStockProviderInterface = $defaultStockProviderInterface;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return product stockLevel
     * @param $currentProduct
     * @return array|string
     * @throws InputException
     * @throws LocalizedException
     */
    public function getStockLevel($currentProduct)
    {
        if ($this->isConfigurable($currentProduct)) {
            return \array_map([$this, 'defineStockLevel'], $this->getSalableQty($currentProduct));
        }

        if ($this->isSimple($currentProduct)) {
            return $this->defineStockLevel($this->getSalableQty($currentProduct));
        }
    }

    /**
     * Set stock levels based upon salable quantities
     * @param $stockQty
     * @return string
     */
    private function defineStockLevel($stockQty): string
    {
        $stockLevel = (int)$stockQty;

        $stockLevelMedium = (int)$this->scopeConfig->getValue(self::STOCK_QUANTITY_MODERATE);
        $stockLevelHigh = (int)$this->scopeConfig->getValue(self::STOCK_QUANTITY_HIGH);

        if ($stockLevel > $stockLevelHigh) {
            return self::STOCK_LEVEL_LABEL_HIGH;
        } elseif ($stockLevelMedium < $stockLevel && $stockLevel <= $stockLevelHigh) {
            return self::STOCK_LEVEL_LABEL_MODERATE;
        } else {
            return self::STOCK_LEVEL_LABEL_LOW;
        }
    }

    /**
     * @param Product $currentProduct
     * @return array|float
     * @throws InputException
     * @throws LocalizedException
     */
    private function getSalableQty(Product $currentProduct)
    {
        if ($this->isConfigurable($currentProduct)) {
            /** @var Configurable $productType */
            $productType = $currentProduct->getTypeInstance();
            $products = $productType->getUsedProducts($currentProduct);
            $salableQuantity = [];

            /** @var Product $simple */
            foreach ($products as $product) {
                $salableQuantity[$product->getId()] =
                    $this->productSalableQty->execute(
                        $product->getSku(),
                        $this->defaultStockProviderInterface->create()->getId()
                    );
            }

            return $salableQuantity;
        }

        if ($this->isSimple($currentProduct)) {
            return $this->productSalableQty->execute(
                $currentProduct->getSku(),
                $this->defaultStockProviderInterface->create()->getId()
            );
        }
    }

    /**
     * Checks if product is of type configurable
     * @param Product $product
     * @return bool
     */
    private function isConfigurable(Product $product) :bool
    {
        return $product->getTypeId() === Configurable::TYPE_CODE;
    }

    /**
     * Checks if product is of type simple
     * @param Product $product
     * @return bool
     */
    private function isSimple(Product $product): bool
    {
        return $product->getTypeId() === Type::TYPE_SIMPLE;
    }
}

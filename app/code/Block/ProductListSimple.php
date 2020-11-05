<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) Spaarne Webdesign, Haarlem, The Netherlands
 * @author Enno Stuurman <enno@spaarnewebdesign.nl>
 */

namespace Spaarne\StockLevel\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\ProductList\Item\Block as ItemBlock;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Spaarne\StockLevel\Helper\GetStockLevel;

/**
 * Class ProductStock
 * @package Spaarne\StockLevel\Block
 */
class ProductListSimple extends ItemBlock
{
    /**
     * @var GetStockLevel
     */
    private GetStockLevel $stockLevel;

    /**
     * ProductStock constructor.
     * @param Context $context
     * @param GetStockLevel $stockLevel
     * @param array $data
     */
    public function __construct(
        Context $context,
        GetStockLevel $stockLevel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->stockLevel = $stockLevel;
    }

    /**
     * Get current product
     * @return ProductInterface|Product
     */
    public function getProduct(): Product
    {
        return parent::getProduct();
    }

    /**
     * Checks if product is simple
     * @return bool
     */
    public function isSimpleProduct(): bool
    {
        return $this->getProduct()->getTypeId() === Type::TYPE_SIMPLE;
    }

    /**
     * Get product  stock level (low, high, medium)
     * @return array|string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStockLevelForSimple(): ?string
    {
        return $this->stockLevel->getStockLevel($this->getProduct());
    }
}


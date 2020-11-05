<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) Spaarne Webdesign, Haarlem, The Netherlands
 * @author Enno Stuurman <enno@spaarnewebdesign.nl>
 */

namespace Spaarne\StockLevel\Plugin;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Spaarne\StockLevel\Helper\GetStockLevel;

class AddStockLevelsToJsonConfig
{
    /**
     * @var JsonSerializer
     */
    private JsonSerializer $jsonSerializer;

    /**
     * @var GetStockLevel
     */
    private GetStockLevel $stockLevel;

    /**
     * AddStockLevelsToJsonConfig constructor.
     * @param GetStockLevel $stockLevel
     * @param JsonSerializer $jsonSerializer
     */
    public function __construct(
        GetStockLevel $stockLevel,
        JsonSerializer $jsonSerializer
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->stockLevel = $stockLevel;
    }


    /**
     * Insert stock level into configurable jsonConfig object
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param $result
     * @return bool|false|string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $currentProduct = $subject->getProduct();
        $stockConfig = [
            'stockLevels' => $this->stockLevel->getStockLevel($currentProduct)
        ];

        $jsonConfig = $this->jsonSerializer->unserialize($result);
        $result = $this->jsonSerializer->serialize(array_merge($jsonConfig, $stockConfig));

        return $result;
    }
}

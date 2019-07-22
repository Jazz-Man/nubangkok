<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Config as ConfigEav;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function GuzzleHttp\json_decode;

/**
 * Class ColorsCommand.
 */
class ColorsCommand extends Command
{

    /**
     * @var \Magento\Framework\App\Config
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var string
     */
    private $_color_code;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var array
     */
    private $_color_attributes;
    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var int|null
     */
    private $color_attribute_id;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $setup;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    private $attributeFactory;


    /**
     * ColorsCommand constructor.
     *
     * @param \Magento\Framework\App\State                              $state
     * @param \Magento\Framework\App\Config                             $config
     * @param \Magento\Eav\Model\Config                                 $eavConfig
     * @param \Magento\Eav\Model\AttributeRepository                    $attributeRepository
     * @param \Magento\Eav\Setup\EavSetupFactory                        $eavSetupFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface         $setup
     */
    public function __construct(
        State $state,
        Config $config,
        ConfigEav $eavConfig,
        AttributeRepository $attributeRepository,
        EavSetupFactory $eavSetupFactory,
        AttributeFactory $attributeFactory,
        ModuleDataSetupInterface $setup
    ) {

        $this->scopeConfig               = $config;
        $this->state                     = $state;
        $this->eavConfig                 = $eavConfig;
        $this->attributeRepository       = $attributeRepository;
        $this->attributeFactory          = $attributeFactory;
        $this->eavSetupFactory           = $eavSetupFactory;
        $this->setup                     = $setup;
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('erpapi:colors');


        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode('adminhtml');
        }


        $this->_color_code = $this->scopeConfig->getValue('erp_etoday_settings/color_settings/color_code');

        $this->_color_attributes = $this->eavConfig->getAttribute(Product::ENTITY, 'color')
                                                   ->setStoreId(Store::DEFAULT_STORE_ID)
                                                   ->getSource()
                                                   ->getAllOptions(false);

        $this->color_attribute_id = $this->attributeRepository->get(Product::ENTITY, 'color')->getAttributeId();


        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Setup colors!');

        $ColorsCodeValues = $this->getColorsCodeValues($this->getColorCode(), 'erp_color_value');

        $not_neaded_colors = array_filter($this->_color_attributes, static function ($item) use ($ColorsCodeValues) {

            return ! in_array($item['value'], $ColorsCodeValues);
        });


        if ( ! empty($not_neaded_colors)) {

            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup     = $this->eavSetupFactory->create(['setup' => $this->setup]);
            $entityTypeId = $eavSetup->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);

            $attribute = $this->attributeFactory->create()->loadByCode($entityTypeId, 'color');

            /** @var \Magento\Eav\Model\Entity\Attribute\Option[] $options */
            $options = $attribute->getOptions();

            $not_neaded_colors = $this->getColorsCodeValues($not_neaded_colors, 'value');


            $options = array_filter($options, static function (Option $item) use ($not_neaded_colors) {


                return in_array($item->getValue(), $not_neaded_colors);
            });

            $optionsToRemove = [];

            if (!empty($options)){
                foreach ($options as $option) {

                    if (!empty($option['value'])){
                        $optionsToRemove['delete'][$option['value']] = true;
                        $optionsToRemove['value'][$option['value']] = true;
                    }

                }
            }

            if (!empty($optionsToRemove)){

                $eavSetup->addAttributeOption($optionsToRemove);
            }

        }
    }

    /**
     * @return array
     */
    private function getColorCode()
    {
        $color_code = json_decode($this->_color_code, true);

        $color_code = array_values($color_code);

        return $color_code;
    }

    /**
     * @param array  $data
     *
     * @param string $column
     *
     * @return array
     */
    private function getColorsCodeValues(array $data, string $column)
    {
        $color_code_values = array_column($data, $column);

        return array_filter($color_code_values);
    }


}

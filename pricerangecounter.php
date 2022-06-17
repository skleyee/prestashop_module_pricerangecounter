<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PriceRangeCounter extends Module {

    public function __construct()
    {
        $this->name = 'PriceRangeCounter';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Skley';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => '1.7.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Price Range Counter';
        $this->description = 'View how many products are in the specified price range.';
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook(['displayFooter'])) {
            return false;
        }

        Configuration::updateValue('pricerangecounter_price_from', null);
        Configuration::updateValue('pricerangecounter_price_to', null);

        return  true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('pricerangecounter_price_from');
        Configuration::deleteByName('pricerangecounter_price_to');

        return parent::uninstall();
    }

    public function getContent()
    {
        if (Tools::isSubmit($this->name)) {
            $this->saveData();
        }

        $output = $this->display(__FILE__,"views/templates/admin/configure.tpl");;

        return $output . $this->renderForm();
    }

    public function saveData() {
        $form_values = [
            'pricerangecounter_price_from' => Configuration::get('pricerangecounter_price_from', null),
            'pricerangecounter_price_to' => Configuration::get('pricerangecounter_price_to', null)
        ];

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function renderForm() {
        $helper = new HelperForm();

        $helper->module = $this;
        $helper->submit_action = $this->name;
        $form = [
            [
                'form' => [
                    'legend' => [
                        'title' => 'Enter the price range',
                        'icon' => 'icon-cogs'
                    ],
                    'input' => [
                        [
                            'col'   => 2,
                            'type'  => 'text',
                            'label' => 'Price from',
                            'name'  => 'pricerangecounter_price_from',
                        ],
                        [
                            'col'   => 2,
                            'type'  => 'text',
                            'label' => 'Price to',
                            'name'  => 'pricerangecounter_price_to',
                        ],
                    ],
                    'fields_value' => [
                        'pricerangecounter_price_from' => 0,
                        'pricerangecounter_price_to' => 0
                    ],
                    'submit' => [
                        'title' => 'Save',
                        'class' => 'btn btn-default pull-right'
                    ],
                ],
            ],
        ];
        return $helper->generateForm($form);
    }


    public function hookDisplayFooter()
    {
         $db = \Db::getInstance();

         $price_from = Configuration::get('pricerangecounter_price_from');
         $price_to   = Configuration::get('pricerangecounter_price_to');
         $result     = false;

         if ($price_to != false && $price_from != false) {
             $query = 'SELECT COUNT(*) as COUNT FROM presta.ps_product WHERE price BETWEEN ' . $price_from . ' AND ' . $price_to;
             $result = $db->getRow($query)['COUNT'];
         }

         $this->context->smarty->assign([
             'number_of_occurrences' => $result ? intval($result) : 'you didnt set range in backoffice'
         ]);
        return $this->display(__FILE__,"views/templates/hook/displayFooter.tpl");
    }

}
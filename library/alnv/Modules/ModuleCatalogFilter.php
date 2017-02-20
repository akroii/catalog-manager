<?php

namespace CatalogManager;

class ModuleCatalogFilter extends \Module {


    protected $strTemplate = 'mod_catalog_filter';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $this->name . ' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    protected function compile() {

        $this->Import('CatalogFilter');

        $this->CatalogFilter->arrOptions = $this->arrData;
        $this->CatalogFilter->strTable = $this->catalogTablename;
        $this->CatalogFilter->initialize();
    }
}
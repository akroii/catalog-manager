<?php

namespace CatalogManager;

class CatalogDcExtractor extends CatalogController {


    protected $strTable = '';
    protected $blnCore = false;
    protected $strOrderBy = '';
    protected $arrConvertMap = [

        'config' => [

            'pTable',
            'ctable'
        ],

        'list' => [

            'sorting' => [

                'mode',
                'flag',
                'fields',
                'panelLayout'
            ],

            'label' => [

                'format',
                'fields',
                'showColumns'
            ],

            'operations' => []
        ]
    ];

    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }


    public function initialize( $strTablename ) {

        $this->strTable = $strTablename;
        $this->blnCore = Toolkit::isCoreTable( $strTablename );
    }


    public function convertDataContainerToCatalog() {

        \Controller::loadLanguageFile( $this->strTable );
        \Controller::loadDataContainer( $this->strTable );

        $arrReturn = [];
        $arrDataContainer = $GLOBALS['TL_DCA'][ $this->strTable ];

        if ( !is_array( $arrDataContainer ) ) return [];

        foreach ( $this->arrConvertMap as $strDcConfigType => $varAttributes ) {

            switch ( $strDcConfigType ) {

                case 'config':

                    $arrReturn = $this->convertDcConfigToCatalog( $arrReturn, $arrDataContainer, $varAttributes, $strDcConfigType );

                    break;

                case 'list':

                    $arrReturn = $this->convertDcLabelToCatalog( $arrReturn, $arrDataContainer, $varAttributes['label'], $strDcConfigType );
                    $arrReturn = $this->convertDcSortingToCatalog( $arrReturn, $arrDataContainer, $varAttributes['sorting'], $strDcConfigType );
                    $arrReturn = $this->convertDcOperationsToCatalog( $arrReturn, $arrDataContainer, $varAttributes['operations'], $strDcConfigType );

                    break;
            }
        }

        if ( $this->blnCore ) {

            $arrReturn['navArea'] = '';
            $arrReturn['navPosition'] = '';
            $arrReturn['isBackendModule'] = '';
        }

        return $arrReturn;
    }


    public function convertCatalogToDataContainer() {
        
        $arrReturn = $GLOBALS['TL_DCA'][ $this->strTable ];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $this->strTable ];

        if ( !is_array( $arrCatalog ) ) return [];
        if ( !is_array( $arrReturn ) ) $arrReturn = [];

        foreach ( $this->arrConvertMap as $strDcConfigType => $varAttributes ) {

            switch ( $strDcConfigType ) {

                case 'config':

                    $arrReturn = $this->convertCatalogToDcConfig( $arrReturn, $arrCatalog, $varAttributes, $strDcConfigType );

                    break;

                case 'list':

                    $arrReturn = $this->convertCatalogToDcLabel( $arrReturn, $arrCatalog, $varAttributes['label'], $strDcConfigType );
                    $arrReturn = $this->convertCatalogToDcSorting( $arrReturn, $arrCatalog, $varAttributes['sorting'], $strDcConfigType );
                    $arrReturn = $this->convertCatalogToDcOperations( $arrReturn, $arrCatalog, $varAttributes['operations'], $strDcConfigType );

                    break;


                case 'fields':

                    $arrReturn = $this->convertCatalogFieldsToDcFields( $arrReturn, $arrCatalog, $varAttributes, $strDcConfigType );

                    break;
            }
        }

        return $arrReturn;
    }


    public function extract() {

        $objModule = $this->Database->prepare( 'SELECT * FROM tl_catalog WHERE tablename = ? LIMIT 1' )->execute( $this->strTable );

        if ( $objModule->numRows ) {

            $arrSorting = [

                'mode' => $objModule->mode,
                'flag' => $objModule->flag,
                'fields' => Toolkit::deserialize( $objModule->sortingFields )
            ];

            $this->extractDCASorting( $arrSorting );

            return null;
        }

        $this->loadDataContainer( $this->strTable );

        if ( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['dataContainer'] == 'File' ) {

            return null;
        }

        if ( !empty( $GLOBALS['TL_DCA'][ $this->strTable ]['list'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTable ]['list'] ) ) {

            if ( !empty( $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting'] ) ) {

                $arrSorting = $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting'];

                if ( !Toolkit::isEmpty( $arrSorting['mode'] ) && in_array( $arrSorting['mode'], [ 5, 6 ] ) && empty( $arrSorting['fields'] ) ) {

                    $arrSorting['fields'] = ['sorting'];
                }

                $this->extractDCASorting( $arrSorting );
            }
        }
    }


    public function getOrderByStatement() {

        return $this->strOrderBy;
    }


    public function hasOrderByStatement() {

        return !Toolkit::isEmpty( $this->strOrderBy );
    }


    public function setDcSortingByMode( $strMode, $arrCatalog = [] ) {

        $arrReturn = [

            'mode' => $strMode ?: '0'
        ];

        switch ( $strMode ) {

            case '0':

                return $arrReturn;

                break;

            case '1':

                if ( !Toolkit::isEmpty( $arrCatalog['flag'] ) ) {

                    $arrReturn['flag'] = $arrCatalog['flag'];
                }

                if ( is_array( $arrCatalog['sortingFields'] ) && !empty( $arrCatalog['sortingFields'] ) ) {

                    $arrReturn['fields'] = $arrCatalog['sortingFields'];
                }

                else {

                    $arrReturn['fields'] = [ 'title' ];
                }

                return $arrReturn;

                break;

            case '2':

                $arrReturn['panelLayout'] = Toolkit::createPanelLayout( $arrCatalog['panelLayout'] );

                if ( is_array( $arrCatalog['sortingFields'] ) && !empty( $arrCatalog['sortingFields'] ) ) {

                    $arrReturn['fields'] = $arrCatalog['sortingFields'];
                }

                else {

                    $arrReturn['fields'] = [ 'title' ];
                }

                return $arrReturn;

                break;

            case '3':

                return $arrReturn;

                break;

            case '4':

                if ( is_array( $arrCatalog['sortingFields'] ) && !empty( $arrCatalog['sortingFields'] ) ) {

                    $arrReturn['fields'] = $arrCatalog['sortingFields'];
                }

                else {

                    $arrReturn['fields'] = [ 'title' ];
                }

                if ( !is_array( $arrCatalog['labelFields'] ) || empty( $arrCatalog['labelFields'] ) ) {

                    $arrCatalog['labelFields'] = ['title'];
                }

                if ( is_array( $arrCatalog['headerFields'] ) && !empty( $arrCatalog['headerFields'] ) ) {

                    $arrReturn['headerFields'] = $arrCatalog['headerFields'];
                }

                else {

                    $arrReturn['headerFields'] = [ 'id', 'alias', 'title' ];
                }

                $arrReturn['child_record_callback'] = function () use ( $arrCatalog ) {

                    return $arrCatalog['labelFields'][0];
                };

                break;

            case '5':

                return $arrReturn;

                break;

            case '6':

                if ( is_array( $arrCatalog['sortingFields'] ) && !empty( $arrCatalog['sortingFields'] ) ) {

                    $arrReturn['fields'] = $arrCatalog['sortingFields'];
                }

                else {

                    $arrReturn['fields'] = [ 'title' ];
                }

                break;
        }

        return $arrReturn;
    }


    public function setDcLabelByMode( $strMode, $arrCatalog = [] ) {

        $arrReturn = [];

        if ( $strMode === '0' || $strMode === '1' || $strMode === '2' ) {

            if ( is_array( $arrCatalog['labelFields'] ) && !empty( $arrCatalog['labelFields'] ) ) {

                $arrReturn['fields'] = $arrCatalog['labelFields'];
            }

            else {

                $arrReturn['fields'] = [ 'title' ];;
            }

            $arrReturn['showColumns'] = $arrCatalog['showColumns'] ? true : false;

            if ( !Toolkit::isEmpty( $arrCatalog['format'] ) ) $arrReturn['format'] = $arrCatalog['format'];

            if ( $arrCatalog['useOwnLabelFormat'] && !Toolkit::isEmpty( $arrCatalog['labelFormat'] ) ) {

                $arrReturn['label_callback'] = '';
            }

            if ( $arrCatalog['useOwnGroupFormat'] && !Toolkit::isEmpty( $arrCatalog['groupFormat'] ) ) {

                $arrReturn['group_callback'] = '';
            }

            return $arrReturn;
        }

        if ( $strMode === '3' || $strMode === '4' ) {

            return $arrReturn;
        }

        if ( $strMode === '5' || $strMode === '6' ) {

            if ( is_array( $arrCatalog['labelFields'] ) && !empty( $arrCatalog['labelFields'] ) ) {

                $arrReturn['fields'] = $arrCatalog['labelFields'];
            }

            else {

                $arrReturn['fields'] = [ 'title' ];
            }

            if ( !Toolkit::isEmpty( $arrCatalog['format'] ) ) $arrReturn['format'] = $arrCatalog['format'];

            if ( $arrCatalog['useOwnLabelFormat'] && !Toolkit::isEmpty( $arrCatalog['labelFormat'] ) ) {

                $arrReturn['label_callback'] = '';
            }

            if ( $arrCatalog['useOwnGroupFormat'] && !Toolkit::isEmpty( $arrCatalog['groupFormat'] ) ) {

                $arrReturn['group_callback'] = '';
            }

            return $arrReturn;
        }

        return $arrReturn;
    }


    protected function extractDCASorting( $arrSorting ) {

        $arrTemps = [];
        $arrOrderBy = [];
        $intFlag = Toolkit::isEmpty( $arrSorting['flag'] ) ? 1 : (int) $arrSorting['flag'];
        $arrFields = !empty( $arrSorting['fields'] ) && is_array( $arrSorting['fields'] ) ? $arrSorting['fields'] : [];
        $strOrder = $intFlag % 2 ? 'ASC' : 'DESC';

        foreach ( $arrFields as $strField ) {

            if ( in_array( $strField, $arrTemps ) ) {

                continue;
            }

            else {

                $arrTemps[] = $strField;
            }

            $strUpperCaseField = strtoupper( $strField );

            if ( stripos( $strUpperCaseField, 'ASC' ) || stripos( $strUpperCaseField, 'DESC' ) ) {

                $arrOrderBy[] = $strField;

                continue;
            }

            if ( $this->Database->fieldExists( $strField, $this->strTable ) ) {

                $arrOrderBy[] = $strField . ' ' . $strOrder;
            }
        }

        $this->strOrderBy = implode( ',' , $arrOrderBy );
    }


    protected function convertDcConfigToCatalog( $arrReturn, $arrDataContainer, $varAttributes, $strDcConfigType ) {

        if ( $arrDataContainer[ $strDcConfigType ]['pTable'] ) {

            $arrReturn['pTable'] = $arrDataContainer['config']['pTable'];
        }

        if ( is_array( $arrDataContainer[ $strDcConfigType ]['ctable'] ) && !empty( $arrDataContainer['config']['ctable'] ) ) {

            if ( in_array( 'tl_content', $arrDataContainer[ $strDcConfigType ]['ctable'] ) ) {

                $arrReturn['addContentElements'] = '1';
            }

            $arrReturn['cTables'] = serialize( $arrDataContainer[ $strDcConfigType ]['ctable'] );
        }

        return $arrReturn;
    }


    protected function convertDcSortingToCatalog( $arrReturn, $arrDataContainer, $varAttributes, $strDcConfigType ) {

        if ( is_array( $arrDataContainer[ $strDcConfigType ]['sorting'] ) ) {

            if ( isset( $arrDataContainer[ $strDcConfigType ]['sorting']['mode'] ) ) {

                $arrReturn['mode'] = $arrDataContainer[ $strDcConfigType ]['sorting']['mode'];
            }

            if ( isset( $arrDataContainer[ $strDcConfigType ]['sorting']['flag'] ) ) {

                $arrReturn['flag'] = $arrDataContainer[ $strDcConfigType ]['sorting']['flag'];
            }

            if ( isset( $arrDataContainer[ $strDcConfigType ]['sorting']['panelLayout'] ) && is_string( $arrDataContainer[ $strDcConfigType ]['sorting']['panelLayout'] ) ) {

                $arrPanelLayout = preg_split( '/(,|;)/', $arrDataContainer[ $strDcConfigType ]['sorting']['panelLayout'] );
                $arrReturn['panelLayout'] = serialize( $arrPanelLayout );
            }

            if ( is_array( $arrDataContainer[ $strDcConfigType ]['sorting']['fields'] ) && !empty( $arrDataContainer[ $strDcConfigType ]['sorting']['fields'] ) ) {

                $arrFields = [];
                $arrSortingFields = $arrDataContainer[ $strDcConfigType ]['sorting']['fields'];

                foreach ( $arrSortingFields as $strField ) {

                    $strUpperCaseField = strtoupper( $strField );

                    if ( stripos( $strUpperCaseField, 'ASC' ) || stripos( $strUpperCaseField, 'DESC' ) ) {

                        $arrFieldParameter = explode( ' ' , $strField );

                        if ( !Toolkit::isEmpty( $arrFieldParameter[0] ) ) {

                            $arrFields[] = $arrFieldParameter[0];
                        }

                        continue;
                    }

                    $arrFields[] = $strField;
                }

                $arrReturn['sortingFields'] = serialize( $arrFields );
            }
        }

        return $arrReturn;
    }


    protected function convertDcLabelToCatalog( $arrReturn, $arrDataContainer, $varAttributes, $strDcConfigType ) {

        if ( is_array( $arrDataContainer[ $strDcConfigType ]['label'] ) ) {

            if ( isset( $arrDataContainer[ $strDcConfigType ]['label']['format'] ) ) {

                $arrReturn['format'] = $arrDataContainer[ $strDcConfigType ]['label']['format'];
            }

            if ( $arrDataContainer[ $strDcConfigType ]['label']['showColumns'] ) {

                $arrReturn['showColumns'] = '1';
            }

            if ( is_array( $arrDataContainer[ $strDcConfigType ]['label']['fields'] ) && !empty( $arrDataContainer[ $strDcConfigType ]['label']['fields'] ) ) {

                $arrReturn['labelFields'] = serialize( $arrDataContainer[ $strDcConfigType ]['label']['fields'] );
            }
        }

        return $arrReturn;
    }


    protected function convertDcOperationsToCatalog( $arrReturn, $arrDataContainer, $varAttributes, $strDcConfigType ) {

        if ( $this->blnCore ) {

            $arrReturn['operations'] = '';

            return $arrReturn;
        }

        if ( is_array( $arrDataContainer[ $strDcConfigType ]['operations'] ) ) {

            $arrOperators = [];
            $arrOperatorParameter = array_keys( $arrDataContainer[ $strDcConfigType ]['operations'] );

            if ( is_array( $arrOperatorParameter ) && !empty( $arrOperatorParameter ) ) {

                foreach ( $arrOperatorParameter as $strOperator ) {

                    if ( in_array( $strOperator, Toolkit::$arrOperators ) ) {

                        $arrOperators[] = $strOperator;
                    }
                }

                $arrReturn['operations'] = serialize( $arrOperators );
            }
        }

        return $arrReturn;
    }


    protected function convertCatalogToDcConfig( $arrReturn, $arrCatalog, $varAttributes, $strDcConfigType ) {

        if ( !is_array( $arrReturn[ $strDcConfigType ] ) ) $arrReturn[ $strDcConfigType ] = [];

        if ( $arrCatalog['pTable'] ) {

            $arrReturn[ $strDcConfigType ]['pTable'] = $arrCatalog['pTable'];
        }

        if ( is_array( $arrCatalog['cTables'] ) && !empty( $arrCatalog['cTables'] ) ) {

            $arrReturn[ $strDcConfigType ]['ctable'] = $arrCatalog['cTables'];
        }

        if ( $arrCatalog['addContentElements'] ) {

            if ( !is_array( $arrReturn[ $strDcConfigType ]['ctable'] ) ) {

                $arrReturn[ $strDcConfigType ]['ctable'] = [];
            }

            $arrReturn[ $strDcConfigType ]['ctable'][] = 'tl_content';
        }

        return $arrReturn;
    }


    protected function convertCatalogToDcSorting( $arrReturn, $arrCatalog, $varAttributes, $strDcConfigType ) {

        if ( !is_array( $arrReturn[ $strDcConfigType ] ) ) $arrReturn[ $strDcConfigType ] = [];

        if ( !Toolkit::isEmpty( $arrCatalog['mode'] )) {

            $arrReturn[ $strDcConfigType ]['sorting']['mode'] = $arrCatalog['mode'];
        }

        if ( !Toolkit::isEmpty( $arrCatalog['flag'] )) {

            $arrReturn[ $strDcConfigType ]['sorting']['flag'] = $arrCatalog['flag'];
        }

        if ( !Toolkit::isEmpty( $arrCatalog['panelLayout'] ) ) {

            $arrReturn[ $strDcConfigType ]['sorting']['panelLayout'] = Toolkit::createPanelLayout( $arrCatalog['panelLayout'] );
        }

        if ( is_array( $arrCatalog['sortingFields'] ) && !empty( $arrCatalog['sortingFields'] ) ) {

            $arrReturn[ $strDcConfigType ]['sorting']['fields'] = $arrCatalog['sortingFields'];
        }

        return $arrReturn;
    }


    protected function convertCatalogToDcLabel( $arrReturn, $arrCatalog, $varAttributes, $strDcConfigType ) {

        if ( !is_array( $arrReturn[ $strDcConfigType ] ) ) $arrReturn[ $strDcConfigType ] = [];

        $arrReturn[ $strDcConfigType ]['label']['format'] = $arrCatalog['format'];
        $arrReturn[ $strDcConfigType ]['label']['showColumns'] =  $arrCatalog['showColumns'] ? true : false;

        if ( is_array( $arrCatalog['labelFields'] ) && !empty( $arrCatalog['labelFields'] ) ) {

            $arrReturn[ $strDcConfigType ]['label']['fields'] = $arrCatalog['labelFields'];
        }

        return $arrReturn;
    }


    protected function convertCatalogToDcOperations( $arrReturn, $arrCatalog, $varAttributes, $strDcConfigType ) {

        // @todo

        return $arrReturn;
    }


    protected function convertCatalogFieldsToDcFields( $arrReturn, $arrCatalog, $varAttributes, $strDcConfigType ) {

        // @todo

        return $arrReturn;
    }
}
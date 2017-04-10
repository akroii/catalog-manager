<?php

namespace CatalogManager;

class DCABuilderHelper extends CatalogController {


    public function __construct() {

        parent::__construct();

        $this->import( 'I18nCatalogTranslator' );
    }


    public $arrReservedFields = [

        'id',
        'pid',
        'stop',
        'start',
        'title',
        'alias',
        'invisible'
    ];


    public $arrForbiddenInputTypes = [

        'map',
        'message',
        'fieldsetStart',
        'fieldsetStop'
    ];


    public $arrNoFieldnameRequired = [

        'fieldsetStart',
        'fieldsetStop'
    ];

    
    public $arrReadOnlyInputTypes = [

        'message'
    ];

    
    public $arrInputTypes = [

        'text' => 'text',
        'date' => 'text',
        'number' => 'text',
        'hidden' => 'text',
        'radio' => 'radio',
        'select' => 'select',
        'upload' => 'fileTree',
        'textarea' => 'textarea',
        'checkbox' => 'checkbox',
        'message' => 'catalogMessageWidget'
    ];


    public $arrSQLStatements = [

        'c256' => "varchar(256) NOT NULL default ''",
        'c1' => "char(1) NOT NULL default ''",
        'c16' => "varchar(16) NOT NULL default ''",
        'c32' => "varchar(32) NOT NULL default ''",
        'c64' => "varchar(64) NOT NULL default ''",
        'c128' => "varchar(128) NOT NULL default ''",
        'c512' => "varchar(512) NOT NULL default ''",
        'c1024' => "varchar(1024) NOT NULL default ''",
        'c2048' => "varchar(2048) NOT NULL default ''",
        'i5' => "smallint(5) unsigned NOT NULL default '0'",
        'i10' => "int(10) unsigned NOT NULL default '0'",
        'text' => "text NULL",
        'blob' => "blob NULL",
        'binary' => "binary(16) NULL"
    ];


    public function setInputType( $strType ) {

        return $this->arrInputTypes[ $strType ] ? $this->arrInputTypes[ $strType ] : 'text';
    }


    public function convertCatalogFields2DCA( $arrFields, $arrDCAContainer = [], $arrCatalog = [] ) {

        foreach ( $arrFields as $arrField ) {

            if ( !$this->isValidField( $arrField ) ) continue;

            $arrDCAContainer[ $arrField['fieldname'] ] = $this->convertCatalogField2DCA( $arrField, $arrCatalog );
        }

        return $arrDCAContainer;
    }


    public function convertCatalogField2DCA( $arrField, $arrCatalog = [] ) {

        $arrDCAField = [

            'label' => $this->I18nCatalogTranslator->getFieldLabel( $arrField['fieldname'], $arrField['label'], $arrField['description'] ),
            'inputType' => $this->setInputType( $arrField['type'] ),

            'eval' => [

                'unique' => Toolkit::getBooleanByValue( $arrField['unique'] ),
                'nospace' => Toolkit::getBooleanByValue( $arrField['nospace'] ),
                'mandatory' => Toolkit::getBooleanByValue( $arrField['mandatory'] ),
                'doNotCopy' => Toolkit::getBooleanByValue( $arrField['doNotCopy'] ),
                'allowHtml' => Toolkit::getBooleanByValue( $arrField['allowHtml'] ),
                'tl_class' => Toolkit::deserializeAndImplode( $arrField['tl_class'], ' ' ),
                'trailingSlash' => Toolkit::getBooleanByValue( $arrField['trailingSlash'] ),
                'doNotSaveEmpty' => Toolkit::getBooleanByValue( $arrField['doNotSaveEmpty'] ),
                'spaceToUnderscore' => Toolkit::getBooleanByValue( $arrField['spaceToUnderscore'] ),
            ],

            'sorting' => Toolkit::getBooleanByValue( $arrField['sort'] ),
            'search' => Toolkit::getBooleanByValue( $arrField['search'] ),
            'filter' => Toolkit::getBooleanByValue( $arrField['filter'] ),
            'exclude' => Toolkit::getBooleanByValue( $arrField['exclude'] ),
            'sql' => $this->arrSQLStatements[ $arrField['statement'] ]
        ];

        $arrDCAField['_placeholder'] = $arrField['placeholder'];
        $arrDCAField['_disableFEE'] = $arrField['disableFEE'];
        $arrDCAField['_fieldname'] = $arrField['fieldname'];
        $arrDCAField['_palette'] = $arrField['_palette'];
        $arrDCAField['_type'] = $arrField['type'];

        if ( $arrField['flag'] ) {

            $arrDCAField['default'] = $arrField['flag'];
        }

        if ( Toolkit::isDefined( $arrField['value'] ) && is_string( $arrField['value'] ) ) {

            $arrDCAField['default'] = $arrField['value'];
        }

        if ( Toolkit::isDefined( $arrField['useIndex'] ) ) {

            $arrDCAField['eval']['doNotCopy'] = true;

            if ( $arrField['useIndex'] == 'unique' ) {

                $arrDCAField['eval']['unique'] = true;
            }
        }

        switch ( $arrField['type'] ) {

            case 'text':

                $arrDCAField = Text::generate( $arrDCAField, $arrField );

                break;

            case 'date':

                $arrDCAField = DateInput::generate( $arrDCAField, $arrField );

                break;

            case 'hidden':

                $arrDCAField = Hidden::generate( $arrDCAField, $arrField );

                break;

            case 'number':

                $arrDCAField = Number::generate( $arrDCAField, $arrField );

                break;

            case 'textarea':

                $arrDCAField = Textarea::generate( $arrDCAField, $arrField );

                break;

            case 'select':

                $arrDCAField = Select::generate( $arrDCAField, $arrField, $arrCatalog );

                break;

            case 'radio':

                $arrDCAField = Radio::generate( $arrDCAField, $arrField );

                break;

            case 'checkbox':

                $arrDCAField = Checkbox::generate( $arrDCAField, $arrField );

                break;

            case 'upload':

                $arrDCAField = Upload::generate( $arrDCAField, $arrField );

                break;

            case 'message':

                $arrDCAField = MessageInput::generate( $arrDCAField, $arrField );

                break;
        }

        return $arrDCAField;
    }


    public function getPredefinedDCFields() {

        $arrReturn = [];

        foreach ( $this->getPredefinedFields() as $strFieldname => $arrField ) {

            if ( !$this->isValidField( $arrField ) ) continue;

            $arrReturn[ $strFieldname ] = $this->convertCatalogField2DCA( $arrField );
        }

        return $arrReturn;
    }


    public function getPredefinedFields() {

        return [

            'id' => [

                'type' => 'number',
                'fieldname' => 'id',
                'statement' => 'i10',
                'invisible' => '',
                'disableFEE' => true,
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['id'][0],
                'description' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['id'][1],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['id'][0],
            ],

            'tstamp' => [

                'type' => 'date',
                'statement' => 'i10',
                'fieldname' => 'tstamp',
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['tstamp'][0],
                'description' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['tstamp'][1],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['tstamp'][0],
                'invisible' => '',
                'disableFEE' => true,
            ],

            'pid' => [

                'type' => 'number',
                'statement' => 'i10',
                'fieldname' => 'pid',
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['pid'][0],
                'description' =>  &$GLOBALS['TL_LANG']['catalog_manager']['fields']['pid'][1],
                'placeholder' =>  &$GLOBALS['TL_LANG']['catalog_manager']['fields']['pid'][0],
                'invisible' => '',
                'disableFEE' => true,
            ],

            'sorting' => [

                'type' => 'number',
                'statement' => 'i10',
                'fieldname' => 'sorting',
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['sorting']['pid'][0],
                'description' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['sorting'][1],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['sorting'][0],
                'invisible' => '',
                'disableFEE' => true,
            ],

            'title' => [

                'type' => 'text',
                'fieldname' => 'title',
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][0],
                'description' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][1],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][0],
                'statement' => 'c128',
                'maxlength' => '128',
                'exclude' => '1',
                'tl_class' => serialize( [ 'w50' ] ),
                '_palette' => 'general_legend',
                'invisible' => ''
            ],

            'alias' => [

                'type' => 'text',
                'fieldname' => 'alias',
                'title' =>  &$GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0],
                'description' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][1],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0],
                'statement' => 'c128',
                'maxlength' => '128',
                'mandatory' => '1',
                'unique' => '1',
                'exclude' => '1',
                'doNotCopy' => '1',
                'rgxp' => 'alias',
                'tl_class' => serialize( [ 'w50' ] ),
                '_palette' => 'general_legend',
                'invisible' => ''
            ],

            'invisible' => [

                'exclude' => '1',
                'statement' => 'c1',
                'type' => 'checkbox',
                'fieldname' => 'invisible',
                'title' =>  &$GLOBALS['TL_LANG']['catalog_manager']['fields']['invisible'][0],
                'description' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['invisible'][1],
                '_palette' => 'invisible_legend',
                'invisible' => '',
                'placeholder' => ''
            ],

            'start' => [

                'type' => 'date',
                'exclude' => '1',
                'rgxp' => 'datim',
                'statement' => 'c16',
                'datepicker' => true,
                'fieldname' => 'start',
                '_palette' => 'invisible_legend',
                'tl_class' =>  serialize( [ 'w50 wizard' ] ),
                'title' =>  &$GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][0],
                'description' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][1],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][0],
                'invisible' => ''
            ],

            'stop' => [

                'type' => 'date',
                'exclude' => '1',
                'rgxp' => 'datim',
                'statement' => 'c16',
                'datepicker' => true,
                'fieldname' => 'stop',
                '_palette' => 'invisible_legend',
                'tl_class' =>  serialize( [ 'w50 wizard' ] ),
                'title' =>  &$GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][0],
                'description' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][1],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][0],
                'invisible' => ''
            ]
        ];
    }

    
    public function isValidField( $arrField ) {

        if ( empty( $arrField ) && !is_array( $arrField ) ) return false;

        if ( !$arrField['type'] ) return false;

        if ( in_array( $arrField['type'], $this->arrForbiddenInputTypes ) && !in_array( $arrField['type'], $this->arrReadOnlyInputTypes ) ) return false;

        return true;
    }
}
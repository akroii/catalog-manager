<?php

namespace CatalogManager;

class GeoCoding extends CatalogController {

    private $strCity;
    private $strStreet;
    private $strPostal;
    private $strCountry;
    private $strStreetNumber;

    private $arrGoogleMapsCache = [];


    public function __construct() {

        parent::__construct();
    }


    public function getCords( $strAddress = '', $strLanguage = 'en', $blnServer = false ) {

        $arrReturn = [ 'lat' => '', 'lng' => '' ];

        if ( !$strAddress ) {

            $arrAddress = [];

            if ( $this->strStreet ) $arrAddress[] = $this->strStreet;

            if ( $this->strStreetNumber ) $arrAddress[] = $this->strStreetNumber;

            if ( $this->strPostal ) $arrAddress[] = $this->strPostal;

            if ( $this->strCity ) $arrAddress[] = $this->strCity;

            if ( $this->strCountry ) $arrAddress[] = $this->strCountry;
            
            $strAddress = implode( ',' , $arrAddress );
        }

        $strGoogleIDKey = '';
        $strCacheID = md5( urlencode( $strAddress ) );
        $strGoogleID = $blnServer ? \Config::get('catalogGoogleMapsServerKey') : \Config::get('catalogGoogleMapsClientKey');

        if( is_array( $this->arrGoogleMapsCache[ $strCacheID ] ) ) {

            return $this->arrGoogleMapsCache[ $strCacheID ];
        }

        if ( $strGoogleID ) {

            $strGoogleIDKey = sprintf( '&key=%s', $strGoogleID );
        }

        $strGoogleMapsRequest = sprintf( 'https://maps.googleapis.com/maps/api/geocode/json?address=%s%s&language=%s&region=%s', urlencode( $strAddress ), $strGoogleIDKey, urlencode( $strLanguage ), strlen( $strLanguage ) );

        $objRequest = new \Request();
        $objRequest->send( $strGoogleMapsRequest );

        if ( $objRequest->hasError() ) {

            return $arrReturn;
        }

        $arrResponse = $objRequest->response ? json_decode( $objRequest->response, true ) : [];

        if ( isset( $arrResponse['error_message'] ) && $arrResponse['error_message'] ) {

            \System::log( $arrResponse['error_message'], '\CatalogManager\GeoCoding\getCords', 'Google Maps' );

            return $arrReturn;
        }

        if( !empty( $arrResponse ) && is_array( $arrResponse ) ) {

            $arrGeometry = $arrResponse['results'][0]['geometry'];

            $arrReturn['lat'] = $arrGeometry['location'] ? $arrGeometry['location']['lat'] : '';
            $arrReturn['lng'] = $arrGeometry['location'] ? $arrGeometry['location']['lng'] : '';

            $this->arrGoogleMapsCache[ $strCacheID ] = $arrReturn;

            return $arrReturn;
        }

        return $arrReturn;
    }


    public function setCity( $strCity ) {

        $this->strCity = $strCity ? $strCity : '';
    }


    public function setStreet( $strStreet ) {

        $this->strStreet = $strStreet ? $strStreet : '';
    }


    public function setStreetNumber( $strStreetNumber ) {

        $this->strStreetNumber = $strStreetNumber ? $strStreetNumber : '';
    }


    public function setPostal( $strPostal ) {

        $this->strPostal = $strPostal ? $strPostal : '';
    }


    public function setCountry( $strCountry ) {

        $this->strCountry = $strCountry ? $strCountry : '';
    }
}
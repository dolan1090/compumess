<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Components;


class PriceRoundingService
{

    const ROUNDING_TYPE_DOWN = 'roundOff';
    const ROUNDING_TYPE_UP = 'roundUp';

    public function __construct()
    {
    }

    public function round( float $number, int $decimalPlaces, string $roundingType ): float
    {

        switch ($roundingType) {
            case self::ROUNDING_TYPE_DOWN:
                $result = $this->roundDown( $number, $decimalPlaces );
                break;
            case self::ROUNDING_TYPE_UP:
                $result = $this->roundUp( $number, $decimalPlaces );
                break;
            default:
                $result = $this->roundCommercial( $number, $decimalPlaces );
        }

        return $result;
    }

    private function roundCommercial( float $number, int $decimalPlaces ): float
    {
        return round( $number, $decimalPlaces );
    }

    private function roundUp( float $number, int $decimalPlaces ): float
    {
        return ceil($number * pow(10, $decimalPlaces)) / pow(10, $decimalPlaces);
    }

    private function roundDown( float $number, int $decimalPlaces ): float
    {
        return floor($number * pow(10, $decimalPlaces)) / pow(10, $decimalPlaces);
    }

}

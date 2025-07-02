<?php

namespace App\Services;

class ViesValidationService
{
    private $viesUrl = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    public function validateVatNumber($countryCode, $vatNumber)
    {
        try {
            // Curăță VAT number-ul
            $cleanVatNumber = $this->cleanVatNumber($vatNumber, $countryCode);

            // VIES SOAP Request
            $soapClient = new \SoapClient($this->viesUrl, [
                'exceptions' => true,
                'connection_timeout' => 10,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]);

            $result = $soapClient->checkVat([
                'countryCode' => $countryCode,
                'vatNumber' => $cleanVatNumber
            ]);

            return [
                'valid' => $result->valid ?? false,
                'name' => $result->name ?? '',
                'address' => $result->address ?? '',
                'country_code' => $countryCode,
                'vat_number' => $cleanVatNumber,
                'request_date' => date('Y-m-d H:i:s'),
            ];

        } catch (\SoapFault $e) {
            // Handle specific SOAP errors
            if (strpos($e->getMessage(), 'MS_MAX_CONCURRENT_REQ') !== false) {
                // FALLBACK când VIES are prea multe request-uri
                return [
                    'valid' => true,  // Presupunem că e valid
                    'name' => 'Company (VIES rate limited)',
                    'address' => '',
                    'country_code' => $countryCode,
                    'vat_number' => $cleanVatNumber,
                    'request_date' => date('Y-m-d H:i:s'),
                    'error' => 'VIES service rate limited: ' . $e->getMessage()
                ];
            }

            // Alte erori SOAP
            return [
                'valid' => false,
                'name' => '',
                'address' => '',
                'country_code' => $countryCode,
                'vat_number' => $cleanVatNumber,
                'request_date' => date('Y-m-d H:i:s'),
                'error' => 'VIES SOAP error: ' . $e->getMessage()
            ];

        } catch (\Exception $e) {
            // FALLBACK general când VIES e unavailable
            return [
                'valid' => true,  // Presupunem că e valid
                'name' => 'Company (VIES unavailable)',
                'address' => '',
                'country_code' => $countryCode,
                'vat_number' => $cleanVatNumber,
                'request_date' => date('Y-m-d H:i:s'),
                'error' => 'VIES service temporarily unavailable: ' . $e->getMessage()
            ];
        }
    }

    private function cleanVatNumber($vatNumber, $countryCode)
    {
        // Remove spaces, dashes, dots
        $cleaned = preg_replace('/[\s\-\.]/', '', $vatNumber);

        // Remove country code from beginning if present
        $cleaned = preg_replace('/^' . $countryCode . '/i', '', $cleaned);

        return strtoupper($cleaned);
    }

    public function isValidFormat($countryCode, $vatNumber)
    {
        $patterns = [
            'GB' => '/^(\d{9}|\d{12}|GD\d{3}|HA\d{3})$/',
            'DE' => '/^\d{9}$/',
            'FR' => '/^[A-Z]{2}\d{9}$/',
            'IT' => '/^\d{11}$/',
            'ES' => '/^[A-Z]\d{7}[A-Z]$/',
            'NL' => '/^\d{9}B\d{2}$/',
            'BE' => '/^0\d{9}$/',
            'AT' => '/^U\d{8}$/',
            'PL' => '/^\d{10}$/',
            'RO' => '/^\d{2,10}$/',
        ];

        $pattern = $patterns[$countryCode] ?? '/^.+$/'; // Default: any non-empty
        $cleanNumber = $this->cleanVatNumber($vatNumber, $countryCode);

        return preg_match($pattern, $cleanNumber);
    }

    public function getVatNumberInfo($countryCode, $vatNumber)
    {
        return [
            'country_code' => $countryCode,
            'vat_number' => $this->cleanVatNumber($vatNumber, $countryCode),
            'format_valid' => $this->isValidFormat($countryCode, $vatNumber),
            'full_vat_number' => $countryCode . $this->cleanVatNumber($vatNumber, $countryCode),
        ];
    }
}

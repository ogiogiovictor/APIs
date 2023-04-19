<?php

namespace App\Helpers;

class StringHelper
{
  

     /**
     * Generate initials from a name
     *
     * @source https://chrisblackwell.me/generate-perfect-initials-using-php/ Generate Initials using PHP
     */
    public static function generateInitials(?string $name = null): string {
        if(is_null($name)) return '';

        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        return self::makeInitialsFromSingleWord($name);
    }

     /**
     * Make initials from a word with no spaces
     *
     * @source https://chrisblackwell.me/generate-perfect-initials-using-php/ Generate Initials using PHP
     */
    public static function makeInitialsFromSingleWord(?string $name = null): string {
        if(is_null($name)) return '';

        preg_match_all('#([A-Z]+)#', $name, $capitals);
        if (count($capitals[1]) >= 2) {
            return substr(implode('', $capitals[1]), 0, 2);
        }
        return strtoupper(substr($name, 0, 2));
    }

    public static function removeSpecialCharsAndSlashes($accountNumber) {
        // Replace all non-alphanumeric characters and slashes with an empty string
        return preg_replace('/[^a-zA-Z0-9]/', '', $accountNumber);
      }


      public static function formatAccountNumber($cleanedAccountNumber) {
        // Define the regular expression pattern for formatting the account number
        $pattern = '/(\d{2})(\d{2})(\d{2})(\d{4})(\d{2})/';
    
        // Use the preg_replace() function to format the account number
        $formattedAccountNumber = preg_replace($pattern, '$1/$2/$3/$4-$5', $cleanedAccountNumber);
    
        // Return the formatted account number
        return $formattedAccountNumber;
    }
    


    public static function convertHTMLToText(string $html): string
    {
        $text = preg_replace("/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($html))));
        return $text;
    }

    public static function formatNumber($number) {
        return number_format((float) $number, 2, '.', ',');
    } 

    
  


}
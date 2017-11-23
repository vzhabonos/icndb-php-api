<?php
/**
 * Copyright (C) 2017 Viacheslav Zhabonos
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class ICNDB
{
    private static $requestUrl = 'https://api.icndb.com/';

    /**
     * Returns array of random jokes by specified parameters
     * @param int $quantity
     * @param array $limitTo
     * @param array $exclude
     * @param string $firstName
     * @param string $lastName
     * @return null|array
     */
    public static function getRandomJokes($quantity = 1, $limitTo = array(), $exclude = array(), $firstName = '', $lastName  = '')
    {
        $url = self::$requestUrl . 'jokes/random/';
        $queryParameters = array();

        if($quantity > 1)
            $url .= "$quantity/";

        if(!empty($limitTo)) {
            $validCategories = array();
            $existingCategories = self::getCategories();
            if($existingCategories) {
                foreach ($limitTo as $category) {
                    if(in_array($category, $existingCategories))
                        $validCategories[] = $category;
                }
                if(!empty($validCategories))
                    $queryParameters[] = 'limitTo=[' . implode(',', $validCategories) . ']';
            }
        }

        if(!empty($exclude)) {
            $validCategories = array();

            if(!isset($existingCategories))
                $existingCategories = self::getCategories();

            if($existingCategories) {
                foreach ($exclude as $category) {
                    if(in_array($category, $existingCategories))
                        $validCategories[] = $category;
                }
                if(!empty($validCategories))
                    $queryParameters[] = 'exclude=[' . implode(',', $validCategories) . ']';
            }
        }

        if(!empty($firstName))
            $queryParameters[] = "firstName=$firstName";

        if(!empty($lastName))
            $queryParameters[] = "lastName=$lastName";

        if(!empty($queryParameters))
            $url .= '?' . implode('&', $queryParameters);

        $response = self::makeRequest($url);

        if(isset($response['type']) && $response['type'] == 'success') {
            if($quantity == 1)
                return [$response['value']];
            return $response['value'];
        }

        return null;
    }

    /**
     * Returns all jokes from database
     * @return null|array
     */
    public static function getAllJokes()
    {
        $response = self::makeRequest(self::$requestUrl . 'jokes/');

        if(isset($response['type']) && $response['type'] == 'success')
            return $response['value'];

        return null;
    }

    /**
     * Returns array of categories
     * @return null|array
     */
    public static function getCategories()
    {
        $response = self::makeRequest(self::$requestUrl . 'categories');
        if(isset($response['type']) && $response['type'] == 'success')
            return $response['value'];

        return null;
    }

    /**
     * Returns count of all jokes at database
     * @return int
     */
    public static function getCountOfAllJokes()
    {
        $response = self::makeRequest(self::$requestUrl . 'jokes/count/');
        if(isset($response['type']) && $response['type'] == 'success')
            return intval($response['value']);
        return 0;
    }

    /**
     * Returns specific joke by id
     * @param $id
     * @return null|array
     */
    public static function getJokeById($id)
    {
        $response = self::makeRequest(self::$requestUrl . 'jokes/' . $id);
        if(isset($response['type']) && $response['type'] == 'success')
            return $response['value'];
        return null;
    }

    /**
     * @param string $url
     * @return mixed
     */
    private static function makeRequest($url = '')
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return self::formatResponse($response);
    }

    /**
     * @param $response string in JSON format
     * @return mixed
     */
    private static function formatResponse($response)
    {
        return json_decode($response, true);
    }

}
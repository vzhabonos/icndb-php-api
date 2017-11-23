<?php
/**
 * Copyright (C) 2017 Viacheslav Zhabonos
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
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
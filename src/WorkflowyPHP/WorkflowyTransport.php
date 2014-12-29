<?php

/*
 * This file is part of the WorkflowyPHP package.
 *
 * (c) Johan Satgé
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace WorkflowyPHP;

class WorkflowyTransport
{

    const LOGIN_URL = 'https://workflowy.com/accounts/login/';
    const API_URL   = 'https://workflowy.com/%s';
    const TIMEOUT   = 5;

    /**
     * Sends a CURL request to the request URL, by using the given POST data and parameters
     * @param string $url
     * @param array $post_fields
     * @param bool $return_headers
     * @param bool $return_json
     * @throws WorkflowyException
     * @return array|string
     */
    public static function curl($url, $post_fields, $return_headers, $return_json)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HEADER, $return_headers ? true : false);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        if (is_array($post_fields) && count($post_fields) > 0)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }
        $raw_data = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);
        if (!empty($error))
        {
            throw new WorkflowyException($error);
        }
        if ($return_json)
        {
            $json = json_decode($raw_data, true);
            if ($json === null)
            {
                throw new WorkflowyException('Could not decode JSON');
            }
            return $json;
        }
        return $raw_data;
    }

}

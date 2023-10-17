<?php
/**
 * This class is the Soap Webservice Server class to handle all webservices.
 * @author Samir Shelar
 * @created  15-Jun-2010 16:10 PM PST
 * @changed 20-Aug-2020 17:00PM IST
 * @reviewed by
 * @version 1.0
 * @package CSG WebService
 */
class clsCurlClient
{
    private $mObjClient;
    private $bypassSSLCert;

    /**
     * Constructor of the class
     */
    public function __construct($wsdlPath, $bypassSSL = false)
    {
        // Set bypassSSLCert true if want to by pass the SSL cerificate warnings.
        $this->bypassSSLCert = $bypassSSL;
    }

    /**
     * Function to call the webservice
     * @param string $strUrl
     * @param array $arrParams
     * @return array $arrUpdatedResult
     * @author Umesh W
     * @ Date:  16-JUNE-2010
     * @ Reviewed by: TBD
     */
    public function callService($strUrl, $arrParams)
    {
        //Call service
        //$this->mObjClient->$strMethod($request);
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type'=>'text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrParams);
        if ($this->bypassSSLCert == true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        $proArray = array();
        if (curl_exec($ch) === false) {
            $proArray['Error']['Description'] = curl_error($ch);
            return $proArray;
        } else {
            $responseXML=curl_exec($ch);
            $proArray = $this->xmlToArray($responseXML);
            return $proArray;
        }
    }

    /**
     * Function to convert the xml into array
     * @param string $responseXML
     * @return array $xml_array
     * @author Umesh W
     * @ Date:  16-JUNE-2010
     * @ Reviewed by: TBD
     */

    public function xmlToArray($responseXML, $get_attributes = 1, $priority = 'tag')
    {
        $contents = "";
        if (!function_exists('xml_parser_create')) {
            return array();
        }

        $contents = $responseXML;
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        if (!$xml_values) {
            return;
        }

        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = & $xml_array;
        $repeated_tag_index = array();

        foreach ($xml_values as $data) {
            unset($attributes, $value);
            extract($data);
            $result = array();
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag') {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                }
            }
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag') {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    }
                }
            }
            if ($type == "open") {
                $parent[$level -1] = & $current;
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                    $current[$tag] = $result;
                    if ($attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                } else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        );
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            } elseif ($type == "complete") {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                } else {
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        );
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            } elseif ($type == 'close') {
                $current = & $parent[$level -1];
            }
        }
        return ($xml_array);
    }


    /**
     * Function to call the REST webservice
     * @param string $strUrl
     * @param array $arrParams
     * @return array $arrUpdatedResult
     * @author Akshay Jadhav & Jana Davangave
     * @ Date:  31-July-2020
     * @ Reviewed by:
     */
    public function callServiceRest($strUrl, $arrParams)
    {
        $curl = curl_init();
        $headers = [];
        $proArray = [];
        curl_setopt_array($curl, array(
            CURLOPT_URL => $strUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $arrParams['Header'],
        ));

        if (isset($arrParams['HeaderFunction'])) {
            curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) { // ignore invalid headers
                    return $len;
                }
                $headers[strtolower(trim($header[0]))][] = trim($header[1]);
                return $len;
            });
        }

        $response = curl_exec($curl);
        $proArray['http_status'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $proArray['headers'] = $headers;
        $proArray['arrayResponse'] = json_decode($response);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $proArray;
        }
    }
}

<?php

namespace Sparky7\Api\Proxy;

use Sparky7\Api\Response\APIResponse;
use Sparky7\Helper\Json;
use Exception;

/**
 * API style proxy.
 */
class APIProxy
{
    private $call;
    private $content;

    /**
     * Construct method.
     */
    final public function __construct()
    {
        $this->call = null;
        $this->content = null;
    }

    /**
     * Get method.
     *
     * @param string $key Parameter name
     *
     * @return string Parameter value
     */
    final public function __get($key)
    {
        return (isset($this->{$key})) ? $this->{$key} : null;
    }

    /**
     * Get file extension from mime.
     *
     * @param string $mime Mime type
     *
     * @return string Extension
     */
    final public function fileExtension($mime)
    {
        switch ($mime) {
            case 'application/pdf':
                return 'pdf';
            case 'application/zip':
                return 'zip';
            case 'image/gif':
                return 'gif';
            case 'image/jpeg':
                return 'jpg';
            case 'image/png':
                return 'png';
            case 'text/css':
                return 'css';
            case 'text/html':
                return 'html';
            case 'text/javascript':
                return 'js';
            case 'text/plain':
                return 'txt';
            case 'text/xml':
                return 'xml';
            default:
                throw new Exception('Invalid mime type: '.$mime);
        }
    }

    /**
     * Makes a DELETE request to the API via cURL.
     *
     * @param string $url      URL Endpoint
     * @param array  $argument Parameters
     * @param bool True or false
     */
    final public function delete($url, array $argument = [])
    {
        return $this->sendRequest($url, strtoupper(__FUNCTION__), $argument);
    }

    /**
     * Makes a GET request to the API via cURL.
     *
     * @param string $url      URL Endpoint
     * @param array  $argument Parameters
     * @param bool True or false
     */
    final public function get($url, array $argument = [])
    {
        return $this->sendRequest($url, strtoupper(__FUNCTION__), $argument);
    }

    /**
     * Makes a POST request to the API via cURL.
     *
     * @param string $url      URL Endpoint
     * @param array  $argument Parameters
     * @param bool True or false
     */
    final public function post($url, array $argument = [])
    {
        return $this->sendRequest($url, strtoupper(__FUNCTION__), $argument);
    }

    /**
     * Makes a PUT request to the API via cURL.
     *
     * @param string $url      URL Endpoint
     * @param array  $argument Parameters
     * @param bool True or false
     */
    final public function put($url, array $argument = [])
    {
        return $this->sendRequest($url, strtoupper(__FUNCTION__), $argument);
    }

    /**
     * Validate content type.
     *
     * @param string $content_type Content type
     *
     * @return string Content type
     */
    final private function sanitizeContentType($content_type = null)
    {
        $available = ['application/json', 'application/x-www-form-urlencoded', 'multipart/form-data-encoded'];

        $default = 'application/json';
        $request = (isset($_SERVER['HTTP_CONTENT_TYPE'])) ? filter_var($_SERVER['HTTP_CONTENT_TYPE'], FILTER_SANITIZE_STRING) : null;

        /*
         * Return content type
         *   - Argument content type
         *   - Request content type
         *   - Default content type
         */

        foreach ($available as $value) {
            if (strpos($content_type, $value) !== false) {
                return $value;
            }
        }

        foreach ($available as $value) {
            if (strpos($request, $value) !== false) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Validate request method.
     *
     * @param string $method Method
     *
     * @return string Method
     */
    final private function santizeMethod($method = null)
    {
        $available = ['GET', 'POST', 'PUT', 'DELETE', 'HEAD'];

        $default = 'POST';
        $request = (isset($_SERVER['REQUEST_METHOD'])) ? filter_var($_SERVER['REQUEST_METHOD'], FILTER_SANITIZE_STRING) : null;

        /*
         * Return method
         *   - Argument method
         *   - Request method
         *   - Default method
         */

        if (in_array($method, $available)) {
            return strtoupper($method);
        }

        if (in_array($request, $available)) {
            return strtoupper($request);
        }

        return strtoupper($default);
    }

    /**
     * Makes a request to the API via cURL.
     *
     * @param string $url      URL Endpoint
     * @param string $method   Method
     * @param array  $argument Parameters
     * @param string $rid      Request ID
     * @param APIReseponse API Response Object
     */
    final public function sendRequest($url, $method = null, array $argument = [], $rid = null)
    {
        $uri = parse_url($url);

        $this->call = [];
        $this->call['headers'] = [];
        $this->call['method'] = $this->santizeMethod($method);
        $this->call['content_type'] = $this->sanitizeContentType();
        $this->call['resource'] = $uri['path'];
        $this->call['parameters'] = $argument;
        $this->call['files'] = [];
        $this->call['url'] = $uri['scheme'].'://'.$uri['host'].$uri['path'];

        /*
         * Converts Files into base64
         */

        $files = [];
        foreach ($_FILES as $name => $file) {
            $file_type = null;
            $temp_path = null;

            if (is_array($file['tmp_name'])) {
                $file_name = $file['name'][0];
                $file_type = $file['type'][0];
                $temp_path = $file['tmp_name'][0];
            } else {
                $file_name = $file['name'];
                $file_type = $file['type'];
                $temp_path = $file['tmp_name'];
            }

            $this->call['files'][$name] = 'data:'.mime_content_type($temp_path);
            $this->call['files'][$name] .= ';base64,'.base64_encode(file_get_contents($temp_path));
            $files[] = $temp_path; // store file array for deletion
        }

        /*
         * Set content type
         */

        switch ($this->call['method']) {
            case 'PUT':
            case 'DELETE':
                $this->call['content_type'] = 'application/json';
                break;
        }

        /*
         * Set headers
         */

        $forward_headers = [
            'HTTP_ACCEPT' => 'Accept',
            'HTTP_ACCEPT_ENCODING' => 'Accept-Encoding',
            'HTTP_ACCEPT_LANGUAGE' => 'Accept-Language',
            'HTTP_CACHE_CONTROL' => 'Cache-Control',
            'HTTP_COOKIE' => 'Cookie',
            'HTTP_CONNECTION' => 'Connection',
            'HTTP_PRAGMA' => 'Pragma',
            'HTTP_USER_AGENT' => 'User-Agent',
        ];

        $this->call['headers'][] = 'Content-Type: '.$this->call['content_type'];
        $this->call['headers']['X-Forwarded-For'] = RemoteAddress::ip();
        if (!is_null($rid)) {
            $this->call['headers']['X-Requested-ID'] = $rid;
        }
        $this->call['headers'][] = 'X-Requested-With: XMLHttpRequest';

        foreach ($forward_headers as $expected => $header) {
            if (isset($_SERVER[$expected])) {
                $this->call['headers'][] = $header.': '.$_SERVER[$expected];
            }
        }

        /*
         * Create request.
         *   - Create curl resource
         *   - Configure curl
         *   - Execute curl
         */

        $curl = curl_init();

        switch ($this->call['method']) {
            case 'HEAD':
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_NOBODY, true);
                $this->call['parameters'] = [];
                break;
            case 'GET':
                $this->call['url'] .= '?'.http_build_query($this->call['parameters']);
                $this->call['parameters'] = [];
                break;
            case 'POST':
                if ($this->call['content_type'] === 'multipart/form-data-encoded') {
                    $params = [];
                    foreach (explode('&', http_build_query($this->call['parameters'])) as $key) {
                        $value = explode('=', $key);
                        $params[$value[0]] = urldecode($value[1]);
                    }

                    $this->call['parameters'] = array_merge($params, $this->call['files']);
                } elseif ($this->call['content_type'] === 'application/x-www-form-urlencoded') {
                    $this->call['parameters'] = http_build_query($this->call['parameters']);
                }
                break;
            case 'DELETE':
            case 'PUT':
                break;
            default:
                throw new Exception('Unkown method type: '.$this->call['method']);
        }

        curl_setopt($curl, CURLOPT_URL, $this->call['url']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->call['method']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->call['headers']);
        if ($this->call['content_type'] === 'application/json' && count($this->call['parameters']) > 0) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->call['parameters']));
        } elseif (count($this->call['parameters']) > 0) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->call['parameters']);
        }
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');

        $curl_content = curl_exec($curl);
        $curl_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);

        curl_close($curl);

        /*
         * Clean up temp files
         */

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        /*
         * Grab content
         */

        $this->content = Json::decode($curl_content, false);

        /*
         * Overwrite content on curl error
         */

        if (strlen($curl_error) > 0) {
            $this->content = [
                'rid' => $rid,
                'code' => 503,
                'status' => false,
                'message' => 'Service Unavailable: '.trim($curl_error),
                'response' => null,
                ];
        } elseif ($this->call['method'] === 'HEAD' && strlen($curl_error) === 0) {
            $this->content = [
                'rid' => $rid,
                'code' => $curl_code,
                'status' => true,
                'message' => null,
                'response' => null,
                ];
        } elseif ($this->content === false) {
            $this->content = [
                'rid' => $rid,
                'code' => 503,
                'status' => false,
                'message' => 'Decode error: '.Json::getMessageError(),
                'response' => null,
                ];
        }

        /*
         * Create api response
         */

        $APIResponse = new APIResponse();
        $APIResponse->rid = $this->content['rid'];
        $APIResponse->code = $this->content['code'];
        $APIResponse->errors = (isset($this->content['errors'])) ? $this->content['errors'] : null;
        $APIResponse->status = $this->content['status'];
        $APIResponse->message = $this->content['message'];
        $APIResponse->response = $this->content['response'];

        return $APIResponse;
    }
}

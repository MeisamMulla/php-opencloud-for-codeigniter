<?php
/**
*   The MIT License (MIT)
*   
*   Copyright (c) 2013 Meisam Mulla <Insight Technologies Ltd.>
*   
*   Permission is hereby granted, free of charge, to any person obtaining a copy
*   of this software and associated documentation files (the "Software"), to deal
*   in the Software without restriction, including without limitation the rights
*   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
*   copies of the Software, and to permit persons to whom the Software is
*   furnished to do so, subject to the following conditions:
*   
*   The above copyright notice and this permission notice shall be included in
*   all copies or substantial portions of the Software.
*   
*   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
*   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
*   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
*   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
*   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
*   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
*   THE SOFTWARE.
**/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Opencloud
{
    
    private $CI; 
    private $conn; 
    private $ostore; 
    
    private $opencloud_username; 
    private $opencloud_key; 
    private $opencloud_endpoint;
    private $opencloud_region;

    private $container;

    public $error;
    
    
    function __construct($params = array())
    {
        $this->CI =& get_instance();        
        $this->initialize();
    }
    
    // Initializes the library parameters
    public function initialize()
    {   
        $this->CI->config->load('opencloud');

        $this->opencloud_username  = config_item('opencloud_username');
        $this->opencloud_key       = config_item('opencloud_key');
        $this->opencloud_endpoint  = config_item('opencloud_endpoint');
        $this->opencloud_region    = config_item('opencloud_region');
        
        require_once(APPPATH . 'libraries/opencloud/php-opencloud.php');
        
        try {
            $this->conn = new \OpenCloud\Rackspace($this->opencloud_endpoint, array(
                'username' => $this->opencloud_username,
                'apiKey' => $this->opencloud_key
            ));
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }

        try {
            $this->conn->SetDefaults('ObjectStore', 'cloudFiles', $this->opencloud_region);
            $this->ostore = $this->conn->ObjectStore();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }
    }
    
    public function create_container($name) {
        try {
            $container = $this->ostore->Container();
            $container->Create(array('name' => $name));

            $container->PublishToCDN();

            return true;

        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }

    public function delete_container($name) {
        try {
            $this->ostore->Container($name)->Delete();

            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }
    
    public function list_containers() {
        $containers = array();
        
        try {
            $list = $this->ostore->ContainerList();

            while ($container = $list->Next()) {
                $containers[] = array(
                    'name' => $container->name,
                    'count' => $container->count,
                    'bytes' => $container->bytes
                );
            }

            return $containers;

        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }

    public function set_container($container) {
        try {
            $this->container = $this->ostore->Container($container);
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }

    public function list_objects() {
        $objects = array();
        
        try {
            $list = $this->container->ObjectList();

            while ($item = $list->Next()) {
                $objects[] = array(
                    'name' => $item->name,
                    'content_type' => $item->content_type,
                    'bytes' => $item->bytes,
                    'cdn-url' => $item->PublicUrl()
                );
            }

            return $objects;

        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }
    
    public function add_object($name, $contents, $content_type) {
        try {
            $object = $this->container->DataObject();

            $object->SetData($contents);
            $object->name = $name;
            $object->content_type = $content_type;
            $object->Create();

            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }

    public function delete_object($name) {
        try {
            $this->container->DataObject($name)->Delete();

            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }
}

/* End of file Opencloud.php */
/* Location: ./application/libraries/Opencloud.php */
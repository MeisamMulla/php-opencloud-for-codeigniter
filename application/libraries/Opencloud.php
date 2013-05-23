<?php
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
    
    public function create_container($name, $cdn = true) {
        try {
            $container = $this->ostore->Container();
            $container->Create(array('name' => $name));

            if ($cdn) {
                $cdn_version = $container->PublishToCDN();
            }

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
    
    public function get_containers() {
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

    public function get_container($name) {
        $objects = array();
        
        try {
            $list = $this->container->ObjectList();

            while ($item = $list->Next()) {
                $objects[] = array(
                    'name' => $item->name,
                    'content_type' => $item->content_type,
                    'bytes' => $item->bytes
                );
            }

            return $objects;

        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }
    
    public function add_object($name, $content_type, $contents) {
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

/* End of file opencloud.php */
/* Location: ./application/libraries/Opencloud.php */
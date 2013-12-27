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
    public $last_response;


 	/**
	 * Initialize
	 * 
	 * @note	calls initialize
	 */    
    function __construct()
    {
        $this->CI =& get_instance();        
        $this->initialize();
    }
    

 	/**
	 * Initialize
	 * 
	 * @public
	 * 
	 * @todo	add params to enable the ability to override the config
	 */
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
    
    
   	/**
	 * Reset Request Response
	 * 
	 * @private
	 * 
	 * @desc	Reset internal messages before a new request
	 * 
	 * @return	mixed
	 */    
    private function reset_request_response() {
		$this->error = null;
		$this->last_response = null;
	}


 	/**
	 * Get Last Response
	 * 
	 * @public
	 * 
	 * @desc	This method exposes responses from various methods (end points) within the open 
	 * 			cloud lib. In some cases, if a method in this class returns true, you may
	 * 			still need to inspect the response to get more details (ie. bulk delete will
	 * 			return true if the request was successful, however, you should inspect the 
	 * 			response to see how many files were deleted, failed, etc).
	 * 
	 * @note	The method name contains "last", as an additional reminder that this method only returns
	 * 			the response from the last request made
	 * 
	 * @return	mixed
	 */    
    public function get_last_response() {
		return $this->last_response;
	}
	
	
 	/**
	 * Get Last Error
	 * 
	 * @public
	 * 
	 * @note	The method name contains last, as an additional reminder that this method only returns
	 * 			error information for the last request made
	 * 
	 * @return	mixed	string containing the error message from the caught exception during the last request,
	 * 					null if there was no error
	 */    
    public function get_last_error() {
		return $this->error;
	}


 	/**
	 * Create Container
	 * 
	 * @public
	 * 
	 * @note	This method returns true if the container name you are
	 * 			attempting to create already exists
	 * 
	 * @param	string	the name of the container you wish to create
	 * @return	bool	TRUE on success, FALSE on failure 
	 */    
    public function create_container($name) {
		$this->reset_request_response();
		
        try {
            $container = $this->ostore->Container();
            $this->last_response = $container->Create(array('name' => $name));
            
            if( $this->last_response ) {

				$container->PublishToCDN();
				
			}

            return true;

        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }


 	/**
	 * Delete Container
	 * 
	 * @public
	 * 
	 * @note	Only empty containers can be deleted
	 * 
	 * 			$this->last_response is set after a successful call
	 * 
	 * @param	string	the name of the container you wish to delete
	 * @return	bool	TRUE on success, FALSE on failure 
	 */
    public function delete_container($name) {
		$this->reset_request_response();
		
        try {
            $this->last_response = $this->ostore->Container($name)->Delete();

            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }
  
  
 	/**
	 * List Containers
	 * 
	 * @public
	 * 
	 * @return	mixed	array of container objects available under the current account, 
	 * 					FALSE on an error
	 */  
    public function list_containers() {
		$this->reset_request_response();
		
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


 	/**
	 * Set Container
	 * 
	 * @public
	 * 
	 * @param	string	the name of the container you want to use
	 * @return	bool	TRUE on success, FALSE on failure 
	 */
    public function set_container($container) {
		$this->reset_request_response();
		
        try {
            $this->container = $this->ostore->Container($container);
            
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }


 	/**
	 * List Objects
	 * 
	 * @public
	 * 
	 * @return	mixed	array of data objects in the current container, 
	 * 					FALSE on an error
	 */
    public function list_objects() {
		$this->reset_request_response();
		
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
 
 
 	/**
	 * Add Object
	 * 
	 * @public
	 * 
	 * @note	$this->last_response is set after a successful call
	 * 			
	 * 			EXTRACT ARCHIVE NOTES
	 * 
	 * 			If you are adding an archieved file (tar, tar.gz, tar.bz2) for extraction, you can use the $name param as a directory name for where the files will be extracted to
	 * 			within the current container. To have the files extracted to the root container folder, set the $name param to '/'
	 * 
	 * 				ie. $name = 'folder' would result in all the extracted files being prefixed with `folder/` (ex. 'container/folder/my-file.txt')
	 * 				ie. $name = '/' would result in all the extracted files being added to the root container (ex. 'container/my-file.txt')
	 * 			
	 * 			If you want to easily parse archieved file results (since they are returned in the body of the HTML response) -- pass in an additional header using the $params param
	 * 			with a valid response header accept type (Acceptable formats are text/plain, application/json, application/xml, and text/xml)
	 * 
	 * 				ie. $params[ 'extra_headers' ][ 'Accept' ] = 'application/json' would result in the response body being returned as a JSON string
	 * 			
	 * 			You can than access the response as follows:
	 * 				
	 * 				$response = json_decode( $this->last_response->httpBody() );
	 * 				
	 * 			Where $response would be OBJECT( 'Number Files Created' => int, 'Response Status' => string, 'Errors' => Object Array, 'Response Body' => string )
	 * 
	 * 			A Response Status === '201 Created' indicates all valid files were uploaded successfully
	 * 
	 * 			See documentation (http://docs.rackspace.com/files/api/v1/cf-devguide/content/Extract_Archive-d1e2338.html) for more information on response codes
	 * 
	 * @param	string	the name of file to add
	 * @param	string	the file contents
	 * @param	string	the content type (mime type) of the file
	 * @param	mixed	to extract a tar.gz or tar.bz2 file being added, pass a string containing the type of extraction
	 * 					you want performed after the compressed file is uploaded (ie. tar, tar.gz, tar.bz2)
	 * @param	array	additional params to pass into the create data object call ( name, content_type, extra_headers, send_etag )
	 * @return	bool 	TRUE on success, FALSE on failure  
	 */   
    public function add_object($name, $contents, $content_type = null, $extractArchive = null, $params = array()) {
		$this->reset_request_response();
		
        try {
			      $object = $this->container->DataObject();

            $object->name = $name;
            $object->content_type = $content_type;
            $this->last_response = $object->Create($params, $contents, $extractArchive);
			
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }
	
	
	/**
	 * Delete Object
	 * 
	 * @public
	 * 
	 * @note	$this->last_response is set after a successful call
	 * 
	 * @param	string	the name of file to delete
	 * @return	bool	TRUE on success, FALSE on failure 
	 */
    public function delete_object($name) {
		$this->reset_request_response();
		
        try {
           $this->last_response = $this->container->DataObject($name)->Delete();

            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }
    

	/**
	 * Bulk Delete Objects
	 * 
	 * @public
	 * 
	 * @note	$this->last_response is set after a successful call
	 * 
	 * 			@nyndesigns: I will be making a pull request to the Rackspace PHP OpenCloud PHP SDK in the next few days
	 * 			which will enable this method (BULK DELETE) for a container.
	 * 
	 * @param	array	the names of files to delete
	 * @return	bool	TRUE on success, FALSE on failure 
	 */
    //~ public function bulk_delete_objects($names) {
		//~ $this->reset_request_response();
		//~ 
        //~ try {
            //~ $this->last_response = $this->container->Bulk_Delete($names);
			//~ 
            //~ return true;
        //~ } catch (Exception $e) {
            //~ $this->error = $e->getMessage();
			//~ 
            //~ return false;
        //~ }
	//~ }
}

/* End of file Opencloud.php */
/* Location: ./application/libraries/Opencloud.php */

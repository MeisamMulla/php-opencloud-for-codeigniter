<?php

class cloudfiles extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->library('opencloud');
	}

	public function index() {
		/* Create Container */
		if ($this->opencloud->create_container('My Container')) {
			echo 'Container Created'
		} else {
			echo 'Failed to create container because: ' . $this->opencloud->error;
		}

		/* List Containers */
		$containers = $this->opencloud->list_containers();

		foreach ($containers as $container) {
			echo 'name:' . $container->name;
            echo 'count:' . $container->count;
            echo 'bytes:' . $container->bytes;
		}


		/* List Objects in a Container */
		$this->opencloud->set_container('My Container');
		$objects = $this->opencloud->list_objects();

		foreach ($objects as $object) {
			echo 'name:' . $object->name;
            echo 'content_type:' . $object->content_type;
            echo 'bytes:' . $object->bytes;
		}


		/* Add object to Container */
		$this->opencloud->set_container('My Container');
		$this->opencloud->add_object('text1.txt', 'Hello World!', 'text/plain');
		$this->opencloud->add_object('text2.txt', 'Hello World!'); // Content-type will be auotmatically detected
		$this->opencloud->add_object('text3.txt', 'Hello World!');


		/* Delete Object from Container */
		$this->opencloud->set_container('My Container');
		$this->opencloud->delete_object('text1.txt');
		$this->opencloud->delete_object('text2.txt');


		/* Delete Container */
		if ($this->opencloud->delete_container('My Container')) {
			echo 'Container Deleted'
		} else {
			echo 'Failed to delete container because: ' . $this->opencloud->error;
		}
	}

	public function delete() {
		$this->opencloud->delete_container('abc');
	}
}
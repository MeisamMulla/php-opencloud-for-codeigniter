<?php

class cloudfiles extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->library('opencloud');
	}

	public function index() {
		/* Create Container */
		echo '<h2>Create Container</h2>';
		if ($this->opencloud->create_container('TestContainer')) {
			echo '<p>Container Created</p>';
		} else {
			echo '<p>Failed to create container because: ' . $this->opencloud->error . '</p>';
		}

		/* List Containers */
		echo '<h2>List Containers</h2>';
		$containers = $this->opencloud->list_containers();

		foreach ($containers as $container) {
			echo '<p>';
			echo 'name:' . $container['name'] . '<br />';
			echo 'count:' . $container['count'] . '<br />';
			echo 'bytes:' . $container['bytes'] . '<br />';
			echo '</p>';
		}


		/* Add object to Container */
		echo '<h2>Add Objects</h2>';
		$this->opencloud->set_container('TestContainer');
		$this->opencloud->add_object('text1.txt', 'Hello World!', 'text/plain');
		$this->opencloud->add_object('text2.txt', 'Hello World!', 'text/plain');
		$this->opencloud->add_object('text3.txt', 'Hello World!', 'text/plain');


		/* List Objects in a Container */
		echo '<h2>List Objects</h2>';
		$this->opencloud->set_container('TestContainer');
		$objects = $this->opencloud->list_objects();

		foreach ($objects as $object) {
			echo '<p>';
			echo 'name:' . $object['name'] . '<br />';
			echo 'content_type:' . $object['content_type'] . '<br />';
			echo 'bytes:' . $object['bytes'] . '<br />';
			echo 'CDN url:' . $object['cdn-url'] . '<br />';
			echo '</p>';
		}

		/* Delete Object from Container */
		echo '<h2>Delete Objects</h2>';
		$this->opencloud->set_container('TestContainer');
		$this->opencloud->delete_object('text1.txt');
		$this->opencloud->delete_object('text2.txt');
		$this->opencloud->delete_object('text3.txt');


		/* Delete Container */
		echo '<h2>Delete Container</h2>';
		if ($this->opencloud->delete_container('TestContainer')) {
			echo 'Container Deleted';
		} else {
			echo 'Failed to delete container because: ' . $this->opencloud->error;
		}
	}
}
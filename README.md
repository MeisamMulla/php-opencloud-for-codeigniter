PHP-OpenCloud Library for CodeIgniter
=============================

This library will let you perform certain commands on the new Rackspace php-opencloud API. Currently the following methods are available:

* [Creating a Container](#create)
* [Deleting a Container](#delete)
* [Listing all Containers](#list)
* [Listing all objects in a Container](#list_objects)
* [Adding an Object to a Container](#add_object)
* [Deleting an Object from Container](#delete_object)

## Installation ##
Drop the files/folders in the `/application` folder into your CodeIgniter `/application` folder. You will then need to modify the contents of `/application/config/opencloud.php` to match your Rackspace account.

## Usage ##
You can load the library in the controller you would like to use it in like so:
```php
$this->load->library('opencloud');
```

### Error Handling ###
All methods return a boolean value and the last error message can be grabbed by calling `$this->opencloud->error` ex:
```php
if ($this->opencloud->create_container('MyContainer')) {
	// Container created successfully
} else {
	echo $this->opencloud->error;
}
```

### <a name="create"></a>Creating a Container ###
You can create a container like so:
```php
$this->opencloud->create_container('MyContainer');
```
This will automatically make the container CDN enabled.


### <a name="delete"></a>Deleting a Container ###
A container must be empty before it can be deleted.
```php
$this->opencloud->delete_container('MyContainer');
```

### <a name="list"></a>Listing all Containers ###
```php
$containers = $this->opencloud->list_containers();
```
This will return a multi-dimentional array containing `name`, `count`, `bytes` for each container.

### <a name="list_objects"></a>Listing all Objects in a Container ###
You will need to set the container first by using the method `set_container()`
```php
$this->opencloud->container('MyContainer');
$objects = $this->opencloud->list_objects();
```
This will return a multi-dimentional array containing `name`, `content_type`, `bytes` and `cdn-url` for each object.

### <a name="add_object"></a>Adding objects in a Container ###
You will need to set the container first by using the method `set_container()`
```php
$this->opencloud->container('MyContainer');
$this->opencloud->add_object('text1.txt', file_get_contents('files/text1.txt'), 'text/plain');
$this->opencloud->add_object('text2.txt', file_get_contents('files/text2.txt'), 'text/plain');
$this->opencloud->add_object('text3.txt', file_get_contents('files/text3.txt'), 'text/plain');
```
The first paramater will be the name you will assign the object, the second is the contents of the file, and the third will be the Content-type of the file.

### <a name="delete_object"></a>Deleting Objects from a Container ###
You will need to set the container first by using the method `set_container()`
```php
$this->opencloud->container('MyContainer');
$this->opencloud->delete_object('text1.txt');
$this->opencloud->delete_object('text2.txt');
$this->opencloud->delete_object('text3.txt');
```

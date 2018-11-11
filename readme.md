![Version](https://img.shields.io/packagist/v/johansatge/workflowy-php.svg)

![WorkflowyPHP](logo.png)

An unofficial WorkFlowy API written in PHP.

---

* [Disclaimer](#disclaimer)
* [Installation](#installation)
* [Usage](#usage)
  * [Login API](#login-api)
  * [Lists API](#lists-api)
    * [Get the informations of a list](#get-the-informations-of-a-list)
    * [Edit the informations of a list](#edit-the-informations-of-a-list)
  * [Account API](#account-api)
* [Changelog](#changelog)
* [License](#license)
* [Credits](#credits)

## Disclaimer

The aim of the API is to keep things simple. Please keep in mind that it is an unofficial tool, and it may stop working at any time.

So, I strongly recommend you not to manipulate sensitive data with this API, and be sure to make regular backups of your lists.

## Installation

By using [Composer](https://getcomposer.org/):

```json
{
    "require": {
        "johansatge/workflowy-php": "0.1"
    }
}
```

If you do not use Composer, you can download the source files, install them anywhere on your project, and call the providden autoloader file:

```php
<?php require_once '/your/project/root/path/workflowy-php/src/autoload.php';
```

## Usage

### Login API

Because of the unofficial status of the API, you have to login first, by using your regular credentials, before being able to perform requests on your data.

```php
use WorkFlowyPHP\WorkFlowy;
use WorkFlowyPHP\WorkFlowyException;
try
{
    $session_id = WorkFlowy::login('user@domain.org', 'password');
}
catch (WorkFlowyException $e)
{
    var_dump($e->getMessage());
}
```

The `$session_id` variable will be used later, when performing requests.

You have to use your unencoded password in your code.
So I strongly advise you to store it in a different file, or ask it once to the user, then store the session ID. (But keep in mind that the session does not last forever.)
This is a huge limitation, but for now there is no workaround.

### Lists API

Lists-related stuff is managed with the recursive `WorkFlowySublist` class.

First, you will need to get the main (root) list.

```php
use WorkFlowyPHP\WorkFlowyList;

$list_request = new WorkFlowyList($session_id);
$list = $list_request->getList();
```

Then, you will be able to perform the following operations on the resulting `$list`, or its sublists.

#### Get the informations of a list

| Function | Returns | Description |
| --- | --- | --- |
| `$list->getID();` | `string` | Get the ID of the list |
| `$list->getName();` | `string` | Get the name of the list |
| `$list->getDescription();` | `string` | Get the description of the list |
| `$list->getParent();` | `WorkFlowySublist` | Get the parent of the list |
| `$list->isComplete();` | `boolean` | Get the status of the list |
| `$list->getCompletedTime();` | `int` | Get the completed time of the list (Unix timestamp) |
| `$list->getLastModifiedTime();` | `int` | Get the last modified time of the list (Unix timestamp) |
| `$list->getOPML();` | `string` | Get the list and its sublists as an OPML string |
| `$list->getSublists();` | `array` | Get the sublists of the list |
| `$list->searchSublist('/My sublist name/');` | `WorkFlowySublist` | Returns the first child list matching the given name |
| `$list->searchSublist('/My sublist name/', array('get_all' => true));` | `array` | Returns all children lists matching the given name |

#### Edit the informations of a list

| Function | Parameters | Description |
| --- | --- | --- |
| `$list->setName('My sublist');` | `string` | Sets the list name |
| `$list->setDescription('My sublist description');` | `string` | Sets the list description |
| `$list->setParent($parent_list, 2);` | `WorkFlowySublist`,`int` | Sets the list parent and its position |
| `$list->setComplete(true);` | `boolean` | Sets the list status |
| `$list->createSublist('My sublist name', 'My sublist description', 9);` | `string`,`string`,`int` | Creates a sublist |

The methods below are used to edit data.

Keep in mind that they will send requests to the server, but not update the existing variables.

For instance, if you change the parent of a list and call the getSublists() method on its old parent, the list will still be present in the resulting array.

### Account API

| Function | Returns | Description |
| --- | --- | --- |
| `$account_request = new WorkFlowyAccount($session_id);` | `WorkFlowyAccount` | Gets an account object |
| `$account_request->getUsername();` | `string` | Gets his username |
| `$account_request->getEmail();` | `string` | Gets his email address |
| `$account_request->getTheme();` | `string` | Gets his selected theme |
| `$account_request->getItemsCreatedInMonth();` | `int` | Gets the number of items created during the month |
| `$account_request->getMonthlyQuota();` | `int` | Gets his monthly quota |
| `$account_request->getRegistrationDate('d-m-Y');` | `string` | Gets his registration date<br>Leave the format empty to use the default value ('Y-m-d H:i:s') |
| `$account_request->getRegistrationDate('timestamp');` | `string` | Gets his registration time |

## Changelog

| Version | Date | Notes |
| --- | --- | --- |
| `0.2.1` | 2018-11-11 | Fix `getLastModifiedTime()` and `getCompletedTime()` methods<br>Internal WorkFlowy API started returning timestamps in seconds |
| `0.2.0` | 2018-07-21 | Fix `getItemsCreatedInMmonth()` method naming (renamed to `getItemsCreatedInMonth()`)<br>Update documentation<br>Update sample code |
| `0.1.3` | 2017-02-28 | Add `$list->getCompletedTime()` & `$list->getLastModifiedTime()` methods (#5)<br>Fix OPML encoding (#4) |
| `0.1.2` | 2016-06-26 | Fix `searchSublist`with `get_all` option ([@hirechrismeyers](https://github.com/hirechrismeyers)) |
| `0.1.1` | 2015-08-25 | Fix case of filenames ([@citywill](https://github.com/citywill)) |
| `0.1` | 2015-01-01 | Initial version |

## License

This project is released under the [MIT License](LICENSE).

## Credits

* [WorkFlowy](http://workflowy.com)

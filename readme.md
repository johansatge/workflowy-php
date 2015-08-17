# WorkFlowyPHP

An unofficial WorkFlowy API written in PHP.

---

* [Disclaimer](#disclaimer)
* [Installation](#installation)
* [Usage](#usage)
  * [Login API](#login-api)
  * [Lists API](#lists-api)
    * [Get a list](#get-a-list)
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

If you do not use Composer, you can download the source files, install it anywhere on your project, and call the providden autoloader file:

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

As you can see, you have to use your unencoded password in your code. 
So I strongly advise you to store it in a different file, or ask it once to the user, then store the session ID. (But keep in mind that the session does not last forever.)
This is a huge limitation, but for now there is no workaround.

### Lists API

Lists-related stuff is managed with the recursive `WorkFlowySublist` class.

#### Get a list

Gets the main account list (returns `WorkFlowySublist`)

```php
use WorkFlowyPHP\WorkFlowyList;

$list_request = new WorkFlowyList($session_id);
$list = $list_request->getList();
```

Looks recursively for a sublist (returns `WorkFlowySublist`)

```php
// Returns the first match
$sublist = $list->searchSublist('/My sublist name/');

// Returns all matches as an array
$sublist = $list->searchSublist('/My sublist name/', array('get_all' => true));
```

#### Get the informations of a list

| Function | Returns | Description
| --- | --- | --- |
| `$sublist->getID();` | `string` | Get the ID of the list |
| `$sublist->getName();` | `string` | Get the name of the list |
| `$sublist->getDescription();` | `string` | Get the description of the list |
| `$sublist->getParent();` | `WorkFlowySublist` | Get the parent of the list |
| `$sublist->isComplete();` | `boolean` | Get the status of the list |
| `$sublist->getOPML();` | `string` | Get the list and its sublists as an OPML string |
| `$sublist->getSublists();` | `string` | Get the sublists of the list |

#### Edit the informations of a list

The above methods are used to edit data.

Keep in mind that they will send requests to the server, but not update the existing variables.

For instance, if you change the parent of a list and call the getSublists() method on its old parent, the list will still be present in the resulting array.

| Function | Parameters | Description
| --- | --- | --- |
| `$sublist->setName('My sublist');` | `string` | Sets the list name |
| `$sublist->setDescription('My sublist description');` | `string` | Sets the list description |
| `$sublist->setParent($list, 2);` | `WorkFlowySublist`,`int` | Sets the list parent and position |
| `$sublist->setComplete(true);` | `boolean` | Sets the list status |
| `$sublist->createSublist('My sublist name', 'My sublist description', 9);` | `string`,`string`,`int` | Creates a sublist |

### Account API

| Function | Returns | Description
| --- | --- | --- |
| `$account_request = new WorkFlowyAccount($session_id);` | `WorkFlowyAccount` | Gets an account object |
| `$account_request->getUsername();` | `string` | Gets his username |
| `$account_request->getEmail();` | `string` | Gets his email address |
| `$account_request->getEmail();` | `string` | Gets his registration date |
| `$account_request->getTheme();` | `string` | Gets his selected theme |
| `$account_request->getItemsCreatedInMmonth();` | `int` | Gets the number of items created during the month |
| `$account_request->getMonthlyQuota();` | `int` | Gets his monthly quota |
| `$account_request->getRegistrationDate('d-m-Y');` | `string` | Gets his registration date<br>Leave the format empty to use the default value ('Y-m-d H:i:s')<br> Give the 'timestamp' value to get the timestamp instead of a date. |

## Changelog

| Version | Date | Notes |
| --- | --- | --- |
| `0.1` | January 1st, 2015 | Initial version |

## License

This project is released under the [MIT License](LICENSE).

## Credits

* [WorkFlowy] (http://workflowy.com)

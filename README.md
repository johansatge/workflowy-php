![WorkflowyPHP](logo.png)

An unofficial WorkFlowy client written in PHP.

Only works under PHP 7.4, but *not* compatible with PHP 8.0 because this library uses CURL heavily in the current state.

![CI](https://github.com/johansatge/workflowy-php/workflows/CI/badge.svg)

[![Latest Stable Version](https://poser.pugx.org/johansatge/workflowy-php/v/stable.png)](https://packagist.org/packages/johansatge/workflowy-php) [![Total Downloads](https://poser.pugx.org/johansatge/workflowy-php/downloads.png)](https://packagist.org/packages/johansatge/workflowy-php) [![Latest Unstable Version](https://poser.pugx.org/johansatge/workflowy-php/v/unstable.png)](https://packagist.org/packages/johansatge/workflowy-php) [![License](https://poser.pugx.org/johansatge/workflowy-php/license.png)](https://packagist.org/packages/johansatge/workflowy-php)

---

* [Disclaimer](#disclaimer)
* [Installation](#installation)
* [Usage](#usage)
* [Changelog](#changelog)
* [License](#license)
* [Credits](#credits)

## Disclaimer

The aim of the API is to keep things simple. Please keep in mind that it's an unofficial tool, and it may stop working 
at any time.

It's strongly recommended you not to manipulate sensitive data with this API, and be sure to make regular backups of 
your lists.

## Installation

By using [Composer](https://getcomposer.org/):

```bash
composer require johansatge/workflowy-php
```

To use it in your script require the autoloader from composer, and you are all set:

```php
<?php

require_once 'vendor/autoload.php';
```

## Usage

Because of the unofficial status of the API, you have to login first, by using your regular credentials, before being 
able to perform requests on your data. There are no API keys.

```php
use JohanSatge\WorkFlowy\WorkFlowyClient;
use JohanSatge\WorkFlowy\WorkFlowyException;

try {
    $workflowy = new WorkFlowyClient();
    $sessionId = $workflowy->login('user@domain.org', 'password');
    // Internally client also stores the session id for this run of the script.
    // You could already use the client from here, no need to make a new one.
} catch (WorkFlowyException $e) {
    var_dump($e->getMessage());
}
```

The `$sessionId` is newly created on each call of login. Once it's created it's valid for 6 months from the time of when 
it was created. Use of that session id does not refresh those 6 months.

It's good practice storing that session id and reuse it. I would recommend a cache that has a slightly smaller than the 
6 month lifetime, and if it's empty, do the login. That should do the trick nicely.

When calling the login function, the client also stores the current session id internally, so you don't need to recreate 
the client again and can use it from there out directly. There is no great downside to call login on each call, but it's 
nicer to WorkFlowy's server if you reuse session id's. 

*You also have to pass in your password in clear, so be mindful where you put that.*

If you have the session id stored, you can then just pass it to the clients constructor and from there use the client.

```php
use JohanSatge\WorkFlowy\WorkFlowyClient;
use JohanSatge\WorkFlowy\WorkFlowyException;

// $sessionId comes from somewhere here

try {
    $workflowy = new WorkFlowyClient($sessionId);
    // No login needed, start using the client right away
    // If it fails, the session might have timed out

    // This is the first call you probably want when talking to the client, to get and parse the root list.
    $document = $workflowy->getDocument();
    
    // The document contains the root list and all children
    // This will print the first level of your bullet points, printing name, description and ids of those items.
    echo '<ul>';
    foreach ($document->getRoot()->getChildren() as $item) {
        echo '<li>';
        echo $item->getName().' | '.$item->getDescription().' ('.$item->getId().')';
        echo '</li>';
    }
    echo '</ul>';
} catch (WorkFlowyException $e) {
    var_dump($e->getMessage());
}
```

When working with lists, the most secure way is to use their ids, as those won't change even if you alter the name. 
Their ids are UUID's. Currently they are total random, but the library uses UUID v4 and the official client is said to
transition to v4 as well. Only the root list has no UUID and is faked as "root" by this library.
The root id is also available as constant under \JohanSatge\WorkFlowy\WorkFlowyClient::ROOT_ITEM_ID

Say you want to add items to a "Tasks" list, you could accomplish it like this. Also this is one of the few workflows
where you will not need to load the document first.

```php
use JohanSatge\WorkFlowy\WorkFlowyClient;
use JohanSatge\WorkFlowy\WorkFlowyException;

// $sessionId comes from somewhere here, or use login
// $tasksId is the id of your tasks list which you found out already using the other api calls

try {
    $workflowy = new WorkFlowyClient($sessionId);
    // For adding, you only need an id, not even load the list first.
    // You can also add a description and a priority, but those are optional, even the name itself is optional.
    $workflowy->createItem($tasksId, 'New task to add to the tasks list');
} catch (WorkFlowyException $e) {
    var_dump($e->getMessage());
}
```

For updating a list, first fetch it, then change it, then send it to the client for updating.

```php
use JohanSatge\WorkFlowy\WorkFlowyClient;
use JohanSatge\WorkFlowy\WorkFlowyException;

// $sessionId comes from somewhere here
// $itemId The id of the item we want to change

try {
    $workflowy = new WorkFlowyClient($sessionId);
    $document = $workflowy->getDocument();
    // For the below to work, you need to have called getDocument once, or it doesn't find any items
    $item = $document->getItem($itemId);
    $item->setName('A new name');
    $item->setDescription('A new description');
    // No changes are saved until you call this, and only now will the server be called
    $workflowy->update($item);
    
    // To remove it you can also call delete on the item, which will also remove its children
    //$workflowy->delete($item);
} catch (WorkFlowyException $e) {
    var_dump($e->getMessage());
}
```

Lastly, you can move an item from one place to another. For that you need to load both items first, then use the move 
function. This is to ensure that both exist before you move it.

```php
use JohanSatge\WorkFlowy\WorkFlowyClient;
use JohanSatge\WorkFlowy\WorkFlowyException;

// $sessionId comes from somewhere here
// $subjectId The id of the item we want to move
// $targetId The id of the item where we want to move the subject item to

try {
    $workflowy = new WorkFlowyClient($sessionId);
    $document = $workflowy->getDocument();
    $subject = $document->getItem($subjectId);
    $target = $document->getItem($targetId);
    // Third parameter is priority which says where you want to put it in order.
    $workflowy->move($subject, $target, 9);
} catch (WorkFlowyException $e) {
    var_dump($e->getMessage());
}
```

A word on priority: The priority is used to sort items within lists. The higher the number you send, the lower the item
will be on the list. If you send items with identical priorities then the server will do some magic to sort it out, so 
your result might not be very predictable.

You can also use this to create backups in OPML format. Once you have the string, save it where you want to keep your 
backup.

```php
use JohanSatge\WorkFlowy\WorkFlowyClient;
use JohanSatge\WorkFlowy\WorkFlowyException;
use JohanSatge\WorkFlowy\WorkFlowyExportOPML;

// $sessionId comes from somewhere here

try {
    $workflowy = new WorkFlowyClient($sessionId);
    $document = $workflowy->getDocument();
    $opml = WorkFlowyExportOPML::exportDocument($document);
    // $opml is now a string containing the XML dump of the entire document
} catch (WorkFlowyException $e) {
    var_dump($e->getMessage());
}
```

This will create a backup of all items and is formatted the same way as the OPML export is on the WorkFlowy page.

## Not implemented functions

* Dealing with mirrors. A new feature introduced on 4. January 2021, this library doesn't really know what to do with
  mirror items at the moment. During parsing it will just create an item that has an id and nothing else. So there will
  be no errors, but also no additional functionality.

## Changelog

| Version | Date | Notes |
| --- | --- | --- |
| `1.0.0` | ????-??-?? | Complete overhaul of the API, breaking pretty much all the past things. Check above usage for how it works now. |
| `0.2.3` | 2019-05-17 | Fix authentication process (#10) |
| `0.2.2` | 2019-02-13 | Fix authentication process (#8) |
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

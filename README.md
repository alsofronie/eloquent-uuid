# eloquent-uuid
An Eloquent UUID Trait to use with Laravel 5.1 or Laravel 5.2.13+.

[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/hyperium/hyper/master/LICENSE)

It **should** work with Laravel 5.0 also, but it's untested.

The trait overwrites the static `boot` method and listens to the `creating`
event. It generates a UUID (strips the dashes) and stores it in the primary
key attribute. Thus, you'll need a `CHAR(32)` primary key for your model
(see migrations below).

## Installation

	composer require alsofronie/eloquent-uuid:dev-master

## Use

In order to make it faster, you have the option to use one of three traits:

 - `UuidModelTrait` - the key must be `CHAR(36)` and contains the dashes
 - `Uuid32ModelTrait` - the key must be `CHAR(32)`, the dashes are striped
 - `UuidBinaryModelTrait` - the key is `BINARY(16)`.

#### Using `UuidModelTrait`

In order to use this trait, your **schema** must be something like:

```
<?php
	// ...
	Schema::create('users', function (Blueprint $table) {
		$table->uuid('id');	// this will create a CHAR(36) field
		// or
		// $table->char('id', 36);
		$table->string('username', 32);
		$table->string('password', 50);
		// ...
		$table->primary('id');
	});
```

#### Using `Uuid32ModelTrait`

For this type, just use `CHAR(32)` in your schema (this is identical to the first one, but with stripped dashes).

```
<?php
	// ...
	Schema::create('users', function (Blueprint $table) {
		$table->char('id', 32);
		// ...
		$table->string('username', 32);
		$table->string('password', 50);

		$table->primary('id');
	});
```

#### Using `UuidBinaryModelTrait`

This stores the key as binary. The default Laravel `Blueprint` curretly 
[does not currently support binary fields with specified length](https://github.com/laravel/framework/issues/1606),
and (at least in MySQL) you cannot create an index (including primary key) on a `BINARY` field without length.

So, the schema definition should be something like this (please double check if you're not using MySQL):

```
<?php

	// ...
	Schema::create('users', function (Blueprint $table) {
		$table->string('username', 32);
		$table->string('password', 50);
	});

	DB::statement('ALTER TABLE `usersb` ADD `id` BINARY(16); ALTER TABLE `usersb` ADD PRIMARY KEY (`id`);')
?>
```

There are two additional notes for this particular trait.

> Note 1. In order to get a string representation of your uuid, simple call `$model->id_string` and you'll get it.

> Note 2. You can use `User::find($uuid)` with both the binary version or the string (bin2hex) version.

#### In your models

In order to use this in your models, just put `use Uuid[32|Binary]ModelTrait;`:

```
<?php

namespace App;
use Alsofronie\Uuid;

class User extends Eloquent
{
	use Uuid[32|Binary]ModelTrait;
}
```

## Running tests

To run the tests, just run `composer install` and `./vendor/bin/phpunit`.


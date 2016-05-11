Laravel Likeable Plugin
============

This is a fork from [rtconner/laravel-likeable](https://github.com/rtconner/laravel-likeable) by Robert Conner - [smartersoftware.net](http://smartersoftware.net) , thank you for smart software :)

We have added option to have dislike as separate field (counter) or any other countable social reaction (follow,hate,dislike,like ..)

# TODO:
   Inspired by other [laravel-likeable](https://github.com/DraperStudio/Laravel-Likeable) packages, will add options to tracks reaction with timestamp, which gives us more deep metrics etc..


[![Build Status](https://travis-ci.org/nanosolutions/laravel-likeable.svg?branch=master)](https://travis-ci.org/nanosolutions/laravel-likeable)
[![Latest Stable Version](https://poser.pugx.org/nanosolutions/laravel-likeable/v/stable.svg)](https://packagist.org/packages/nanosolutions/laravel-likeable)
[![License](https://poser.pugx.org/nanosolutions/laravel-likeable/license.svg)](https://packagist.org/packages/nanosolutions/laravel-likeable)

Trait for Laravel Eloquent models to allow easy implementation of a "like" or "favorite" or "remember" feature.

[Laravel 5 Documentation](https://github.com/nanosolutions/laravel-likeable/tree/laravel-5)  
[Laravel 4 Documentation](https://github.com/nanosolutions/laravel-likeable/tree/laravel-4)

#### Composer Install (for Laravel 5+)

	composer require nanosolutions/laravel-likeable "~1.3"

#### Install and then run the migrations

```php
'providers' => [
	\Nano\Likeable\LikeableServiceProvider::class,
],
```

```bash
php artisan vendor:publish --provider="Nano\Likeable\LikeableServiceProvider" --tag=migrations
php artisan migrate
```

#### Setup your models

```php
use Nano\Likeable\Likeable;

class Article extends Model {
	use Likeable;
}
```

#### Sample Usage

```php
$article->like(); // like the article for current user
$article->like($myUserId); // pass in your own user id
$article->like(0); // just add likes to the count, and don't track by user

$article->unlike(); // remove like from the article
$article->unlike($myUserId); // pass in your own user id
$article->unlike(0); // remove likes from the count -- does not check for user

// Dislike (new metric)
$article->like('dislike'); // alias -> $article->dislike();
$article->unlike('dislike'); // alias -> $article->dislike($myUserId);

// Or anything you want (no alias)
$article->like('follow');
$article->unlike('follow');


$article->likeCount; // get count of likes
$article->likeCount(dislike''); // get count of dislikes

$article->likes; // Iterable Illuminate\Database\Eloquent\Collection of existing likes
$article->likes('dislike'); // Iterable Illuminate\Database\Eloquent\Collection of existing disklikes
$article->liked(); // check if currently logged in user liked the article
$article->liked($myUserId);

Article::whereLiked($myUserId) // find only articles where user liked them
	->with('likeCounter') // highly suggested to allow eager load
	->get();
```

#### Credits

 - Robert Conner - http://smartersoftware.net

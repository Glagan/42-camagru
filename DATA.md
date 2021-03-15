
# Data

## User

* id: ``int``
* username: ``string``
* mail: ``string``
* password: : ``string``
* verified: ``boolean`` mail is verified
* theme: ``'light' | 'dark'``
* receiveComments: ``boolean`` Receive a mail when a comment is posted in one of your images

## Image

* id: ``int``
* user: ``User.id``
* path: ``string``
* private: ``boolean``

## Like

* id: ``int``
* image: ``Image.id``
* at: ``Date``

## Comment

* id: ``int``
* image: ``Image.id``
* user: ``User.id``
* at: ``Date``


## Commands

```SQL
CREATE DATABASE `camagru` COLLATE 'utf8mb4_unicode_ci';
```

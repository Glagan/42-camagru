
# Data

## User

* id: ``int``
* username: ``string``
* mail: ``string``
* password: : ``string``
* verified: ``boolean`` mail is verified
* receiveComments: ``boolean`` Receive a mail when a comment is posted in one of your images

## Image

* id: ``int``
* user: ``User.id``
* name: ``string``
* at: ``Date``

## Decoration

* id: ``int``
* name: ``string``
* category: ``still | animated``
* public: ``boolean``

## Like

* id: ``int``
* user: ``User.id``
* image: ``Image.id``
* at: ``Date``

## Comment

* id: ``int``
* image: ``Image.id``
* user: ``User.id``
* message: ``string``
* at: ``Date``

## Session

* id: ``int``
* user: ``User.id``
* session: ``string`` Unique PHP session
* issued: ``Date``

## Token

* id: ``int``
* user: ``User.id``
* token: ``string`` 50 characters long unique token
* scope: ``verification | password``
* issued: ``Date``

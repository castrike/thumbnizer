Thumbnizer
========================

Welcome to the Thumbnizer. Thumbnizer is an application based off the [Symfony Standard Edition framework][1]. 
The purpose of this application is to crop images on the fly behind a content delivery network.

1) Installing 
----------------------------------

Clone the repo and update the dependencies.

### Use Composer (*recommended*)

Thumbnizer uses [Composer][2], just like symfony.

If you don't have Composer yet, download it and follow the instructions from http://getcomposer.org/.
  
    curl -s http://getcomposer.org/installer | php


2) Checking your System Configuration
-------------------------------------

Since we are using Symfony, Execute the `check.php` script from the command line:

    php app/check.php

Setup Apache to allow encoded slashes

    AllowEncodedSlashes On

  * This can be added in under your virtual host settings.

3) Using the Application
--------------------------------
    [Your CDN]/thumbnail/[width]/[height]/[public image url]


4) Coming Soon
--------------------------------
- Ability to use a configuration file to parse urls allowed to use this tool.
- More functions. ( Crop image by %, add effects, rotate image )
- Work as an API (Using API keys and stufff)


Enjoy!

CREDITS:
Thanks to marchibbins for the Filters and Effects.
https://github.com/marchibbins/GD-Filter-testing

[1]:  http://symfony.com/doc/2.4/quick_tour/the_big_picture.html
[2]:  http://getcomposer.org/



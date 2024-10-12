CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

This module integrates the beautiful carousel slider Owl Carousel through
blocks integrations.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/owlcarousel2

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/owlcarousel2


REQUIREMENTS
------------

This module requires the following outside of Drupal core:

 * OwlCarousel2 - https://github.com/OwlCarousel2/OwlCarousel2/archive/2.3.4.zip
   library

Effects provided by the

 * Animate.css - https://daneden.github.io/animate.css/


INSTALLATION
------------

 * Install the [insert name] module as you would normally install a contributed
    Drupal module. Visit https://www.drupal.org/node/1897420 for further
    information.

To install OwlCarousel 2 on Drupal, proceed as usual and then download and
copy the `dist` folder of the OwlCarousel2 v2.3.4 javascript library on
`\libraries\OwlCarousel2\dist`.


CONFIGURATION
-------------

After installation, a new tab `Carousel` will be present on `Content` admin
 menu.

    1. On content menu, select the `Carousel` tab, `Add OwlCarousel2` to create
       a new carousel.
    2. You can fill the carousel Name and configuration on the form or skip it
       to do it later.
    3. Click on `Save`
    4. A new fieldset will be presented. Include the carousel items there.
       - Items can be Image, Youtube or Vimeo urls or Views results.
       - For images, it can be a static image or an image linked to a content
        entity.
       The view mode will define which fields of the content will be presented.
       - For view results, the view result will be considered, not the fields.
       The content presentation will be based on the view mode selected.
    5. After you've included all items, click on save and a preview will be
       presented.
    6. Once you have created your carousel, go to admin/structure/block and
       place a new block wherever you want.
    7. On the block pop-up, select `Carousel Block` and in `Carousel` field
       choose the carousel you've created.
    8. Finish the block configuration as usual.

PS: You can use Display Suite or Page Manager to configure the OwlCarousel2
blocks as well.


MAINTAINERS
-----------

 * George Anderson (geoanders) - https://www.drupal.org/u/geoanders
 * Yuri Seki (yuriseki) - https://www.drupal.org/user/1523064

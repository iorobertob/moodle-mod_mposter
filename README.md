Moodle Media Poster activity module [INTERMUSIC]
=============================

Important!!
----------
- This **Media Poster** module is  based on the original *Poster* plugin, which is customised to work together with other Intermusic project plugins and therefore does not follow the releaes of the original plugin.
- When installing the name of this module's folder should be named **mposter**.
- Metadata functionality works only with an enabled ResourceSpace respository plugin. I will still work withoug it, but it wont fetch/autofill metadata. 

Differences with original Media Poster:
----------
- This version has settings to add **metadata** to this activity, a ['Media Poster List' activity plugin](https://github.com/iorobertob/moodle-mod_inter) can later read this data and create a table with it. 
- This module can retrieve such metadata from a **collection** in a ResourceSpace asset management system(AMS) instance. This is linked to moodle using the [ResourceSpace repository plugin](https://github.com/iorobertob/moodle-repository_resourcespace.git) which has to be installed for this feature to work. 


How does this module get its metadata
----------
- The module has settings for  7 metadata field titles and values, that can be filled manually or sought for in the ResourceSapce instance. 
- The module also has the option to upload a metadata file. The metadata file contains in its name, the ID of a **collection** in ResourceSpace, and performs an API call to retrieve a json list with information about files of that collection. 
- From the info of the collection, the script looks for the metadata fields that match the titles set in this module's configuration or settings. If they are found, they are used to fill the values in this module's info. If the metadata field titles in the config are left empty, the mposter will use the default (configurable) values:  	
- "Composer";
- "Title";
- "Title - English";
- "Surtitle";
- "List";
- "1st line";
- "Language";


Intermusic Project
----------
This module was created as part of the [Intermusic Project](https://intermusic.lmta.lt). Its functionality reflects the needs of the project, but it is also intended to work away from that context and use the metadata features more generally. 


Installation
----------
Standard moodle plugin process, though the admin pages or by copying this repository in a folder named **mposter** inside **mod/**


Moodle Poster activity module [ORIGINAL]
=============================

[![Build status](https://travis-ci.org/mudrd8mz/moodle-mod_mposter.svg?branch=master)](https://travis-ci.org/mudrd8mz/moodle-mod_mposter)

Poster is a resource activity module for Moodle. It allows teachers to create a page for their students. What makes this module
unique is that the contents of the mposter page is composed of Moodle blocks (such as HTML blocks, Calendar block, Latest news block
etc.). It provides yet another place within the course where blocks can be put without polluting the course front page.



Motivation
----------

There are many useful blocks available for Moodle. Typically, they can be only added to the sides of the Moodle pages, or to the
user's dashboard page (also known as My home page). Sometimes, you may want to keep your course main page quite clean, not cluttered
with blocks on both sides. In such case, you can put useful blocks into a separate Poster page.

The overall concept is somewhat similar to how pages are created in Mahara - but it is typically the teacher in Moodle who creates
the Poster for students to view.

Usage
-----

To use the module, you should understand how Moodle sticky blocks work. See [Block
settings](https://docs.moodle.org/en/Block_settings) page for more details.

1. Add the module instance into the course.
2. Keep the editing mode on.
3. Add the Moodle blocks you want to display on the mposter.
4. Click the icon to configure the block. Set the block instance so that it is displayed in the context of the
   mposter, on page type _Poster module main page_ (`mod-mposter-view`), inside the region `mod_mposter-pre` or `mod_mposter-post`.
5. Alternatively, use the drag and drop feature to move the block to the regions at the mposter content area.
6. Note that some blocks must be first added to the course main page first, configured to be displayed at any page and then
   configured again to be displayed at the mposter main page only (this is how block positioning works in Moodle generally).

The mposter can be used as for example:

* Course wall/dashboard (contact teachers, detailed outline of the course, latest news, comments, ...).
* Project dashboard (project goals, calendar, comments, people, ...)
* Research report (goals, methods, results, comments, ...)

Implementation
--------------

The Poster module uses not so well known feature of the Moodle blocks architecture. In almost all cases, it is the theme that
defines regions where plugins can be added to. However in special cases, such as this one, any Moodle plugin can define its custom
block regions.  Within the context of the Poster module instance, when displaying its view.php page, two extra block regions are
defined - `mod_mposter-pre` and `mod_mposter-post`. The Poster module itself is just a tiny wrapper for displaying these two regions
as its content. Simple and clever.

The module natively supports responsive layout in bootstrap based themes (both 2.x and 3.x versions).

* The original Poster module has been written by David Mudrák.
* The current plugin is a modified version of David's as part of Intermusic Project. 




            

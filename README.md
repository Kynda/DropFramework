# DropFramwork

## Yet Another PHP Microframework

For all intensive purposes, you can consider this an abandoned project and I
would not recommend anyone actually use this in production.

A few years ago when Code Igniter was still quite a hot thing and a lot of 
servers were still running PHP 5.2, e.g. the "dark ages" before we got all the 
nice things that came along in PHP 5.3 it seemed to be quite the fashion for 
everyone to try their hand at writing their own framework.

This was my go at it.

You will find a lot of similarities with Code Igniter (since that is the 
framework I worked with at the time) and you might also find a lot of classes 
that look like they came straight out of [PHP Objects, Patterns and 
Practice](http://www.amazon.com/Objects-Patterns-Practice-Experts-Source/dp/143022925X) 
since that was my bible.

I wanted to do a few things in writing the DropFramework:

1. I wanted to better understand the MVC pattern, the choices being made and how 
CI works.
1. I wanted a framework that was small enough that I could read and understand 
every class in it.
1. I wanted a framework with a very small footprint that worked by transforming 
HTTP requests into request objects / command objects. This allowed me to fire up 
multiple instances of the framework per HTTP request with the master application 
generating it's own request objects that it would feed into it's child  
instances  and then construct a document out of the application responses from 
the children.
1. I did not like at the time, and still do not like the major design patterns 
of a lot of ORM solutions which tend to treat the database as the authoritative 
model of the data. I rather turn this convention upside down: treat the database 
as just another form of user input. The model can then be constructed from any 
form of input -- the database, an HTTP post, a file. The PHP object is then the 
authoritative source for how the data structure relates with other data. Any 
data coming into the model passes through a validation layer that translates it 
(or rejects it if it invalid).

Whether or not I succeeded at this items? I don't think I would really know.

## Version 0.4.0

The version of the framework that had been sitting on my hard disk for some time 
was 0.3.0. In deciding to release it I have done two major things:

1. I created a simple example of the framework working. The 
[code](https://github.com/Kynda/DropFramework-Example) for this example is also 
up on github and a [live version](http://dropframework-example.kynda.net) as 
well.
1. I namespaced the entire framework and brought it into PSR-4 compliance 
allowing for installation via Composer and the use of the Composer autoloader.  
This defeats a lot of the purpose of the PHP 5.2 era frameworks which devoted a 
lot of their resources to locating and managing the loading of assets. This, of 
course, makes this no longer a PHP 5.2 compatible framework and probably even 
makes a lot of the framework look rather silly.

wp-default-author
=================

WordPress - Default Author 

###Description

This plugin allows you to define the default author, both globally and per user.

The global settings are under `options-writing.php`:

<img src="https://raw.github.com/birgire/wp-default-author/master/screenshot-2.png" />

The settings for each user is under `profile.php`:

<img src="https://raw.github.com/birgire/wp-default-author/master/screenshot-1.png" />

Note that the user settings will overwrite global settings.

The user can always change the author of each post later on:

<img src="https://raw.github.com/birgire/wp-default-author/master/screenshot-3.png" />

###Detailed instructions

This plugin changes who is displayed as the publisher of a blog article. To change this for a single user you have to be logged in as Editor or with higher priveleges. You go to the users profile page and there will be a section there called Default Author Settings, there will be a dropdown list of all users and you pick the user that you want to be displayed as the author of the current users posts. 

So if you want John to be the displayed publisher of Smith, then you go to edit Smith (under User->Smith) and pick John from the dropdown menu in the Default Author Settings section. Save your settings and then future posts made by Smith should appear to be made by John.

To globally change the default author and have all posts be published by John, you go to the Settings->Writing menu and there should be (if you are logged in as Administrator that is) an option called Global default author and a dropdown menu of users after that. Select one of the users and all users who publish on the blog will appear as John.

The Default Author Setting does take precedence over the Global default author, so if you have set Maggie as the default author for Vince, and then set John as Global default author it doesn't change the Default Author Setting for Vince, his posts will still appear as they were from Maggie (you will have to go to Vince's user page and change it for him if you want to have him publish as John also).

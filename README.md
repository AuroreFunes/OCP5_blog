# OCP5_blog
OpenClassrooms - Project 5 - Blog from scratch

This is a simple personal blog, developed from scratch, with basic functionality. It allows you to :
* show your CV
* create blog posts
* users can register and log in
* when users are registered and logged in, they can post comments for each blog post
* all comments must be validated by an administrator to be visible on the site
* a contact form allows you to send an email to an email address known only to you

User management is not included, you will have to use the database directly to give the role of administrator, change an email address or ban a user.


## Prerequisites

You must have PHP 7.x and a MySQL database that you can manage freely. You must also have Composer installed and have a terminal to use the command lines.

You must also have an SMTP server to send emails from the contact form, and an email address to receive your emails.


## Dependencies

The tools used for this project are :
* phpdotenv (4.2): it allows the use of a ".env" file to define the variables that will be useful for the whole project
* twig (1.42) : it is used for the construction of the views, it allows to secure the display in an automatic way
* phpmailer (6.5) : it is used to send e-mails

The pages are built from the "Clean Blog" theme proposed by "Start Bootstrap", which uses Bootstrap version 5.1.13.


## Installation procedure

* You must copy all the files in a directory that will contain your project.
* Feel free to go to the "public\assets\file" directory and replace the existing file with your own CV. Be sure to name it "my-cv.pdf" so that it can be displayed on your site.
* Similarly, replace the photo in "public\assets\img" with your own photo, keeping the name "photo.jpg".
* Open the file "index.html.twig" which is in "view/pages" to replace the links to your own social networks. You can remove or add them by copying one of the <li> blocks like the one starting on line 15.
* At the command prompt, go to the root directory of the project and use the "compose-install" command to install the dependencies.
* Rename the ".env.example" file to ".env" and fill in the variables with your own parameters:
`* HOST: is the location of your database
`* DB_NAME: is the name of your database. If you haven't made any changes, it should be "ocp5_blog".
`* DB_USER: is the name of the user who is allowed to access your database. It depends on the configuration of your environment.
`* DB_PASSWORD: this is the password of the user who is allowed to access your database. It depends on the configuration of your environment.
`* SMTP_HOST: this is the address of your SMTP server
`* SMTP_PORT: this is the port used by your SMTP server. By default this should be 587
`* SMTP_SECURE: This is the type of security used by your SMTP server
`* SMTP_MAIL: this is the email address to which messages from your contact form will be sent
`* SMTP_PWD: this is the password used by your SMTP server
`* SMTP_FROM: This is what you will see in the "sender name" when you receive an email from your site.
* Use the SQL file to create the database and tables.
* An "Admin" user was automatically created when you created the database. The username and email address must be changed as desired directly in the database as the functionality to change them from the site has not yet been implemented.

You are now ready to use your site! The password to access your Admin account is "123Admin!" Remember to change it immediately after installing your site.

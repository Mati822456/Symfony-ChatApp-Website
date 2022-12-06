# Symfony-ChatApp-Website
Chat application build on Symfony with simple features.

![main](https://user-images.githubusercontent.com/103435077/201998792-58048ce5-9840-40fb-be3c-8a59d432dbb9.png)

## Table of Contents
* [General Info](#general-info)
* [Technologies](#technologies)
* [Setup](#setup)
* [Incoming](#incoming)
* [API](#api)
* [Acknowledgements](#acknowledgements)
* [Contact](#contact)

## General Info
This website was built with [PHP](https://www.php.net/), MySQL, [Symfony](https://symfony.com/doc/current/setup.html) and JQuery. In this project you can send invitations, accept and send a messages to friends. Futhermore you can receive notifications. As a mod or admin you have access to admin panel where you can manage users, invitations, friends, notifications and clear messages. The website is responsive.

## Technologies
* Symfony 6.1.4
* Twig 3.4.2
* PHP 8.1.7
* MySQL 8.0.29
* HTML 5
* CSS 3
* JavaScript
* JQuery 3.5.1
* SweetAlert 2
* FontAwesome 6.1.2
* Boxicons 2.1.4

## Setup
To run this project you will need to install Symfony, PHP, [Composer](https://getcomposer.org/download/), [NPM](https://www.npmjs.com/package/npm), and MySQL on your local machine.

If you have everything you can run these commands:

```
# Clone this respository
> git clone https://github.com/Mati822456/Symfony-ChatApp-Website.git

# Go into the respository
> cd Symfony-ChatApp-Website

# Install dependencies from lock file
> composer install

# Install packages from package.json
> npm install

# Compile assets 
> npm run dev

```

`In .env file change db_user, db_password, db_name`

```
# Create database
> symfony console doctrine:database:create

# Load migrations
> symfony console doctrine:migrations:migrate

# Create admin, mod and user
> symfony console doctrine:fixtures:load

# Start server 
> symfony server:start

# Access using
http://localhost:8000

```

Now you can login using created accounts:
```
Role: Admin
Email: admin@db.com
Password: Admin1234

Role: Mod
Email: mod@db.com
Password: Mod1234

Role: User
Email: user@db.com
Password: User1234
```

![admin](https://user-images.githubusercontent.com/103435077/201997684-e4b77375-7968-44f6-93b4-451ec8ffd017.png)
![admin_users](https://user-images.githubusercontent.com/103435077/201997732-21b8b6ab-82f9-4c09-a539-46b11db979e5.png)

## Incoming
* Groups
* Choice of color scheme for message windows
* Ability to send photos, attachments
* Improving the appearance of the friends page
* And much more, probably...

## API
### Public
<details>
  <summary>Users</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/users/{uuid} | GET |
  
</details>

<details>
  <summary>Invitations</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/invitations | GET |
  | /api/v1/invitations/{uuid} | GET |
  | /api/v1/invitations/{uuid} | POST |
  | /api/v1/invitations/{uuid} | DELETE |
  
</details>

<details>
  <summary>Friends</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/friends | GET |
  | /api/v1/friends/{uuid} | GET |
  | /api/v1/friends?query= | GET |
  | /api/v1/friends/{uuid} | POST |
  | /api/v1/friends/{uuid} | DELETE |
  
</details>

<details>
  <summary>Notifications</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/notifications | GET |
  | /api/v1/notifications/{id} | DELETE |
  
</details>

<details>
  <summary>Messages</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/messages/{uuid} | GET |
  | /api/v1/messages/{uuid} | POST |
  | /api/v1/messages/{uuid} | DELETE |
  
</details>

<details>
  <summary>Theme</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/theme | PATCH |
  
</details>

<details>
  <summary>Scheme</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/scheme/{uuid} | GET |
  | /api/v1/scheme/{uuid}/{scheme_id} | PATCH |
  
</details>

### Admin

<details>
  <summary>Users</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/admin/users?query=&role=&limit=20 | GET |
  | /api/v1/admin/users/{id} | GET |
  | /api/v1/admin/users/{id} | PUT |
  | /api/v1/admin/users/{id} | DELETE |
  
</details>

<details>
  <summary>Invitations</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/admin/invitations?from=&to=&limit=20 | GET |
  | /api/v1/admin/invitations/{id}?limit=20 | GET |
  | /api/v1/admin/invitations/{id} | PUT |
  | /api/v1/admin/invitations/{id} | DELETE |
  
</details>

<details>
  <summary>Friends</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/admin/friends?user=&friend=&limit=20 | GET |
  | /api/v1/admin/friends/{id}?limit=20 | GET |
  | /api/v1/admin/friends/{id} | PUT |
  | /api/v1/admin/friends/{id} | DELETE |
  
</details>

<details>
  <summary>Notifications</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/admin/notifications?user=&limit=20 | GET |
  | /api/v1/admin/notifications/{id}?limit=20 | GET |
  | /api/v1/admin/notifications/{id} | PUT |
  | /api/v1/admin/notifications/{id} | DELETE |
  
</details>

<details>
  <summary>Messages</summary>
  
  | ENDPOINT | METHOD |
  | -------- | ------ |
  | /api/v1/admin/messages?from=&to=&limit=20 | GET |
  | /api/v1/admin/messages?from=&to= | DELETE |
  
</details>

## Acknowledgements
Thanks to:</br>
<a href="https://www.flaticon.com/free-icons/question" title="question icons">Question icons created by dmitri13 - Flaticon</a></br>
<a href="https://www.flaticon.com/free-icons/user" title="user icons">User icons created by kmg design - Flaticon</a></br>
<a href="https://www.flaticon.com/free-icons/user" title="user icons">User icons created by Freepik - Flaticon</a></br>
SIMON LURWER for Gradient Banner Cards https://dribbble.com/shots/14101951-Banners</br>
Matt Smith for Responsive Table https://codepen.io/AllThingsSmitty</br>
Inspired by https://www.codinglabweb.com, login, register and navigation bar

## Contact
Feel free to contact me via email mateusz.zaborski1@gmail.com. :D
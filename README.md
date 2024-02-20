# LemmyAutomod
LemmyAutomod is a tool for Lemmy that allows instance admins to set rules that will take action in certain scenarios. The key actions currently supported are:
- Automatically approve registration applications when the answer provided matches a specific response.
- Set regular expressions to identify email addresses, usernames, posts, or comments that should cause the user to be instantly banned, or reported for manual review.
- Optionally remove all posts and comments where a user is auto-banned (set per rule)
- Mark certain users as "trusted", where if that user reports content, LemmyAutomod will automatically remove the content and resolve the report.
- Ban a user if they post an image that is the same or similar to an image hash that you generate from an image URL.

Other features:
- Message selected users on Lemmy when an action has been taken
- Post to Matrix or Slack chat when an action has been taken
- Notify when a new user has been added
- Uses [Lemmy Webhook](https://github.com/RikudouSage/LemmyWebhook) for near instant checking of new content

# Table of Contents
<!-- TOC -->
* [LemmyAutomod](#lemmyautomod)
* [Table of Contents](#table-of-contents)
* [Prerequisites](#prerequisites)
* [Installation](#installation)
  * [1. Set up Webhooks](#1-set-up-webhooks)
  * [2. Set up LemmyAutomod container](#2-set-up-lemmyautomod-container)
    * [**2.1** Update docker compose stack](#21-update-docker-compose-stack)
    * [**2.2** Export YAML](#22-export-yaml)
    * [**2.3** Import webhooks](#23-import-webhooks)
  * [3. Notification setup (optional)](#3-notification-setup-optional)
    * [**3.1**  Private message on Lemmy](#31-private-message-on-lemmy)
    * [**3.2** Post to chat room on Matrix](#32-post-to-chat-room-on-matrix)
      * [**3.2.1** Create a new user on Matrix](#321-create-a-new-user-on-matrix)
      * [**3.2.2** Create a new Matrix chat room](#322-create-a-new-matrix-chat-room)
      * [**3.2.3** Retrieve access token](#323-retrieve-access-token)
    * [**3.3** Post to chat room on Slack](#33-post-to-chat-room-on-slack)
    * [**3.4** New user notifications](#34-new-user-notifications)
    * [**3.5** Test notification](#35-test-notification)
  * [4. Add rules to database](#4-add-rules-to-database)
    * [**4.1** Automatic approval](#41-automatic-approval)
    * [**4.2** Ban user when email matches regular expression](#42-ban-user-when-email-matches-regular-expression)
    * [**4.3** Ban user when username matches regular expression](#43-ban-user-when-username-matches-regular-expression)
    * [**4.4** Ban user if content matches regular expression](#44-ban-user-if-content-matches-regular-expression)
    * [**4.5** Report user if content matches regular expression](#45-report-user-if-content-matches-regular-expression)
    * [**4.6** Trusted users](#46-trusted-users)
    * [**4.7** Ban user if image is the same or similar](#47-ban-user-if-image-is-the-same-or-similar)
* [Information](#information)
  * [Table descriptions](#table-descriptions)
  * [Environment variables](#environment-variables)
    * [List](#list)
    * [LEMMY_AUTH_MODE environment variable](#lemmy_auth_mode-environment-variable)
  * [Jobs that can be manually run](#jobs-that-can-be-manually-run)
    * [Analyze a specific comment or post](#analyze-a-specific-comment-or-post)
    * [Reanalyze all posts since a point in time](#reanalyze-all-posts-since-a-point-in-time)
    * [Unban user and restore content](#unban-user-and-restore-content)
<!-- TOC -->
# Prerequisites
To use LemmyAutomod, you will need:
- Webhooks set up via [Lemmy Webhook](https://github.com/RikudouSage/LemmyWebhook)
- [LemmyWebhookManager](https://github.com/RikudouSage/LemmyWebhookManager/) set up if you want to set up the Automod webhooks via import.
- An account set up on your Lemmy instance with admin privileges to act as the Automod
- Access to the server your Lemmy instance is installed on, to add new containers to the docker compose stack and to directly edit the LemmyAutomod SQLite database to add your rules

# Installation

## 1. Set up Webhooks

First, set up [Lemmy Webhook](https://github.com/RikudouSage/LemmyWebhook) as well as the GUI [LemmyWebhookManager](https://github.com/RikudouSage/LemmyWebhookManager/).

## 2. Set up LemmyAutomod container

### **2.1** Update docker compose stack

Add the following to your Docker Compose file, and update the environment variables as applicable:
```
automod:
    image: rikudousage/lemmy-automod
    environment:
      - LEMMY_USER=Automod # Automod lemmy username
      - LEMMY_INSTANCE=lemmings.world # Automod lemmy instance hostname
      - LEMMY_PASSWORD=mypassword # Automod Lemmy password
      - APP_SECRET=[32 character random hex]
      - REDIS_HOST=redis
      - LEMMY_AUTH_MODE=4
    volumes:
      - ./volumes/automod:/opt/database
```
More information and further environment variables are detailed in [Environment variables](#environment-variables).

Ensure you also add networks that allow the LemmyWebhook to access LemmyAutomod, and LemmyAutomod to access Lemmy if you've got custom networks.

Start LemmyAutomod by running `docker compose up -d`

### **2.2** Export YAML
Run the following command to output a YAML file that contains the required webhooks. Ensure you update the container name where needed.

`docker exec [lemmy_automod_1] bin/console app:dump`

The above command assumes the Automod can be accessed at the address "http://automod". If you have changed the name of the service or if you are not running it as part of the same Docker Compose stack, you should use the following command to have the Webhooks contact the Automod at a different address:
`docker exec lemmy_automod_1 bin/console app:dump https://example.com`

Once the output has been generated, copy the YML from the output.

### **2.3** Import webhooks
Go to the LemmyWebhookManager web interface and log in. In the Webhooks section, choose "Import webhooks", then paste the YML into the provided field.
Click "Import", and the required webhooks should be automatically added.

LemmyWebhook is now set up to trigger LemmyAutomod.

## 3. Notification setup (optional)

There are three options for receiving notifications: A private message on Lemmy, or posting to a chat room on Matrix or Slack.

### **3.1**  Private message on Lemmy

To have LemmyAutomod message you on Lemmy with actions taken, add the following environment variable alongside the others you added to your Docker Compose file above, and edit the user to your own account:
`LEMMY_USERS_TO_NOTIFY=myLemmyUserAccount@lemmings.world`

You can add multiple users to notify by separating them with a comma.

### **3.2** Post to chat room on Matrix
To post to a chat room on Matrix, you will need to follow through a few steps.

#### **3.2.1** Create a new user on Matrix
Create a new Matrix user for the Automod.

#### **3.2.2** Create a new Matrix chat room
Create a new Matrix room and publish it. Add your new Automod Matrix user to the room.

If you use the Matrix client Element on desktop, you can click the + next to your list of rooms to create a new one. Name your room then confirm creation.

The room must be published, so go into the room settings, and on the General tab, add a room address in the "Local Addresses" section, then make sure this is set as the main address in the "Published addresses" section. Note this does not mean anyone can join your room, if you made it private it is still private.

Invite the Automod to the room (or invite yourself, if the Automod created the room).

#### **3.2.3** Retrieve access token

Run the following command to retrieve the Matrix access token:
` curl -XPOST -d '{"type":"m.login.password", "user":"[your-username]", "password":"[your-password]"}' "https://matrix.org/_matrix/client/r0/login"`

Replace [your-username] with the username of your new Automod Matrix account, [your-password] with the password for that account, and replace "matrix.org" with the Matrix server the account is on.

This should return a response similar to the following:
```
{
"user_id": "@automod:lemmings.world",
"access_token": "[secret-access-token]",
"home_server": "lemmings.world",
"device_id": "EIEIOEIEIO"
}
```

Add the following to your Docker Compose file in the automod service. Add your access token where stated, as well as the published name of your room.

```
 - MATRIX_API_TOKEN=[access token]
 - MATRIX_ROOM_NAMES="#automod:lemmings.world"
 - USE_LEMMYVERSE_LINK_MATRIX=1
```

`USE_LEMMYVERSE_LINK_MATRIX=1` indicates that notifications from the bot to Matrix should use lemmyverse.link links for all links so the links are universal, rather than being for a specific instance.

Recreate the container by running `docker compose up -d`.

### **3.3** Post to chat room on Slack



### **3.4** New user notifications

You can be notified of new users being added by adding this environment variable alongside the others you added to your Docker Compose file above:
`ENABLE_NEW_USERS_NOTIFICATION=1`

And you can be notified when new users create their first post or comment by adding:
`ENABLE_FIRST_POST_COMMENT_NOTIFICATION=1`

### **3.5** Test notification

You can send a test notification to test your setup by running the following command (update the container name if needed):
`docker exec -it lemmy_automod_1 bin/console app:test:notification "Test message"`

## 4. Add rules to database

LemmyAutomod will run rules over any new content. To set the rules, you add them into the SQLite database. Detailed information about the tables is listed in [Table Descriptions](#table-descriptions).

To access the SQLite console, go to your /volumes/automod directory and run:  
`sqlite3 data.db`

You may need to install sqlite3 from your package repository.

Now you're in the SQLite console, run the appropriate commands to add the rules (type ".quit" and press enter to exit the console).

### **4.1** Automatic approval

Account registration applications that match the regular expression will be automatically approved.

For example, the following will approve registration applications where the user typed exactly "I agree to the terms and conditions" in the Answer box:  
`insert into auto_approval_regexes (regex) values ('I agree to the terms and conditions');`

### **4.2** Ban user when email matches regular expression

When a new user uses an email address that matches a regular expression, the user will be banned. The "reason" is used for the mod log.

For example, the following will match some specific email addresses:  
`insert into banned_emails (regex, reason) values ('(spammer@hotmail.com|spambot@gmail.com)', 'detected spam email address');`

Or block emails from a whole domain with:
`insert into banned_emails (regex, reason) values ('@spamdomain.com)', 'Email domain not allowed: "spamdomain.com"');`

### **4.3** Ban user when username matches regular expression

When a user has a username that matches the regular expression, they get banned. The "remove_all" option indicates whether all posts should be removed when banning a user.

For example, the following will ban any user with the term "Spammer" in their name (e.g. BlogSpammer):
`insert into banned_usernames (username, reason, remove_all) values ('Spammer', 'detected banned username', false);`

This example will only ban a user if their username is "Spammer", and won't ban a user called "BlogSpammer":
`insert into banned_usernames (username, reason, remove_all) values ('^Spammer$', 'detected banned username', true);`

The first example will not remove the user's posts, while the second example will remove all posts. While it's possible to manually un-remove a removed post, it's not easy if there are a large number so use this setting with care.

### **4.4** Ban user if content matches regular expression

These rules will run over comments and posts, and ban the user if a match is found.

Rules are checked for posts against:
- Title
- Body
- URL
- Author name
- Author display name

And for comments:
- Content
- Author name
- Author display name

For example, you may want to ban a user that posts a known spam site:  
`insert into instance_ban_regexes (regex, reason, remove_all) values ('blogspamsite\.com', 'spammer', false);`

This example has "remove_all" set to false. If you set this to true, it will remove all the user's posts. While it's possible to manually un-remove a removed post, it's not easy if there are a large number so use this setting with care.

### **4.5** Report user if content matches regular expression

This works the same as [Ban user if content matches regular expression](#44-ban-user-if-content-matches-regular-expression) above, but instead of banning the user it will report them for manual review.

For example. the following will identify comments or posts with the phrase "you're an idiot" and report them for manual review:  
`insert into report_regexes (regex, message) values ('you\''re an idiot', 'Possible flame war');`

### **4.6** Trusted users

You can mark users as trusted users by adding them to the trusted_users table. When any user present in this table makes a report, it gets automatically resolved by removing the post or comment.

The following will add the user @trustworthy_user@lemmings.world into the trusted_users table:  
`insert into trusted_users (username, instance) values ('trustworthy_user', 'lemmings.world');`

### **4.7** Ban user if image is the same or similar

By adding an image hash to the `banned_images` table, you can ban any user that posts that image or a similar one.

First, generate the hash by running the following command:  
`docker exec -it lemmy_automod_1 bin/console app:image:hash [image URL]`

For example:
`docker exec -it lemmy_automod_1 bin/console app:image:hash https://lemmings.world/pictrs/image/89863158-5c41-4dea-b965-c7b0029cf837.webp?format=webp`

This will output a hash as follows:  
`1111111100000000001011000010000000100001010000000000011011111111`

Add the hash into the `banned_images` table as follows:  
`INSERT INTO banned_images (image_hash, similarity_percent, remove_all, reason, description) VALUES ('1111111100000000001011000010000000100001010000000000011011111111', 95, false, 'spammer', 'image containing a can of spam with ctkparr discord link');`


# Information

This section contains descriptions of tables, environment variables, and jobs that can be manually run to do various tasks.

## Table descriptions

- `auto_approval_regexes` - when a registration application answer matches, it gets approved
  - `regex` - the regex string
- `banned_emails` - when a new local user's email matches the regex, the user is banned
  - `regex` - the regex string
  - `reason` - the reason that will be used for the ban
- `banned_usernames` - when a user has matching username, it gets banned
  - `username` - the regex string (yes, it's really regex, just badly named, will be changed in the future)
  - `reason` - the reason that will be used for the ban
  - `remove_all` - whether all posts and comments should be removed when banning
- `instance_ban_regexes` - when a post (title, body, url, author name, author display name) or comment (content, author name, author display name) matches, the user gets banned and all posts get removed
  - `regex` - the regex string
  - `reason` - the reason that will be used for the ban
  - `remove_all` - whether all posts and comments should be removed when banning
- `report_regexes` - when a post or comment matches this regex, the user will be reported for manual review
  - `regex` - the regex string
  - `message` - the message that will be used as a reason for the report
- `trusted_users` - when any user present in this table makes a report, it gets automatically resolved by removing the post/comment
  - `username` - the local part of the username, like `rikudou`
  - `instance` - the instance, like `lemmings.world`
  - `user_id` - the ID of the user
  - The only field needed is the `user_id`, but if you don't want to go looking in the db for that,
    you can simply provide the `username` and `instance` and the automod will save the `user_id` on
    its own next time it gets any report
- `banned_images` if an image linked in a post is similar to the image_hash, the user is banned.
  - `image_hash` - the hash of the image to ban (see [Ban user if image is the same or similar](#47-ban-user-if-image-is-the-same-or-similar))
  - `similarity_percent` - a decimal number between 0 and 100 determining how similar the image must be - 100 means it needs to look exactly the same, 0 means that any image will match
  - `remove_all` - whether to remove all user's posts and comments if the image matches
  - `reason` - the optional reason that will be in the modlog
  - `description` - optional description of the image, only used for notification reports

## Environment variables

### List

Here is a list of environment variables and their descriptions:

| Environment Variable          | Description                                                                                                                                                                                                                                                           |
|-------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| LEMMY_USER                    | Username for Automod Lemmy account.                                                                                                                                                                                                                                   |
| LEMMY_INSTANCE                | Instance hostname of Automod Lemmy account.                                                                                                                                                                                                                           |
| LEMMY_PASSWORD                | Password for Automod Lemmy account.                                                                                                                                                                                                                                   |
| LEMMY_USERS_TO_NOTIFY         | Comma separated list of Lemmy users to message when actions are taken.                                                                                                                                                                                                |
| APP_SECRET                    | A random 32 hex characters - you can generate this using the terminal command 'openssl rand -hex 16'.                                                                                                                                                                 |
| REDIS_HOST                    | Service name for your redis service you would have added for LemmyWebhook.                                                                                                                                                                                            |
| ENABLE_NEW_USERS_NOTIFICATION | Whether to receive a notification when a new user joins.                                                                                                                                                                                                              |
| MATRIX_API_TOKEN              | Token for connecting to Matrix for posting to a Matrix chat. See [Notification setup](#3-notification-setup-optional).                                                                                                                                                |
| MATRIX_ROOM_NAMES             | A comma-separated list of Matrix rooms you want the bot to post notifications to. See [Notification setup](#3-notification-setup-optional).                                                                                                                           |
| USE_LEMMYVERSE_LINK_MATRIX    | Whether to use lemmyverse.link when posting links to content or users on Matrix, which is a universal link that directs users to the content on their own instance.                                                                                                   |
| SLACK_BOT_TOKEN               | Token for connecting to Slack for posting to a Slack channel. See [Notification setup](#3-notification-setup-optional).                                                                                                                                               |
| SLACK_CHANNELS                | A comma-separated list of Slack channels you want the bot to post notifications to. See [Notification setup](#3-notification-setup-optional).                                                                                                                         |
| USE_LEMMYVERSE_LINK_SLACK     | Whether to use lemmyverse.link when posting links to content or users on Slack.                                                                                                                                                                                       |
| USE_LEMMYVERSE_LINK_LEMMY     | Whether to use lemmyverse.link when posting links to content or users in Lemmy personal messages.                                                                                                                                                                     |
| LEMMY_AUTH_MODE               | Whether to send the Lemmy authentication as part of the header, body, or both. Sending as the header prevents credentials showing in logs, but is only supported by Lemmy 0.19.0 and up. See [Lemmy Auth Mode](#lemmy_auth_mode-environment-variable) for the options |

### LEMMY_AUTH_MODE environment variable

This environment variable controls whether to send the Lemmy authentication as part of the header, body, or both. Sending in the header prevents credentials showing in logs, but is only supported by Lemmy 0.19.0 and up.
The options are as follows:
- `2` - send auth as part of body (supports Lemmy < 0.19)
- `4` - send auth as a header (supports Lemmy >= 0.19)
- `6` - send auth both as part of body and as a part of header (supports both Lemmy < 0.19 and >= 0.19)

## Jobs that can be manually run

LemmyAutomod has the ability to run the checks over a specified post, comment, or user. It can reanalyze content since a certain point in time, and there are various other jobs that can be run.

### Analyze a specific comment or post
To have the automod check your rules against a specific post, run:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\AnalyzePostMessage --arg [post ID]`

To have the automod check your rules against a specific comment, run:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\AnalyzeCommentMessage --arg [comment ID]`

### Reanalyze all posts since a point in time

You can start a job to reanalyse all posts since a certain time (including checking image hashes). This might be helpful if you added a new rule, and want to run this over posts already checked before the rule was in place. To do this, run:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\ReanalyzePostsMessage --arg [datetime]`

For example:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\ReanalyzePostsMessage --arg '2024-02-16T01:00:00+02:00'`

### Unban user and restore content

If you have accidentally banned a user, you can unban them using the website UI, but make sure you first change the rule that banned them otherwise the user may be re-banned. If you've removed their content, you can use the following command to unban and restore content:

`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\UnbanUserMessage --arg [username] --arg [instance]`

For example:

`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\UnbanUserMessage --arg 'MyUsername' --arg 'lemmings.world'`

This will unban them. It will also iterate through all posts and comments and make all their comments and posts visible again. Note this will restore all posts and comments, even if the Automod was not the one that removed them, including posts and comments that the user themselves deleted. So be careful using it.
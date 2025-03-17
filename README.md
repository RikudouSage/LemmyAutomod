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
      * [**3.3.1** Create a new Slack app](#331-create-a-new-slack-app)
      * [**3.3.2** Update environment variables](#332-update-environment-variables)
    * [**3.4** New user notifications](#34-new-user-notifications)
    * [**3.5** Test notification](#35-test-notification)
  * [4. Add rules to database](#4-add-rules-to-database)
    * [**4.1** Automatic approval](#41-automatic-approval)
    * [**4.2** Ban user when email matches regular expression](#42-ban-user-when-email-matches-regular-expression)
    * [**4.3** Ban user when username matches regular expression](#43-ban-user-when-username-matches-regular-expression)
    * [**4.4** Ban user if content matches regular expression](#44-ban-user-if-content-matches-regular-expression)
    * [**4.5** Report user if content matches regular expression](#45-report-user-if-content-matches-regular-expression)
    * [**4.6** Trusted users](#46-trusted-users)
    * [**4.7** Watched users](#47-watched-users)
    * [**4.8** Ban user if image is the same or similar](#48-ban-user-if-image-is-the-same-or-similar)
    * [**4.9** Defederate from instances that meet certain criteria](#49-defederate-from-instances-that-meet-certain-criteria)
      * [**4.9.1** Software](#491-software)
      * [**4.9.2** Registration rules](#492-registration-rules)
      * [**4.9.3** Minimum version](#493-minimum-version)
      * [**4.9.4** Default values](#494-default-values)
      * [**4.9.5** Sync defederations to Fediseer censures](#495-sync-defederations-to-fediseer-censures)
    * [**4.10** Remove communities matching a regex](#410-remove-communities-matching-a-regex)
* [Information](#information)
  * [Table descriptions](#table-descriptions)
  * [Settings](#settings)
    * [Environment variable list](#environment-variable-list)
    * [Lemmy authentication mode](#lemmy-authentication-mode)
  * [Ignored content](#ignored-content)
    * [Ignore post](#ignore-post)
    * [Ignore comment](#ignore-comment)
    * [Ignore user](#ignore-user)
  * [Jobs that can be manually run](#jobs-that-can-be-manually-run)
    * [Restoration of accidentally removed posts](#restoration-of-accidentally-removed-posts)
    * [Analyze a specific comment or post](#analyze-a-specific-comment-or-post)
    * [Reanalyze all posts since a point in time](#reanalyze-all-posts-since-a-point-in-time)
    * [Send a test notification](#send-a-test-notification)
  * [Management API](#management-api)
    * [Securely accessing the api](#securely-accessing-the-api)
      * [Access it locally on the server only](#access-it-locally-on-the-server-only)
      * [Add a reverse proxy with a basic auth](#add-a-reverse-proxy-with-a-basic-auth)
      * [Port forwarding with SSH tunnel](#port-forwarding-with-ssh-tunnel)
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
    image: ghcr.io/rikudousage/lemmy-automod:latest
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
More information and further environment variables are detailed in [Environment variable list](#environment-variable-list).

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

You can have LemmyAutoMod post to a Slack channel by following the below steps.

#### **3.3.1** Create a new Slack app

Go to https://api.slack.com/apps and click the button `Create an App`, then choose the option `From an app manifest`.

Choose which Workspace you want it to be for.

On the next page, switch to the YAML tab then paste in the Slack manifest available [here](https://github.com/RikudouSage/LemmyAutomod/blob/master/slack.manifest.yaml).

Create the app, then press the button to `Install to workspace`.

Grant access by clicking `Allow`, then in the left-hand menu choose `OAuth & Permissions`.

On this page you will find the `Bot User OAuth Token`, copy this token.

#### **3.3.2** Update environment variables

In your docker-compose file, add the following environment variable under the existing LemmyAutoMod environment variables:
```
SLACK_BOT_TOKEN=[paste Bot User OAuth Token here]
SLACK_CHANNELS=[comma separated channel list]
USE_LEMMYVERSE_LINK_SLACK=1
```

`USE_LEMMYVERSE_LINK_SLACK=1` indicates that notifications from the bot to Slack should use lemmyverse.link links for all links so the links are universal, rather than being for a specific instance.

Recreate the container by running `docker compose up -d`.

### **3.4** New user notifications

You can be notified of new users being added by adding this environment variable alongside the others you added to your Docker Compose file above:
`ENABLE_NEW_USERS_NOTIFICATION=1`

And you can be notified when new users create their first post or comment by adding:
`ENABLE_FIRST_POST_COMMENT_NOTIFICATION=1`

### **3.5** Test notification

You can send a test notification to test your setup by running the following command (update the container name if needed):
`docker exec -it lemmy_automod_1 bin/console app:test:notification "Test message"`

## 4. Add rules to database

> Since version 2.13.0 you can use an API to control all the below settings, more information in the [Management API](#management-api) section

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

Note that reports are federated to other instances. If you don't want this, you can choose to only be notified through your [notification channel](#3-notification-setup-optional) by setting `private` to `1`, for example:

`insert into report_regexes (regex, message, private) values ('you\''re an idiot', 'Possible flame war', 1);`

### **4.6** Trusted users

You can mark users as trusted users by adding them to the trusted_users table. When any user present in this table makes a report, it gets automatically resolved by removing the post or comment.

The following will add the user @trustworthy_user@lemmings.world into the trusted_users table:  
`insert into trusted_users (username, instance) values ('trustworthy_user', 'lemmings.world');`

### **4.7** Watched users

You can mark users to watch by adding them to the watched_users table. When any user present in this table creates a post or comment, you will be notified on the channels you have set up for notifications.

The following will add the user @watched_user@lemmings.world into the trusted_users table:  
`insert into watched_users (username, instance) values ('watched_user', 'lemmings.world');`

### **4.8** Ban user if image is the same or similar

By adding an image hash to the `banned_images` table, you can ban any user that posts that image or a similar one.

First, generate the hash by running the following command:  
`docker exec -it lemmy_automod_1 bin/console app:image:hash [image URL]`

For example:
`docker exec -it lemmy_automod_1 bin/console app:image:hash https://lemmings.world/pictrs/image/89863158-5c41-4dea-b965-c7b0029cf837.webp?format=webp`

This will output a hash as follows:  
`1111111100000000001011000010000000100001010000000000011011111111`

Add the hash into the `banned_images` table as follows:  
`INSERT INTO banned_images (image_hash, similarity_percent, remove_all, reason, description) VALUES ('1111111100000000001011000010000000100001010000000000011011111111', 95, false, 'spammer', 'image containing a can of spam with ctkparr discord link');`

### **4.9** Defederate from instances that meet certain criteria

You can set rules that allow you to automatically defederate from instances based on either their registration rules, or their software version. You can also filter the rule by the software it uses. Currently, re-federation must be done manually (e.g. if the out of date instance is brought up to date, the Automod won't re-enable federation).

As an example, to defederate from Lemmy instances that have open registrations with no registration application, use this rule:  
`INSERT INTO instance_defederation_rules (software, allow_open_registrations, allow_open_registrations_with_application) values ('lemmy', false, true);`

To defederate from instances that are running a version of Lemmy under 18.0, you could add:  
`INSERT INTO instance_defederation_rules (software, minimum_version) values ('lemmy', '0.18.0');`

In general, fields not set are ignored. Not setting the software will cause the rule to apply to all software.

More detail follows.

#### **4.9.1** Software

The software field is taken from the data received from the server, and may change depending on the server sending it. For some software, it will be null.

Some common fediverse software identifiers:

| Software name | Identifier for rule |
|---------------|---------------------|
| Lemmy         | `lemmy`             |
| Kbin          | `kbin`              |
| Mbin          | `mbin`              |
| PieFed        | `PieFed`            |
| Mastodon      | `mastodon`          |
| Pleroma       | `pleroma`           |
| Akkoma        | `akkoma`            |

There are many more, this value is in the `software` column of the `instance` table in the Lemmy database. The value is case-sensitive.

You can also use a value of `null` to match any software.

#### **4.9.2** Registration rules

There are four fields that control defederation based on the setting for creating new accounts. If registration is closed, the server will not be defederated based on these rules (but may be defederated based on the version).

If `allow_open_registrations` is not set to `false`, then registration rules will not cause defederation of the server based on this defederation rule.

If `allow_open_registrations` is set to false, then instances that allow open registration with no protection will be defederated. However, instances will not be defederated if they match one of the following exceptions:

- If `allow_open_registrations_with_captcha` is set to true, then instances that allow open registration but have captcha enabled will not be defederated.
- Likewise, if `allow_open_registrations_with_email_verification` is set to true then instances that require email verification will not be defederated.
- Finally, if `allow_open_registrations_with_application` is set to true then instances that require registration applications will not be defederated.

As an example, to defederate from Lemmy instances that have open registrations with no email verification, use this rule:  
`INSERT INTO instance_defederation_rules (software, allow_open_registrations, allow_open_registrations_with_email_verification) values ('lemmy', false, true);`

#### **4.9.3** Minimum version

You can defederate instances whose version is below a minimum you set. This version check uses the [PHP version_compare](https://www.php.net/manual/en/function.version-compare.php) function.

To defederate from instances that are running a version of Lemmy under 18.0, you could add:  
`INSERT INTO instance_defederation_rules (software, minimum_version) values ('lemmy', '0.18.0');`

#### **4.9.4** Default values

If information from the remote instance does not exist (for example, it does not state its registration policy, or does not provide its version) then you can set a default. This is set in the field `treat_missing_data_as`.

For registration rules, setting `treat_missing_data_as` to `true` will treat answers as if they are true. For example, as Mastodon does not state whether a captcha is used, if `treat_missing_data_as` is set to `true` then `allow_open_registrations_with_captcha` will be treated as `true` for Mastodon, and the server will not be defederated. Likewise, setting `treat_missing_data_as` to `false` will cause all Mastodon servers to be defederated (among others).

For `minimum_version`, if the version is not known then setting `treat_missing_data_as` to `false` will defederate from all instances where the version is not known. Setting `treat_missing_data_as` to `true` will never defederate if the version is not known.

As an example, to defederate from Lemmy instances that have open registrations with no email verification *and* defederate if the registration policy isn't known, use:  
`INSERT INTO instance_defederation_rules (software, allow_open_registrations, allow_open_registrations_with_email_verification, treat_missing_data_as) values ('lemmy', false, true, false);`

#### **4.9.5** Sync defederations to Fediseer censures

It is possible to automatically sync with [Fediseer](https://fediseer.com/) to add a censure whenever a defederation action is taken.

To do this, add the following to your environment variables in the docker-compose file:

`FEDISEER_API_KEY=[API key here]`

### **4.10** Remove communities matching a regex

Communities can be removed when their name, title or description matches a regex. You can optionally choose
to also ban all mods.

Without banning mods:  
`INSERT INTO community_remove_regexes (regex, reason) VALUES ('blogspamsite\.com', 'spam promoting community')`

With mod banning:  
`INSERT INTO community_remove_regexes (regex, reason, ban_moderators) VALUES ('blogspamsite\.com', 'spam promoting community', 1)`

### **4.11** Ban user for a private message content

The senders of a private message can be banned if their private message matches a regex. Sadly Lemmy doesn't allow
deleting a message using api, so there's no way to delete it.

Without deleting all the user's content:

`INSERT INTO private_message_ban_regexes (regex, reason) VALUES ('blogspamsite\.com', 'spammer')`

With deleting all the user's content:

`INSERT INTO private_message_ban_regexes (regex, reason, remove_all) VALUES ('blogspamsite\.com', 'spammer', 1)`


# Information

This section contains descriptions of tables, environment variables, and jobs that can be manually run to do various tasks.

## Table descriptions

- `auto_approval_regexes` - when a registration application answer matches, it gets approved
  - `regex` - the regex string
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
- `banned_emails` - when a new local user's email matches the regex, the user is banned
  - `regex` - the regex string
  - `reason` - the reason that will be used for the ban
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
- `banned_usernames` - when a user has matching username, it gets banned
  - `username` - the regex string (yes, it's really regex, just badly named, will be changed in the future)
  - `reason` - the reason that will be used for the ban
  - `remove_all` - whether all posts and comments should be removed when banning
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
- `instance_ban_regexes` - when a post (title, body, url, author name, author display name) or comment (content, author name, author display name) matches, the user gets banned and all posts get removed
  - `regex` - the regex string
  - `reason` - the reason that will be used for the ban
  - `remove_all` - whether all posts and comments should be removed when banning
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
- `report_regexes` - when a post or comment matches this regex, the user will be reported for manual review
  - `regex` - the regex string
  - `message` - the message that will be used as a reason for the report
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
- `trusted_users` - when any user present in this table makes a report, it gets automatically resolved by removing the post/comment
  - `username` - the local part of the username, like `rikudou`
  - `instance` - the instance, like `lemmings.world`
  - `user_id` - the ID of the user
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
  - The only field needed is the `user_id`, but if you don't want to go looking in the db for that,
    you can simply provide the `username` and `instance` and the automod will save the `user_id` on
    its own next time it gets any report
- `banned_images` - if an image linked in a post is similar to the image_hash, the user is banned.
  - `image_hash` - the hash of the image to ban (see [Ban user if image is the same or similar](#48-ban-user-if-image-is-the-same-or-similar))
  - `similarity_percent` - a decimal number between 0 and 100 determining how similar the image must be - 100 means it needs to look exactly the same, 0 means that any image will match
  - `remove_all` - whether to remove all user's posts and comments if the image matches
  - `reason` - the optional reason that will be in the modlog
  - `description` - optional description of the image, only used for notification reports
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
- `instance_defederation_rules` - set rules for defederating from instances that meet certain criteria. See [Defederate from instances that meet certain criteria](#49-defederate-from-instances-that-meet-certain-criteria)
  - `software` - the name of the software the instance is running. See [here](#49-defederate-from-instances-that-meet-certain-criteria) for examples.
  - `allow_open_registrations` - if `false`, instance will be defederated if they allow open registration unless one of the other registration rules is marked as `true`. If `null`, the other registration rules are ignored.
  - `allow_open_registrations_with_captcha` - if `false`, instance will be defederated if they allow open registration with captcha, unless one of the other registration rules is marked as `true`.
  - `allow_open_registrations_with_email_verification` - if `false`, instance will be defederated if they allow open registration with email verification, unless one of the other registration rules is marked as `true`.
  - `allow_open_registrations_with_application` - if `false`, instance will be defederated if they allow registration with applications, unless one of the other registration rules is marked as `true`.
  - `treat_missing_data_as` - if information not available (e.g. Mastodon doesn't say whether it is using a captcha), then this sets a default to use instead. `True`, `False`, or `Null` (default).
  - `minimum_version` - If the server version is less than this (and the software setting matches), the server will be defederated.
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
- `ignored_posts` - mark posts as ignored so the rules don't apply to the post
  - `post_id` - the post ID as an integer
- `ignored_comments` - mark comments as ignored so the rules don't apply to the comment
  - `comment_id` - the comment ID as an integer
- `ignored_users` - don't check rules against this user, ban them, or remove their content
  - `username` - the local part of the username, like `rikudou`
  - `instance` - the instance, like `lemmings.world`
  - `user_id` - the ID of the user
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
  - The only field needed is the `user_id`, but if you don't want to go looking in the db for that,
    you can simply provide the `username` and `instance` and the automod will save the `user_id` on
    its own next time it is triggered
- `watched_users` - Add a user here to be notified when they post or comment.
  - `username` - the local part of the username, like `rikudou`
  - `instance` - the instance, like `lemmings.world`
  - `user_id` - the ID of the user
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.
  - The only field needed is the `user_id`, but if you don't want to go looking in the db for that,
    you can simply provide the `username` and `instance` and the automod will save the `user_id` on
    its own next time it is triggered
- `removal_logs` - this table holds logs of the removals the bot has actioned, and need not be manually modified.
- `community_remove_regexes` - holds regexes that communities will be matched to and banned if their title/name/description matches
  - `regex` - the regex string
  - `reason` - optional reason for the removal
  - `ban_moderators` - 0 or 1, whether the community's moderators should be banned, defaults to 0
- `private_message_ban_regexes` - holds regexes that private message will be matched to and banned if they match
  - `regex` - the regex string
  - `reason` - optional reason for the removal
  - `remove_all` - whether to remove all user's posts and comments
  - `enabled` - whether the rule is enabled. If set to `false`, the rule will be ignored.

## Settings

There are various settings that can be used to control the functionality of LemmyAutomod.

### Environment variable list

Here is a list of environment variables and their descriptions:

| Environment Variable          | Description                                                                                                                                                                                                                                                          |
|-------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| LEMMY_USER                    | Username for Automod Lemmy account.                                                                                                                                                                                                                                  |
| LEMMY_INSTANCE                | Instance hostname of Automod Lemmy account.                                                                                                                                                                                                                          |
| LEMMY_PASSWORD                | Password for Automod Lemmy account.                                                                                                                                                                                                                                  |
| LEMMY_USERS_TO_NOTIFY         | Comma separated list of Lemmy users to message when actions are taken.                                                                                                                                                                                               |
| APP_SECRET                    | A random 32 hex characters - you can generate this using the terminal command 'openssl rand -hex 16'.                                                                                                                                                                |
| REDIS_HOST                    | Service name for your redis service you would have added for LemmyWebhook.                                                                                                                                                                                           |
| ENABLE_NEW_USERS_NOTIFICATION | Whether to receive a notification when a new user joins.                                                                                                                                                                                                             |
| MATRIX_API_TOKEN              | Token for connecting to Matrix for posting to a Matrix chat. See [Notification setup](#3-notification-setup-optional).                                                                                                                                               |
| MATRIX_ROOM_NAMES             | A comma-separated list of Matrix rooms you want the bot to post notifications to. See [Notification setup](#3-notification-setup-optional).                                                                                                                          |
| USE_LEMMYVERSE_LINK_MATRIX    | Whether to use lemmyverse.link when posting links to content or users on Matrix, which is a universal link that directs users to the content on their own instance.                                                                                                  |
| SLACK_BOT_TOKEN               | Token for connecting to Slack for posting to a Slack channel. See [Notification setup](#3-notification-setup-optional).                                                                                                                                              |
| SLACK_CHANNELS                | A comma-separated list of Slack channels you want the bot to post notifications to. See [Notification setup](#3-notification-setup-optional).                                                                                                                        |
| USE_LEMMYVERSE_LINK_SLACK     | Whether to use lemmyverse.link when posting links to content or users on Slack.                                                                                                                                                                                      |
| USE_LEMMYVERSE_LINK_LEMMY     | Whether to use lemmyverse.link when posting links to content or users in Lemmy personal messages.                                                                                                                                                                    |
| LEMMY_AUTH_MODE               | Whether to send the Lemmy authentication as part of the header, body, or both. Sending as the header prevents credentials showing in logs, but is only supported by Lemmy 0.19.0 and up. See [Lemmy Authentication Mode](#lemmy-authentication-mode) for the options |
| REMOVAL_LOG_VALIDITY          | The amount of time to keep logs of removals, which are used to restore posts. Default is 24 hours.                                                                                                                                                                   |
| FEDISEER_API_KEY              | API key for fediseer.com, used to automatically create a censure on Fediseer when an instance is defederated by the automod. See [**4.8.5** Sync defederations to Fediseer censures](#495-sync-defederations-to-fediseer-censures).                                  |
| MANAGEMENT_API_ENABLED        | Whether the management api should be enabled or not (more on the api below).                                                                                                                                                                                         |


### Lemmy authentication mode

The `LEMMY_AUTH_MODE` environment variable controls whether to send the Lemmy authentication as part of the header, body, or both. Sending in the header prevents credentials showing in logs, but is only supported by Lemmy 0.19.0 and up.
The options are as follows:
- `2` - send auth as part of body (supports Lemmy < 0.19)
- `4` - send auth as a header (supports Lemmy >= 0.19)
- `6` - send auth both as part of body and as a part of header (supports both Lemmy < 0.19 and >= 0.19).

## Ignored content

Sometimes your rules may catch content that you want to let through, but you don't want to update the rule. For example, when a legitimate user posts content from a spam site. There are some options to ignore certain content to prevent the automod re-banning the user when you try to un-ban them.

### Ignore post

You can add a post to the `ignored_posts` table. The post will be ignored when rules are checked. You can add a post as follows:
`insert into ignored_posts (post_id) values (12345)`

The `post_id` can be copied from the URL of the post.

### Ignore comment

You can add a comment to the `ignored_comments` table. The comment will be ignored when rules are checked. You can add a comment as follows:
`insert into ignored_comments (comment_id) values (12345)`

The `comment_id` is not easy to retrieve from the default Lemmy website, unless the comment is a top-level comment where you can get it from the comment link. 

You can use your browser to inspect the comment element, where you will see the ID of the element includes the ID, such as `comment-8696754`.

Alternatively an API call may be easier, many of which can be done in your browser. If the comment is recent, it may be easiest to find the comment ID on the user's profile. To do this, for the user 'myLemmyUserAccount@lemmings.world', in your browser go to:
`https://lemmings.world/api/v3/user?username=myLemmyUserAccount@lemmings.world`, find the content in the returned data, and look for the comment ID of the relevant comment.

### Ignore user

You can add a user to the `ignored_users` table. All rules will be ignored for content posted by this user. If you know the user ID, you can add the user as follows:
`insert into ignored_users (user_id) values (12345)`

If you don't know the user ID, you can add the user `myLemmyUserAccount@lemmings.world` as follows:
`insert into ignored_users (username, instance) values ('myLemmyUserAccount', 'lemmings.world)`

## Jobs that can be manually run

LemmyAutomod has the ability to run the checks over a specified post, comment, or user. It can reanalyze content since a certain point in time, unban a user, and there are various other jobs that can be run.

### Restoration of accidentally removed posts

If a genuine user is caught by a rule, they may have been banned. You should adjust the rule before attempting to rectify it, as they may just get re-banned if you try to unban them.

LemmyAutomod also provides a tool to help with restoring content. By default, LemmyAutomod logs its actions for 24 hours. This means you have 24 hours to undo a ban and have removed content restored.

You can do this by running the following job to unban the user and restore their posts (once you have fixed the rule causing them to be banned):  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\UnbanUserMessage --arg [username] --arg [instance]`

For example:

`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\UnbanUserMessage --arg 'MyUsername' --arg 'lemmings.world'`

You can change the length of time the action logs are kept by setting the environment variable `REMOVAL_LOG_VALIDITY`.

You can set this to a number of hours, for example, this will keep logs for 48 hours:
`REMOVAL_LOG_VALIDITY=48`

Or you can use a PHP [DateInterval](https://www.php.net/manual/en/dateinterval.construct.php). For example, use, `P1H30M` to keep logs for one and a half hours, `P7D` to keep logs for a period of 7 days, `P1Y182D` to keep logs for approximately one and a half years. Do not add quotes, for example:  
`REMOVAL_LOG_VALIDITY=P1Y`

Note that if you set the log validity to `0`, then no logs are kept. In this case, if you run the above job then *all* the user's comments and posts will be restored, including posts or comments that the user themselves deleted or that moderators have removed manually.

### Analyze a specific comment or post
To have the automod check your ban rules against a specific post, run:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\AnalyzePostMessage --arg [post ID]`

To have the automod check your report rules against a specific post, run:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\AnalyzePostReportMessage --arg [post ID]`

To have the automod check your ban rules against a specific comment, run:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\AnalyzeCommentMessage --arg [comment ID]`

To have the automod check your report against a specific comment, run:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\AnalyzeCommentReportMessage --arg [comment ID]`

### Reanalyze all posts since a point in time

You can start a job to reanalyse all posts since a certain time (including checking image hashes). This might be helpful if you added a new rule, and want to run this over posts already checked before the rule was in place. To do this, run:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\ReanalyzePostsMessage --arg [datetime]`

For example:  
`docker exec -it lemmy_automod_1 bin/console app:trigger App\\Message\\ReanalyzePostsMessage --arg '2024-02-16T01:00:00+02:00'`

### Send a test notification

You can trigger a job to send a test notification for testing your notification setup. See [Test notification](#35-test-notification).

## Management API

In addition to controlling the automod using the database directly, you can enable a management api. It's a standard
rest api where you can create, read, update or delete all the rules. The api resource names are the same as for the
tables above, except they use a different naming convention (the tables use snake_case, while api uses kebab-case for 
resource names and camelCase for property names).

**Note that the api doesn't offer any protection, if you expose it to public, anyone can read and modify your rules.**

To enable the api, you must set the `MANAGEMENT_API_ENABLED` environment variable to `1`, otherwise all the endpoints
will return the not found status code.

After it's enabled, simply visit `/api` in your browser/Postman/whatever, and you will be presented with a list of
available resources. The api itself uses the [`JSON:API` v1.0 standard](https://jsonapi.org/format/1.0/) and is fairly
straightforward.

For example, a request to `/api/banned-usernames` might yield a JSON like this:

```json
{
  "meta": {
    "totalItems": 1,
    "itemsPerPage": 30,
    "currentPage": 1
  },
  "links": {
    "self": "/api/banned-usernames",
    "first": "/api/banned-usernames?page=1",
    "last": "/api/banned-usernames?page=1",
    "prev": null,
    "next": null
  },
  "data": [
    {
      "id": 1,
      "type": "banned-username",
      "attributes": {
        "username": "some-spammer-username-regex",
        "reason": "spammer",
        "removeAll": true,
        "enabled": true
      }
    }
  ],
  "included": []
}
```

### Securely accessing the api

The api runs on port 80 inside the container, so the first thing to do is to add a port mapping to your docker compose:

```yaml
automod:
    image: ghcr.io/rikudousage/lemmy-automod:latest
    environment:
      - LEMMY_USER=Automod
      - LEMMY_INSTANCE=lemmings.world
      - LEMMY_PASSWORD=mypassword
      - APP_SECRET=[32 character random hex]
      - REDIS_HOST=redis
      - LEMMY_AUTH_MODE=4
    volumes:
      - ./volumes/automod:/opt/database
    ports:
      - 8000:80
```

The above binds the container port 80 to a server port 8000. You can change the 8000 to anything you want.
**Make sure the api is not accessible on the chosen port outside the server.**
Afterwards, you have a few options on how to access it securely:

#### Access it locally on the server only

The simplest, simply access it only on the server using tools like `curl` (and `jq` to format the output). In that case
you can tighten the security by only allowing the local server to access the port at all:

```yaml
    ports:
      - 127.0.0.1:8000:80
```

#### Add a reverse proxy with a basic auth

You can configure a webserver to forward all requests to the automod and add a basic auth. If you don't already
have a webserver installed, caddy might be the easiest:

```Caddyfile
automod.example.com {
        @http {
                protocol http
        }
        redir @http https://{host}{uri}
        
        basicauth {
                some-username $2a$14$brtKkpOmKlmbU5qWCyQ1MOhxq9/tRHbPN4WIhMZFVu7YUF3euwx7i
        }
        
        reverse_proxy localhost:8000
}
```

This makes it only accessible for user named `some-username` with password `test`. To generate the password, you can
use the `caddy hash-password` command.

In this case you can also tighten the security by only allowing the local ip address binding:

```yaml
    ports:
      - 127.0.0.1:8000:80
```

#### Port forwarding with SSH tunnel

Connect to your server using SSH the way you usually do, but add this parameter:

`-L 8000:127.0.0.1:8000`

For example:

`ssh -i ~/.ssh/my-server.pem -L 8000:127.0.0.1:8000 user@example.com`

This will bind the server port 8000 on your local machine, allowing you to visit `http://127.0.0.1:8000` in your
browser/Postman/whatever.

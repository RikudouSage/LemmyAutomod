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

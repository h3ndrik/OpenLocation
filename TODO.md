TODO
====

0.1 (Alpha)
---
done

v0.2 (Alpha)
----
- Friends page (PHP) *done, but ugly*
- Request Friendship (PHP) *done, awaiting bugs*
- Grant Friendship (PHP) *done, awaiting bugs*
- Delete friends (PHP) *done, awaiting bugs*
- Status page (PHP) *done, but ugly*
- Delete locations - interval (PHP) *done, awaiting bugs*
- Delete locations - all (PHP) *done, not tested*

v0.3 (Alpha)
-----------
- fix back button (Android)
- fix refresh (Android)
- helper class for server api (Android)
- Delete pref (Android)
- Friends Activity (Android)
- Status Activity (Android)
- Info/Help screen (Android)

v0.4 (Alpha)
------------
- write error messages to serverlog everywhere(!) for debugging and testing (PHP)
- Move code into (self-contained?) libraries, eg. auth (PHP)
- Remove 'die's, clean error handling (PHP)
- every statement: mysql_real_escape_string, htmlspecialchars (PHP)
- check for other security problems (eg injection, no auth) (PHP)
- clean code, make sure functions are used (PHP)
- don't make local http connections (PHP)
- don't make blocking http requests (PHP)
- give sane error messages, and display them properly (PHP)

v0.5 (Alpha)
-----------
- Bugfix
- Mapsforge View (Android)
- CSS (PHP)


v0.6 (Beta)
-----------
- Bugfix
- Encryption (Android)
- Delete user (PHP)


v0.7 (Beta)
-----------
- Bugfix
- Server2Server Encryption (PHP)

v0.8 (Beta)
-----------
- Bugfix
- Release

v0.9 (Beta)
-----------
- Bugfix
- clone Latitude

v1.0
----
- make stable

later
-----
- Remove location jitter
- Fake position
- see [android-sdk/samples/SampleSyncAdapter]
- replace Credentials in SharedPreferences with AccountManager (Android) [http://developer.android.com/reference/android/accounts/AccountManager.html]
- implement OAuth2 (Android) [http://developer.android.com/training/id-auth/authenticate.html]


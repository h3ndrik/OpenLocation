TODO
====

0.1 (Alpha)
---
done

v0.2 (Alpha)
----
- Friends page (PHP) *done*
- Request Friendship (PHP) *done*
- Grant Friendship (PHP) *done*
- Delete friends (PHP) *done*
- Status page (PHP) *done*
- Delete locations - interval (PHP) *done*
- Delete locations - all (PHP) *done*
- fix back button (Android) *done*
- fix refresh (Android) *done?*
- upload on data connection becoming available (Android) *done?*
- correctly handle nested sql results (replace wiht $result1 $result2...?) (PHP) *done*
- Datenschutzerklärung & Info (PHP) *done*
- don't save passwords in plaintext (PHP) *done*
- fix token handling for usernames with ':' and '-' (strrpos) (PHP) *done?*
- add circle for accuracy of position (PHP) *done?*
- make sure updates are only done once (Android) *done?*

v0.3 (Alpha)
-----------
- clean locationcache (Android)
- clear auth data in webview when credentials change (Android)
- helper class for server api (Android)
- Delete pref (Android)
- Friends Activity (Android)
- Status Activity (Android)
- Info/Help screen (Android)
- progress bar on loading (Android)

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
- implement all remaining TODOs from code

v0.5 (Alpha)
-----------
- Bugfix
- Mapsforge View (Android)
- CSS (PHP)
- add marker with popups to path (PHP)
- translate backend (PHP)

v0.6 (Beta)
-----------
- Bugfix
- implement openlocation auth 0.2
- Encryption (Android)

v0.7 (Beta)
-----------
- Bugfix
- Server2Server Encryption (PHP)
- store hash of password (PHP & Android)

v0.8 (Beta)
-----------
- Bugfix
- translate (PHP)
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
- custom update intervals etc. (Android)
- Delete user (PHP)
- Fragments UI
- see [android-sdk/samples/SampleSyncAdapter]
- replace Credentials in SharedPreferences with AccountManager (Android) [http://developer.android.com/reference/android/accounts/AccountManager.html]
- implement OAuth2 (Android) [http://developer.android.com/training/id-auth/authenticate.html]
